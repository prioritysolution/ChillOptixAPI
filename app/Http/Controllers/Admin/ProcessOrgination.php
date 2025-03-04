<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\ImageUpload;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;
use \stdClass;

class ProcessOrgination extends Controller
{
    use ImageUpload;
    public function process_org(Request $request){
        $validator = Validator::make($request->all(),[
            'org_name' =>'required',
            'org_add' =>'required',
            'org_reg_no' => 'required',
            'org_reg_date' => 'required',
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $img_name = null;
                if ($request->hasFile('org_logo')) {
                    $image = $request->file('org_logo');
                    $extension = strtolower($image->getClientOriginalExtension());
                    $allowedExtensions = ['jpeg', 'jpg', 'png'];
                    if(in_array($extension, $allowedExtensions)){
                        // Define the directory dynamically
                        $directory = 'logo';
                            
                        // Upload and compress the image
                        $path = $this->uploadAndCompressImage($image, 'img',$directory);
                        $img_name = $path;
                        // Save the path to the database or perform other actions
                    }
                    else{
                        throw new Exception("Invalid File Format !!");
                    }
                }

                $sql = DB::statement("Call USP_ADD_ORGINATION(?,?,?,?,?,?,?,?,?,?,?,@error,@messg);",[null,$request->org_name,$request->org_add,$request->org_reg_no,$request->org_reg_date,$request->org_mobile,$request->org_mail,$img_name,$request->is_manual,auth()->user()->Id,1]);

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
                        'message' => 'Organisation Successfully Added !!',
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

    public function update_org(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'org_name' =>'required',
            'org_add' =>'required',
            'org_reg_no' => 'required',
            'org_reg_date' => 'required',
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $img_name = null;
                if ($request->hasFile('org_logo')) {
                    $image = $request->file('org_logo');
                    $extension = strtolower($image->getClientOriginalExtension());
                    $allowedExtensions = ['jpeg', 'jpg', 'png'];
                    if(in_array($extension, $allowedExtensions)){
                        // Define the directory dynamically
                        $directory = 'logo';
                            
                        // Upload and compress the image
                        $path = $this->uploadAndCompressImage($image, 'img',$directory);
                        $img_name = $path;
                        // Save the path to the database or perform other actions
                    }
                    else{
                        throw new Exception("Invalid File Format !!");
                    }
                }

                $sql = DB::statement("Call USP_ADD_ORGINATION(?,?,?,?,?,?,?,?,?,?,?,@error,@messg);",[$request->org_id,$request->org_name,$request->org_add,$request->org_reg_no,$request->org_reg_date,$request->org_mobile,$request->org_mail,$img_name,$request->is_manual,auth()->user()->Id,2]);

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
                        'message' => 'Organisation Successfully Updated !!',
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

    public function process_org_finyear(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' =>'required',
            'start_date' =>'required',
            'end_date' => 'required'
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $sql = DB::statement("Call USP_ORG_ADD_FIN_YEAR(?,?,?,?,@error,@messg);",[$request->org_id,$request->start_date,$request->end_date,auth()->user()->Id]);

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
                        'message' => 'Financial Year Successfully Added !!',
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

    public function check_org_rental(Int $org_id){
        try {
           
            $sql = DB::select("Select DATE_ADD(Valid_To,INTERVAL 1 DAY) As Valid_from From mst_org_server_rental Where Org_Id=? Order By Id Desc Limit 0,1",[$org_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 204);
            }

            
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ],400);

            throw new HttpResponseException($response);
        } 
    }

    public function process_rental(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' =>'required',
            'start_date' =>'required',
            'end_date' => 'required'
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $sql = DB::statement("Call USP_ORG_ADD_RENTAL_DATA(?,?,?,?,@error,@messg);",[$request->org_id,$request->start_date,$request->end_date,auth()->user()->Id]);

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
                        'message' => 'Rental Data Successfully Added !!',
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

    public function get_org_user_role(){
        try {
           
            $sql = DB::select("Select Id,Role_Name From mst_org_user_role Where Is_Active=1");

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 400);
            }

            
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ],400);

            throw new HttpResponseException($response);
        } 
    }

