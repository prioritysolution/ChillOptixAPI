<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;


class ProcessMaster extends Controller
{
    public function process_floor(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'floor_name' => 'required',
            'floor_no' => 'required'
        ]);
        if($validator->passes()){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);
            DB::connection('chill')->beginTransaction();
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_FLOOR(?,?,?,?,?,@error,@message);",[null,$request->floor_name,$request->floor_no,auth()->user()->Id,1]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::connection('chill')->select("Select @error As Error_No,@message As Message;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;

            if($error_No<0){
                DB::connection('chill')->rollBack();
                return response()->json([
                    'message' => $message,
                    'details' => null,
                ],204);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Floor Successfully Added !!',
                    'details' => null,
                ],200);
            }
            
        } catch (Exception $ex) {
            DB::connection('chill')->rollBack();
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
            ],204);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_floor_list(Int $org_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Floor_Name,Floor_No From mst_floor Order By Id");

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
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function update_floor(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'floor_id'=>'required',
            'floor_name' => 'required',
            'floor_no' => 'required'
        ]);
        if($validator->passes()){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);
            DB::connection('chill')->beginTransaction();
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_FLOOR(?,?,?,?,?,@error,@message);",[$request->floor_id,$request->floor_name,$request->floor_no,auth()->user()->Id,2]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::connection('chill')->select("Select @error As Error_No,@message As Message;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;

            if($error_No<0){
                DB::connection('chill')->rollBack();
                return response()->json([
                    'message' => $message,
                    'details' => null,
                ],204);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Floor Successfully Updated !!',
                    'details' => null,
                ],200);
            }
            
        } catch (Exception $ex) {
            DB::connection('chill')->rollBack();
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
            ],204);
        
            throw new HttpResponseException($response);
    } 
    }
}