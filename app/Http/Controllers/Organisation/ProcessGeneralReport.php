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
                    'message' => 'No Data Found !!',
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
                    'message' => 'No Data Found !!',
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
                    'message' => 'No Data Found !!',
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

    public function process_customer_enquery(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);
    
            $sql = DB::connection('chill')->select("Call USP_RPT_CUST_ENQUERY(?,?)",[$request->cust_id,$request->date]);
    
            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $bond_data = [];
            foreach ($sql as $bond) {
                if(!isset($bond_data[$bond->Cust_Id])){
                    $bond_data[$bond->Cust_Id]=[
                        'Cust_Id' => $bond->Cust_Id,
                        'Cust_Name' => $bond->Cust_Name,
                        'Relation_Name' => $bond->Relation_Name,
                        'Address' => $bond->Address,
                        'Mobile' => $bond->Mobile,
                        'Bond_Data' => [],
                        'Loan_Data' => [],
                       ];
                }
                if($bond->Bond_Id){
                    if(!isset($bond_data[$bond->Cust_Id]['Bond_Data'][$bond->Bond_Id])){
                        $bond_data[$bond->Cust_Id]['Bond_Data'][] = [
                            'Book_No' => $bond->Book_No,
                            'Bond_No' => $bond->Bond_No,
                            'Issue_Date' => $bond->Issue_Date,
                            'Issue_Qnty' => $bond->Issue_Pack,
                        ];
                    }
                }
                if($bond->Account_No){
                    if(!isset($bond_data[$bond->Cust_Id]['Loan_Data'][$bond->Account_No])){
                        $bond_data[$bond->Cust_Id]['Loan_Data'][]=[
                            'Account_No' => $bond->Account_No,
                            'Manual_Account' => $bond->Manual_Acct,
                            'Ledger_Folio' => $bond->Ledg_Folio,
                            'Application_Date' => $bond->Appl_Date,
                            'Loan_Amount' => $bond->Appl_Amount,
                            'Balance' => $bond->Balance
                        ];
                    }
                }
            }
    
            $bond_data = array_values($bond_data);
    
            return response()->json([
                'message' => 'Data Found',
                'details' => $bond_data,
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