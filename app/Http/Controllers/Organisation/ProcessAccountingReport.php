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

class ProcessAccountingReport extends Controller
{
    public function process_daybook(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.wax', $db);

            $sql = DB::connection('wax')->select("Call USP_RPT_DAYBOOK(?);",[$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $daubook_data = [];
           
            foreach ($sql as $daybook) {
                if($daybook->Open_Balance){
                    $daubook_data['Opening_Balance']=[
                        'Opening_Cash' => $daybook->Open_Balance,
                        'Receipt_Data' => [],
                        'Payment_Data'=>[],
                        'Closing_Cash'=>'',
                    ];
                }

                if($daybook->Rec_Vouch){
                    $daubook_data['Opening_Balance']['Receipt_Data'][]=[
                        'Vouch_No' => $daybook->Rec_Vouch,
                        'Ledger_Name' => $daybook->Ledger_Name,
                        'Cash_Amt' => $daybook->Receipt_Cash,
                        'Trf_Amt' => $daybook->Receipt_Trf,
                        'Tot_Amt' => $daybook->Tot_Receipt
                    ];
                }

                if($daybook->Payment_Vouch){
                    $daubook_data['Opening_Balance']['Payment_Data'][]=[
                        'Vouch_No' => $daybook->Payment_Vouch,
                        'Ledger_Name' => $daybook->Ledger_Name,
                        'Cash_Amt' => $daybook->Payment_Cash,
                        'Trf_Amt' => $daybook->Payment_Transfer,
                        'Tot_Amt' => $daybook->Payment_Total
                    ];
                }

                 if($daybook->Closing_Balance){
                    $daubook_data['Opening_Balance']['Closing_Cash']=$daybook->Closing_Balance;
                }
            }
            $daubook_data = array_values($daubook_data);
            return response()->json([
                'message' => 'Data Found',
                'details' => $daubook_data,
            ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_cash_acct(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.wax', $db);

            $sql = DB::connection('wax')->select("Call USP_RPT_CASH_ACCOUNT(?,?);",[$request->form_date,$request->to_date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $daubook_data = [];
           
            foreach ($sql as $daybook) {
                if($daybook->Open_Balance){
                    $daubook_data['Opening_Balance']=[
                        'Opening_Cash' => $daybook->Open_Balance,
                        'Receipt_Data' => [],
                        'Payment_Data'=>[],
                        'Closing_Cash'=>'',
                    ];
                }

                if($daybook->Rec_Vouch){
                    $daubook_data['Opening_Balance']['Receipt_Data'][]=[
                        'Vouch_No' => $daybook->Rec_Vouch,
                        'Ledger_Name' => $daybook->Ledger_Name,
                        'Cash_Amt' => $daybook->Receipt_Cash,
                        'Trf_Amt' => $daybook->Receipt_Trf,
                        'Tot_Amt' => $daybook->Tot_Receipt
                    ];
                }

                if($daybook->Payment_Vouch){
                    $daubook_data['Opening_Balance']['Payment_Data'][]=[
                        'Vouch_No' => $daybook->Payment_Vouch,
                        'Ledger_Name' => $daybook->Ledger_Name,
                        'Cash_Amt' => $daybook->Payment_Cash,
                        'Trf_Amt' => $daybook->Payment_Transfer,
                        'Tot_Amt' => $daybook->Payment_Total
                    ];
                }

                 if($daybook->Closing_Balance){
                    $daubook_data['Opening_Balance']['Closing_Cash']=$daybook->Closing_Balance;
                }
            }
            $daubook_data = array_values($daubook_data);
            return response()->json([
                'message' => 'Data Found',
                'details' => $daubook_data,
            ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_cashbook(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.wax', $db);

            $sql = DB::connection('wax')->select("Call USP_RPT_CASHBOOK(?);",[$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $daubook_data = [];
           
            foreach ($sql as $daybook) {
                if($daybook->Opening_Balance){
                    $daubook_data['Opening_Balance']=[
                        'Opening_Cash' => $daybook->Opening_Balance,
                        'Receipt_Data' => [],
                        'Payment_Data'=>[],
                        'Closing_Cash'=>'',
                    ];
                }

                if($daybook->Rec_Vouch){
                    $daubook_data['Opening_Balance']['Receipt_Data'][]=[
                        'Vouch_No' => $daybook->Rec_Vouch,
                        'Ledger_Name' => $daybook->Ledger_Name,
                        'Particular' => $daybook->Rec_Particular,
                        'Amount' => $daybook->Rec_Amt
                    ];
                }

                if($daybook->Pay_Vouch){
                    $daubook_data['Opening_Balance']['Payment_Data'][]=[
                        'Vouch_No' => $daybook->Pay_Vouch,
                        'Ledger_Name' => $daybook->Ledger_Name,
                        'Particular' => $daybook->Pay_Particular,
                        'Amount' => $daybook->Pay_Amt
                    ];
                }

                 if($daybook->Closing_Balance){
                    $daubook_data['Opening_Balance']['Closing_Cash']=$daybook->Closing_Balance;
                }
            }
            $daubook_data = array_values($daubook_data);
            return response()->json([
                'message' => 'Data Found',
                'details' => $daubook_data,
            ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_bank_ledger(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.wax', $db);

            $sql = DB::connection('wax')->select("Call USP_RPT_BANK_LEDGER(?,?,?);",[$request->bank_id,$request->form_date,$request->to_date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $daubook_data = [];
           
            foreach ($sql as $daybook) {
                if(!isset($daubook_data[$daybook->Bank_Id])){
                    $daubook_data[$daybook->Bank_Id]=[
                        'Bank_Id' => $daybook->Bank_Id,
                        'Bank_Name' => $daybook->Bank_Name,
                        'Branch_Name' => $daybook->Branch_Name,
                        'IFSC' => $daybook->Bank_IFSC,
                        'Account_No' => $daybook->Account_No,
                        'Transaction_Data' => [],
                    ];
                }

                if($daybook->Trans_Id){
                    if(!isset($daubook_data[$daybook->Bank_Id]['Transaction_Data'][$daybook->Trans_Id])){
                        $daubook_data[$daybook->Bank_Id]['Transaction_Data'][]=[
                            'Trans_Date' => $daybook->Trans_Date,
                            'Particular' => $daybook->Particular,
                            'Debit' => $daybook->Dr_Amount,
                            'Credit' => $daybook->Cr_Amount,
                            'Balance' => $daybook->Balance,
                            'Balance_Type' => $daybook->Balance_Type,
                        ];
                    }
                }
            }
            $daubook_data = array_values($daubook_data);
            return response()->json([
                'message' => 'Data Found',
                'details' => $daubook_data,
            ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_ledger_list(){
        try {

            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Id<>3;");

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

    public function process_ledger(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.wax', $db);

            $sql = DB::connection('wax')->select("Call USP_RPT_ACCT_LEDGER(?,?,?);",[$request->form_date,$request->to_date,$request->ledger_id]);

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