    public function process_org_user(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' =>'required',
            'user_name' =>'required',
            'user_mail' => 'required|email',
            'user_mob' => 'required',
            'user_pass' => 'required'
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $sql = DB::statement("Call USP_ADD_ORG_ADMIN_USER(?,?,?,?,?,?,?,@error,@messg);",[$request->org_id,1,$request->user_name,$request->user_mail,$request->user_mob,Hash::make($request->user_pass),auth()->user()->Id]);

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

    public function get_org_list(){
        try {
           
            $sql = DB::select("Select Id,Org_Name,Org_Reg_No,Org_Reg_Date,Org_Mobile,Org_Mail,Is_Manual,Org_Address From mst_org_register Where Is_Active=1");

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 400);
            }

            
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => $ex->getMessage(),
                'details' => null,
            ],400);

            throw new HttpResponseException($response);
        } 
    }

    public function get_account_head(){
        try {
           
            $sql = DB::select("Select Id,Head_Name From mst_org_acct_head;");

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

    public function process_acct_head(Request $request){
        $validator = Validator::make($request->all(),[
            'head_name' => 'required',
            'under_head' => 'required'
        ]);
        if($validator->passes()){
        try {

            DB::beginTransaction();
            $sql = DB::statement("Call USP_ADD_EDIT_ACCOUNTS_HEAD(?,?,?,?,?,@error,@message);",[null,$request->head_name,$request->under_head,auth()->user()->Id,1]);

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
                    'message' => 'Accounts Head Successfully Added !!',
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

    public function get_acct_head_list(){
        try {

            $sql = DB::select("Select m.Id,m.Head_Id As Main_Head,m.Sub_Head_Name,h.Head_Name From mst_org_acct_sub_head m Join mst_org_acct_head h On h.Id=m.Head_Id;");

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

    public function update_acct_head(Request $request){
        $validator = Validator::make($request->all(),[
            'head_id' => 'required',
            'head_name' => 'required',
            'under_head' => 'required'
        ]);
        if($validator->passes()){
        try {

            DB::beginTransaction();
            $sql = DB::statement("Call USP_ADD_EDIT_ACCOUNTS_HEAD(?,?,?,?,?,@error,@message);",[$request->head_id,$request->head_name,$request->under_head,auth()->user()->Id,2]);

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
                    'message' => 'Accounts Head Successfully Updated !!',
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

    public function process_acct_ledger(Request $request){
        $validator = Validator::make($request->all(),[
            'ledger_name' => 'required',
        ]);
        if($validator->passes()){
        try {

            DB::beginTransaction();
            $sql = DB::statement("Call USP_ADD_EDIT_ACCT_LEDGER(?,?,?,?,?,?,@error,@message);",[null,$request->ledger_name,$request->head_id,$request->sub_head,auth()->user()->Id,1]);

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
                    'message' => 'Accounts Ledger Successfully Added !!',
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

    public function get_acct_ledger_list(){
        try {

            $sql = DB::select("Select Id,Head_Id,Sub_Head,Ledger_Name From mst_org_acct_ledger;");

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

    public function update_acct_ledger(Request $request){
        $validator = Validator::make($request->all(),[
            'ledger_id' => 'required',
            'ledger_name' => 'required',
        ]);
        if($validator->passes()){
        try {

            DB::beginTransaction();
            $sql = DB::statement("Call USP_ADD_EDIT_ACCT_LEDGER(?,?,?,?,?,?,@error,@message);",[$request->ledger_id,$request->ledger_name,$request->head_id,$request->sub_head,auth()->user()->Id,2]);

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
                    'message' => 'Accounts Ledger Successfully Updated !!',
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

    public function process_default_ledger(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'adv_ledger' => 'required',
            'rent_ledger' => 'required',
            'ins_ledger' => 'required',
            'rms_ledger' => 'required',
            'drying_led' => 'required',
            'loan_prn' => 'required',
            'loan_intt' => 'required'
        ]);
        if($validator->passes()){
        try {

            DB::beginTransaction();
            $sql = DB::statement("Call USP_MAP_ORG_DEFAULT_LEDGER(?,?,?,?,?,?,?,?,?);",[$request->org_id,$request->adv_ledger,$request->rent_ledger,$request->ins_ledger,$request->rms_ledger,$request->drying_led,$request->loan_prn,$request->loan_intt,auth()->user()->Id]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
           
                
                
                DB::commit();
                return response()->json([
                    'message' => 'Default Ledger Is Setup Successful !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    }
    }
}