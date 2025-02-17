<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Models\OrgUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;
use \stdClass;

class UserLogin extends Controller
{
    public function convertToObject($array) {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
    
    public function process_user_login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' =>'required|email',
            'password' =>'required',
            'date' => 'required'
        ]);

        if($validator->passes()){
            try {
            
                $sql = DB::statement("Call USP_PUSH_ORG_USER_LOGIN(?,?,@error,@message,@user_pass,@org_id,@org_name,@org_add,@org_mob,@org_reg,@org_r_date,@org_fin_start,@org_fin_end,@fin_id);",[$request->email,$request->date]);

                if(!$sql){
                    throw new Exception("Operation Could Not Be Complete");
                }
                $result = DB::select("Select @user_pass As Pass,@error As Error_No,@message As Message,@org_id As Org_Id,@org_name As Org_Name,@org_add As org_add,@org_mob As Mob,@org_reg As Reg,@org_r_date As Reg_Date,@org_fin_start As Fin_Start,@org_fin_end As Fin_End,@fin_id As Fin_Id");
                $db_error = $result[0]->Error_No;
                $db_message = $result[0]->Message;
                $user_pass = $result[0]->Pass;
                $org_Id = $result[0]->Org_Id;
                $org_Name = $result[0]->Org_Name;
                $org_Add = $result[0]->org_add;
                $org_mob = $result[0]->Mob;
                $org_reg = $result[0]->Reg;
                $org_reg_date = $result[0]->Reg_Date;
                $fin_start = $result[0]->Fin_Start;
                $fin_end = $result[0]->Fin_End;
                $fin_id = $result[0]->Fin_Id;

                if($db_error<0){
                    $response = response()->json([
                        'message' => $db_message,
                        'details' => null,
                    ],202);
        
                    return $response;
                }
                else{

                    if(Hash::check($request->password, $user_pass)){
                        $user = OrgUser::where("User_Mail", $request->email)->first();
                        $token = $user->CreateToken("OrgAuthAPI")->plainTextToken;
                        return response()->json([
                            'message' => 'Login Successful',
                            'token'=>$token,
                            'org_id' => $org_Id,
                            'org_Name'=>$org_Name,
                            'org_Add'=>$org_Add,
                            'org_mob'=>$org_mob,
                            'org_reg'=>$org_reg,
                            'org_reg_date'=>$org_reg_date,
                            'fin_start'=>$fin_start,
                            'fin_end'=>$fin_end,
                            'fin_id' =>$fin_id
                        ],200);
                    }
                    else{
                        $response = response()->json([
                            'message' => 'Invalid Password',
                            'details' => null
                        ],202);
                    
                        return $response;
                    }
                }

            } catch (Exception $ex) {
                $response = response()->json([
                    'message' => $ex->getMessage(),
                    'details' => null,
                ],400);
    
                throw new HttpResponseException($response);
            }
        }
        else{
            $errors = $validator->errors();

        $response = response()->json([
          'message' => $errors->messages(),
          'details' => null,
      ],400);
  
      throw new HttpResponseException($response);
        }
    }

    public function get_user_sidebar(){
        try {
            $result = DB::select("CALL USP_GET_ORG_USER_DASHBOARD(?);", [auth()->user()->Id]);
            
            if (empty($result)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }

            $menu_set = [];
            
            foreach ($result as $row) {
                if (!isset($menu_set[$row->Id])) {
                    $menu_set[$row->Id] = [
                        "title" => $row->Module_Name,
                        "Icon" => $row->Icon,
                        "path" => $row->Module_Path,
                        "childLinks" => []
                    ];
                }
                if ($row->Menue_Name) {
                    $menu_set[$row->Id]['childLinks'][] = [
                        "Menue_Name" => $row->Menue_Name,
                        "Icon" => $row->Icon,
                        "Page_Allies" => $row->Page_Alies
                    ];
                }
            }
    
            $menu_set = array_values($menu_set);
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $menu_set
            ], 200);
    
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ], 400);
        }
    }

    public function process_log_out(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout Successfull',
            'details' => null,
        ],200);
    }

    public function process_dashboard(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Call USP_GET_DASHBOARD_ITEM(?);",[$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }

            $dashboard_item = [
                'General_Booking' => 0,
                'Bond_Issue' => 0,
                'Pending_Rack' => 0,
                'Pending_Bond' =>0,
                'Chamber_Data' => []
            ];
            
            foreach ($sql as $item) {
                // Assign basic information (only once)
                $dashboard_item['General_Booking'] = $item->Gen_Booking;
                $dashboard_item['Bond_Issue'] = $item->Bond_Issue;
                $dashboard_item['Pending_Rack'] = $item->Pending_Rack;
                $dashboard_item['Pending_Bond'] = $item->Pending_Bond;
            
                // Initialize chamber if not already set
                if (!isset($dashboard_item['Chamber_Data'][$item->Chember_id])) {
                    $dashboard_item['Chamber_Data'][$item->Chember_id] = [
                        'Chamber_Id' => $item->Chember_id,
                        'Capacity' => $item->Capacity,
                        'Chamber_Name' => $item->Chamber_Name,
                        'Qnty' => $item->Chamber_Qnty,
                        'Rack_Data' => []
                    ];
                }
            
                // Initialize rack if not already set
                if (!isset($dashboard_item['Chamber_Data'][$item->Chember_id]['Rack_Data'][$item->Rack_Id])) {
                    $dashboard_item['Chamber_Data'][$item->Chember_id]['Rack_Data'][$item->Rack_Id] = [
                        'Rack_Id' => $item->Rack_Id,
                        'Rack_Name' => $item->Rack_Name,
                        'Capacity' => $item->Rack_Capacity,
                        'Qnty' => $item->Rack_Qnty
                    ];
                }
            }
            
            // Convert Chamber_Data and Rack_Data associative arrays to indexed arrays
            $dashboard_item['Chamber_Data'] = array_values($dashboard_item['Chamber_Data']);
            foreach ($dashboard_item['Chamber_Data'] as &$chamber) {
                $chamber['Rack_Data'] = array_values($chamber['Rack_Data']);
            }
            
            return response()->json([
                'message' => 'Data Found',
                'details' => $dashboard_item
            ], 200);
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_user_profile(){
        try {
            $result = DB::select("Select m.Id,m.User_Name,m.User_Mail,m.User_Mob,r.Role_Name From mst_org_user m Join mst_org_user_role r On r.Id=m.Role_Id Where m.Id=?;", [auth()->user()->Id]);
            
            if (empty($result)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $result
            ], 200);
    
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ], 400);
        }
    }

    public function get_user_role(){
        try {
            $result = DB::select("Select Id,Role_Name From mst_org_user_role;");
            
            if (empty($result)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $result
            ], 200);
    
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ], 400);
        }
    }

    public function process_user(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' =>'required',
            'user_name' =>'required',
            'user_mail' => 'required|email',
            'user_mob' => 'required',
            'user_pass' => 'required',
            'user_role' => 'required'
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $sql = DB::statement("Call USP_ADD_ORG_ADMIN_USER(?,?,?,?,?,?,?,@error,@messg);",[$request->org_id,$request->user_role,$request->user_name,$request->user_mail,$request->user_mob,Hash::make($request->user_pass),auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error_No,@messg As Message");
                $error_No = $result[0]->Error_No;
                $message = $result[0]->Message;

                if($error_No<0){
                    DB::rollBack();
                    return response()->json([
                        'message' => $message,
                        'details' => null,
                    ],400);
                }
                else{
                    DB::commit();
                    return response()->json([
                        'message' => 'User Successfully Added !!',
                        'details' => null,
                    ],200);
                }

            } catch (Exception $ex) {
                DB::rollBack(); 
                $response = response()->json([
                    'message' => $ex->getMessage(),
                    'details' => null,
                ],400);
    
                throw new HttpResponseException($response);
            }
        }
        else{
            $errors = $validator->errors();

            $response = response()->json([
              'message' => $errors->messages(),
              'details' => null,
          ],400);
      
          throw new HttpResponseException($response);
        }
    }

    public function process_update_profile(Request $request){
        $validator = Validator::make($request->all(),[
            'user_name' =>'required',
            'user_mob' => 'required',
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $sql = DB::statement("Call USP_UPDATE_ORG_USER(?,?,?,?);",[auth()->user()->Id,$request->user_name,$request->user_mob,Hash::make($request->user_pass)]);

                if(!$sql){
                    throw new Exception ('Operation Could Not Be Complete !!');
                }

                    DB::commit();
                    return response()->json([
                        'message' => 'User Profile Successfully Updated !!',
                        'details' => null,
                    ],200);

            } catch (Exception $ex) {
                DB::rollBack(); 
                $response = response()->json([
                    'message' => $ex->getMessage(),
                    'details' => null,
                ],400);
    
                throw new HttpResponseException($response);
            }
        }
        else{
            $errors = $validator->errors();

            $response = response()->json([
              'message' => $errors->messages(),
              'details' => null,
          ],400);
      
          throw new HttpResponseException($response);
        }
    }

    public function process_notification(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);
    
            $sql = DB::connection('chill')->select("SELECT DATEDIFF(Valid_Till,CURDATE()) as Date_diff
                                                    FROM mst_licence_details 
                                                    WHERE Valid_Till <= DATE_SUB(CURDATE(), INTERVAL -3 DAY) 
                                                    AND Is_Active = 1;");
    
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $days = $sql[0]->Date_diff;

            return response()->json([
                'message' => 'Data Found',
                'details' => 'Licence Renewal After '.$days.' Days !!',
            ],200);
    
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);
    
            throw new HttpResponseException($response);
        } 
    }

    public function get_user_role_menue(){
        try {
            $sql = DB::select("Select m.Id As Module_Id,m.Module_Name,s.Id As Menue_Id,s.Menue_Name From mst_org_module m Left Join mst_org_submodule s On s.Module_Id=m.Id");
    
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $role_data = [];
            foreach ($sql as $role) {
                if(!isset($role_data[$role->Module_Id])){
                    $role_data[$role->Module_Id] =[
                        'Module_Id' => $role->Module_Id,
                        'Module_Name' => $role->Module_Name,
                        'Menue_Data' => [],
                    ] ;
                }
                if($role->Menue_Id){
                    if(!isset($role_data[$role->Module_Id]['Menue_Data'][$role->Menue_Id])){
                        $role_data[$role->Module_Id]['Menue_Data'][]=[
                            'Menue_Id' => $role->Menue_Id,
                            'Menue_Name' => $role->Menue_Name,
                        ];
                    }
                }
            }
            $role_data = array_values($role_data);
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $role_data,
            ],200);
    
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);
    
            throw new HttpResponseException($response);
        }
    }

    public function get_role_user_list(){
        try {
            $sql = DB::select("Select Id,User_Name From mst_org_user Where Role_Id<>1;");
    
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $sql,
            ],200);
    
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);
    
            throw new HttpResponseException($response);
        }
    }

    public function process_map_role(Request $request){
        $validator = Validator::make($request->all(),[
            'user_id' => 'required',
            'role_data' => 'required'
        ]);
        if($validator->passes()){
        try {
    
            
            DB::beginTransaction();
    
            $drop_table = DB::statement("Drop Temporary Table If Exists tempmodule;");
            $create_tabl = DB::statement("Create Temporary Table tempmodule
                                        (
                                            Module_Id		Int,
                                            Menue_Id		Int
                                        );");
            $bond_data = $this->convertToObject($request->role_data);
            foreach ($bond_data as $bond) {
                DB::statement("Insert Into tempmodule (Module_Id,Menue_Id) Values (?,?);",[$bond->module_id,$bond->menue_id]);
             }
            $sql = DB::statement("Call USP_MAP_ORG_USER_ROLE(?,?,@error,@message);",[$request->user_id,auth()->user()->Id]);
    
            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::select("Select @error As Error_No,@message As Message;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;
    
    
            if($error_No<0){
                DB::rollBack();
                return response()->json([
                    'message' => $message,
                    'details' => null,
                ],202);
            }
            else{
                DB::commit();
                return response()->json([
                    'message' => 'User Role Successfully Mapped !!',
                    'details' => null,
                ],200);
            }
            
        } catch (Exception $ex) {
            DB::rollBack();
            $response = response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ],400);
    
            throw new HttpResponseException($response);
        }
    }
    else{
        $errors = $validator->errors();
    
            $response = response()->json([
                'message' => $errors->messages(),
                'details' => null,
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }
}