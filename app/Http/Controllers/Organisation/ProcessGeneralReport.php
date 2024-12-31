<?php
namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;

class ProcessGeneralReport extends Controller
{
    public function process_booking_register(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'type' => 'required'
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
            
            $sql = DB::connection('chill')->select("Call USP_RPT_GENBOOKING_REGISTER(?,?,?);",[$request->form_date,$request->to_date,$request->type]);
            
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'Error While Calculating !!',
                    'details' => null,
                ], 202);
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
    else{
        $errors = $validator->errors();
    
            $response = response()->json([
                'message' => $errors->messages(),
                'details' => null,
            ],202);
        
            throw new HttpResponseException($response);
    }
    }

    public function process_bond_register(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'type' => 'required'
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
            
            $sql = DB::connection('chill')->select("Call USP_RPT_BOND_REGISTER(?,?,?);",[$request->form_date,$request->to_date,$request->type]);
            
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'Error While Calculating !!',
                    'details' => null,
                ], 202);
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
    else{
        $errors = $validator->errors();
    
            $response = response()->json([
                'message' => $errors->messages(),
                'details' => null,
            ],202);
        
            throw new HttpResponseException($response);
    }
    }

    public function process_collection_register(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required'
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
            
            $sql = DB::connection('chill')->select("Call USP_RPT_COLLECTION_REGISTER(?,?);",[$request->form_date,$request->to_date]);
            
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'Error While Calculating !!',
                    'details' => null,
                ], 202);
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