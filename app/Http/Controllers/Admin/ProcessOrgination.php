<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;
use \stdClass;

class ProcessOrgination extends Controller
{
    public function process_org(Request $request){
        $validator = Validator::make($request->all(),[
            'org_name' =>'required',
            'org_add' =>'required',
            'org_reg_no' => 'required',
            'org_reg_date' => 'required',
            'org_mobile' => 'required',
            'org_mail' => 'required'
        ]);

        if($validator->passes()){
            try {

                DB::beginTransaction();

                $sql = DB::statement("Call USP_ADD_ORGINATION(?,?,?,?,?,?,?,@error,@messg);",[$request->org_name,$request->org_add,$request->org_reg_no,$request->org_reg_date,$request->org_mobile,$request->org_mail,auth()->user()->Id]);

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
}