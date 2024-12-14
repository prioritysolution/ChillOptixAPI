<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            
                $sql = DB::statement("Call USP_PUSH_ORG_USER_LOGIN(?,?,@error,@message,@user_pass,@org_id,@org_name,@org_add,@org_mob,@org_reg,@org_r_date,@org_fin_start,@org_fin_end);",[$request->email,$request->date]);

                if(!$sql){
                    throw new Exception("Operation Could Not Be Complete");
                }
                $result = DB::select("Select @user_pass As Pass,@error As Error_No,@message As Message,@org_id As Org_Id,@org_name As Org_Name,@org_add As Add,@org_mob As Mob,@org_reg As Reg,@org_r_date As Reg_Date,@org_fin_start As Fin_Start,@org_fin_end As Fin_End");
                $db_error = $result[0]->Error_No;
                $db_message = $result[0]->Message;
                $user_pass = $result[0]->Pass;
                $org_Id = $result[0]->Org_Id;
                $org_Name = $result[0]->Org_Name;
                $org_Add = $result[0]->Add;
                $org_mob = $result[0]->Mob;
                $org_reg = $result[0]->Reg;
                $org_reg_date = $result[0]->Reg_Date;
                $fin_start = $result[0]->Fin_Start;
                $fin_end = $result[0]->Fin_End;

                if($db_error<0){
                    $response = response()->json([
                        'message' => $db_message,
                        'details' => null,
                    ],400);
        
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
                            'fin_end'=>$fin_end
                        ],200);
                    }
                    else{
                        $response = response()->json([
                            'message' => 'Invalid Password',
                            'details' => null
                        ],400);
                    
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
}