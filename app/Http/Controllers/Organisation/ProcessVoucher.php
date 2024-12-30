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

class ProcessVoucher extends Controller
{
    public function get_ledger_list(){
        try {

            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head Not In (1,2,3,6,8,9);");
            
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

    public function process_voucher(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'trans_date' => 'required',
            'ledger_id' => 'required',
            'vouch_type' => 'required',
            'particular' => 'required',
            'amount' => 'required',
            'fin_id' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_VOUCHER(?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ledger_id,$request->vouch_type,$request->particular,$request->ref_vouch,$request->amount,$request->fin_id,auth()->user()->Id]);

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
                    'message' => $message,
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

    public function get_bank_balance(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'bank_Id' => 'required',
            'date' => 'required'
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
            
            $sql = DB::connection('chill')->select("Select UDF_CAL_BANK_BALANCE(?,?) As Cal_Data;",[$request->bank_Id,$request->date]);
            $cal_data = $sql[0]->Cal_Data;
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'Error While Calculating !!',
                    'details' => null,
                ], 202);
            }
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $cal_data,
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

    public function process_bank_deposit(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'trans_date' => 'required',
            'bank_id' => 'required',
            'particulars' => 'required',
            'amount' => 'required',
            'fin_id' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->bank_id,$request->trans_date,$request->ref_vouch,$request->particulars,$request->amount,null,null,$request->fin_id,auth()->user()->Id,1]);

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
                    'message' => $message,
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

    public function process_bank_withdrwan(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'trans_date' => 'required',
            'bank_id' => 'required',
            'particulars' => 'required',
            'amount' => 'required',
            'fin_id' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->bank_id,$request->trans_date,$request->ref_vouch,$request->particulars,$request->amount,null,null,$request->fin_id,auth()->user()->Id,2]);

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
                    'message' => $message,
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

    public function process_bank_transfer(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'trans_date' => 'required',
            'bank_id' => 'required',
            'particulars' => 'required',
            'amount' => 'required',
            'fin_id' => 'required'
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->bank_id,$request->trans_date,$request->ref_vouch,$request->particulars,$request->amount,$request->gl_id,$request->trf_id,$request->fin_id,auth()->user()->Id,3]);

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
                    'message' => $message,
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
}