<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Exceptions\HttpResponseException;
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
                ],202);
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
            ],202);
        
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
                ],202);
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function process_chamber(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'floor_id' => 'required',
            'chamber_name' => 'required',
            'chamber_no' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_CHAMBER(?,?,?,?,?,?,@error,@message);",[null,$request->floor_id,$request->chamber_name,$request->chamber_no,auth()->user()->Id,1]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Chamber Successfully Added !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_chamber_list(Int $org_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Floor_Id,Chamber_Name,Chamber_No From chamber_master Order By Id");

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

    public function update_chamber(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'chamber_id' => 'required',
            'floor_id'=>'required',
            'chamber_name' => 'required',
            'chamber_no' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_CHAMBER(?,?,?,?,?,?,@error,@message);",[$request->chamber_id,$request->floor_id,$request->chamber_name,$request->chamber_no,auth()->user()->Id,2]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Chamber Successfully Updated !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_flrwise_chamber(Int $org_id,Int $floor_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Concat(Chamber_Name,' - ',Chamber_No) As Chamber_Name From chamber_master Where Floor_Id=? Order By Id",[$floor_id]);

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

    public function process_rack(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'floor_id' => 'required',
            'chamber_id' => 'required',
            'rack_name' => 'required',
            'rack_no' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_RACK(?,?,?,?,?,?,?,@error,@message);",[null,$request->floor_id,$request->chamber_id,$request->rack_name,$request->rack_no,auth()->user()->Id,1]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Rack Successfully Added !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_rack_list(Int $org_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Floor_Id,Chamber_Id,Rack_Name,Rack_No From mst_rack_master Order By Id");

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

    public function update_rack(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'rack_id' => 'required',
            'floor_id' => 'required',
            'chamber_id' => 'required',
            'rack_name' => 'required',
            'rack_no' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_RACK(?,?,?,?,?,?,?,@error,@message);",[$request->rack_id,$request->floor_id,$request->chamber_id,$request->rack_name,$request->rack_no,auth()->user()->Id,2]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Rack Successfully Updated !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function process_pocket(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'rack_id' => 'required',
            'pocket_name' => 'required',
            'pocket_no' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_POCKET(?,?,?,?,?,?,@error,@message);",[null,$request->rack_id,$request->pocket_name,$request->pocket_no,auth()->user()->Id,1]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Pocket Successfully Added !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_pocket_list(Int $org_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Rack_Id,Pocket_Name,Pocket_No From mst_pocket_master Order By Id");

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

    public function update_pocket(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'pocket_id' => 'required',
            'rack_id' => 'required',
            'pocket_name' => 'required',
            'pocket_no' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_POCKET(?,?,?,?,?,?,@error,@message);",[$request->pocket_id,$request->rack_id,$request->pocket_name,$request->pocket_no,auth()->user()->Id,2]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Pocket Successfully Updated !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function process_agent(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'agent_name' => 'required',
            'agent_rel' => 'required',
            'agent_add' => 'required',
            'agent_mob' => 'required',
            'percent' => 'required',
            'comm_rate' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_AGENT(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[null,$request->agent_name,$request->agent_rel,$request->agent_add,$request->agent_mob,$request->agent_mail,$request->dep_amt,$request->percent,$request->comm_rate,auth()->user()->Id,1]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Agent Successfully Added !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    }  
    }

    public function get_agent_list(Int $org_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Agent_Name,Relation_Name,Address,Mobile,Mail,Deposit_Amt,Is_Percent,Comm_Rate From mst_agent_master Order By Id");

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

    public function update_agent(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'agent_id' => 'required',
            'agent_name' => 'required',
            'agent_rel' => 'required',
            'agent_add' => 'required',
            'agent_mob' => 'required',
            'percent' => 'required',
            'comm_rate' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_EDIT_AGENT(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->agent_id,$request->agent_name,$request->agent_rel,$request->agent_add,$request->agent_mob,$request->agent_mail,$request->dep_amt,$request->percent,$request->comm_rate,auth()->user()->Id,2]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Agent Successfully Updated !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function process_rent(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'basic_rate' => 'required',
            'insurance' => 'required',
            'rms_fees' => 'required',
            'drying_fees' => 'required',
            'valid_frm' => 'required',
            'valid_till' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_RENT_MASTER(?,?,?,?,?,?,?);",[$request->basic_rate,$request->insurance,$request->rms_fees,$request->drying_fees,$request->valid_frm,$request->valid_till,auth()->user()->Id]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Rent Successfully Added !!',
                    'details' => null,
                ],200);
            
            
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_bank_ledger(){
        try {
            $result = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head=? And Is_Active=1", [2]);
            
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

    public function process_bank_account(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'bank_name' => 'required',
            'branch_name' => 'required',
            'ifsc' => 'required',
            'account_no' => 'required',
            'gl_id' => 'required',
            'open_bal' => 'required',
            'open_date' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_BANK_ACCOUNT(?,?,?,?,?,?,?,?,@error,@message);",[$request->bank_name,$request->branch_name,$request->ifsc,$request->account_no,$request->gl_id,$request->open_bal,$request->open_date,auth()->user()->Id]);

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
                ],202);
            }
            else{
                DB::connection('chill')->commit();
                return response()->json([
                    'message' => 'Bank Account Successfully Added !!',
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
            ],202);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_bank_account_list(Int $org_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Bank_Name,Branch_Name,Bank_IFSC,Account_No From mst_bank_account Order By Id");

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

    public function get_chamber_rack(Int $org_id,Int $chamber_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Rack_Name From mst_rack_master Where Chamber_Id=? Order By Id",[$chamber_id]);

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

    public function get_rack_pocket(Int $org_id,Int $rack_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select Id,Pocket_Name From mst_rack_master Where Rack_Id=? Order By Id",[$rack_id]);

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
}