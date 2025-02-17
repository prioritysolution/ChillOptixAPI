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
use \stdClass;

class ProcessLoan extends Controller
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

    public function get_customer_bond(Request $request){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Select m.Id As Bond_Id,m.Bond_No,m.Issue_Date,b.Book_No,m.Bond_No,m.Issue_Pack,b.Bok_Date,c.Id,c.Cust_Name,c.Relation_Name,Concat(c.Village,', ',c.Post_Off,', ',c.Dist,', ',c.Pin_Code) As Address,c.Mobile From mst_bond_master m 
                                                    Join mst_gen_booking b On b.Id=m.Book_Id 
                                                    Join mst_customer c On c.Id=b.Cust_Id
                                                    Where b.Cust_Id=? And m.Is_Loan=0;",[$request->cust_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => null,
                ], 202);
            }
            $bond_data = [];
            foreach ($sql as $bond) {
                if(!isset($bond_data[$bond->Id])){
                    $bond_data[$bond->Id]=[
                        'Id' => $bond->Id,
                        'Customer_Name' => $bond->Cust_Name,
                        'Relation_Name' => $bond->Relation_Name,
                        'Address' => $bond->Address,
                        'Mobile' => $bond->Mobile,
                        'Bond_Data' => [],
                       ];
                }
                if(!isset($bond_data[$bond->Id]['Bond_Data'][$bond->Bond_Id])){
                    $bond_data[$bond->Id]['Bond_Data'][] = [
                        'Bond_Id' => $bond->Bond_Id,
                        'Bond_No' => $bond->Bond_No,
                        'Issue_Date' => $bond->Issue_Date,
                        'Issue_Qnty' => $bond->Issue_Pack,
                        'Booking_No' => $bond->Book_No,
                        'Booking_Date' => $bond->Bok_Date,
                    ];
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

    public function process_loan(Request $request){
    $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'cust_id' => 'required',
            'appl_date' => 'required',
            'loan_amt' => 'required',
            'duration' => 'required',
            'roi' => 'required',
            'fin_id' => 'required',
            'bond_data' => 'required',
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

            $drop_table = DB::connection('chill')->statement("Drop Temporary Table if Exists tempbonddata;");
            $create_tabl = DB::connection('chill')->statement("Create Temporary Table tempbonddata
                                                                (
                                                                    Bond_Id			Int,
                                                                    Bond_Date		Date,
                                                                    Bond_Qnty		Int
                                                                );");
            $bond_data = $this->convertToObject($request->bond_data);
            foreach ($bond_data as $bond) {
                DB::connection('chill')->statement("Insert Into tempbonddata (Bond_Id,Bond_Date,Bond_Qnty) Values (?,?,?);",[$bond->bond_id,$bond->bond_date,$bond->bond_qnty]);
            }
            $sql = DB::connection('chill')->statement("Call USP_ADD_LOAN(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->org_id,$request->cust_id,$request->appl_date,$request->man_acct,$request->ledg_fol,$request->loan_amt,$request->duration,$request->roi,$request->fin_id,$request->bank_id,auth()->user()->Id]);

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

public function search_loan_account(Request $request){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("SELECT m.Id, m.Account_No, m.Manual_Acct, m.Ledg_Folio, 
        c.Cust_Name, c.Relation_Name, 
        CONCAT(c.Village, ', ', c.Post_Off, ', ', c.Dist, ', ', c.Pin_Code) AS Address, 
        c.Mobile 
        FROM mst_loan_account m 
        JOIN mst_customer c ON c.Id = m.Cust_Id 
        WHERE c.Cust_Name LIKE ?;", ["%{$request->cust_name}%"]);

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

public function get_loan_details(Request $request){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("Select m.Id,m.Account_No,m.Manual_Acct,m.Ledg_Folio,m.Appl_Amount As Loan_Amount,m.Appl_Date,m.ROI,m.Duration,m.Repay_Within,c.Cust_Name,c.Relation_Name,Concat(c.Village,', ',c.Post_Off,', ',c.Dist,', ',c.Pin_Code) As Address,c.Mobile,b.Id As Bond_Id,b.Bond_No,b.Issue_Date,b.Issue_Pack,UDF_CAL_LOAN_BALANCE(m.Id,?) As Loan_Balance,UDF_CAL_LOAN_INTT(m.Id,?,1) As Intt,UDF_CAL_LOAN_INTT(m.Id,?,2) As Days From mst_loan_account m 
                                                Join mst_customer c On c.Id=m.Cust_Id 
                                                Join mst_loan_bond_details d On d.Acct_Id=m.Id 
                                                Join mst_bond_master b On b.Id=d.Bond_Id Where m.Account_No=?;",[$request->date,$request->date,$request->date,$request->acct_no]);

        if (empty($sql)) {
            // Custom validation for no data found
            return response()->json([
                'message' => 'No Data Found',
                'details' => null,
            ], 202);
        }
        $bond_data = [];
        foreach ($sql as $bond) {
            if(!isset($bond_data[$bond->Id])){
                $bond_data[$bond->Id]=[
                    'Id' => $bond->Id,
                    'Customer_Name' => $bond->Cust_Name,
                    'Relation_Name' => $bond->Relation_Name,
                    'Address' => $bond->Address,
                    'Mobile' => $bond->Mobile,
                    'Account_No' => $bond->Account_No,
                    'Manual_Account_No' => $bond->Manual_Acct,
                    'Ledger_Folio' => $bond->Ledg_Folio,
                    'Roi' => $bond->ROI,
                    'Duration' => $bond->Duration,
                    'repay_within' => $bond->Repay_Within,
                    'Loan_Amount' => $bond->Loan_Amount,
                    'Loan_Date' => $bond->Appl_Date,
                    'Loan_Balance' => $bond->Loan_Balance,
                    'Interest' => $bond->Intt,
                    'No_Days' => $bond->Days,
                    'Bond_Data' => [],
                   ];
            }
            if(!isset($bond_data[$bond->Id]['Bond_Data'][$bond->Bond_Id])){
                $bond_data[$bond->Id]['Bond_Data'][] = [
                    'Bond_Id' => $bond->Bond_Id,
                    'Bond_No' => $bond->Bond_No,
                    'Issue_Date' => $bond->Issue_Date,
                    'Issue_Qnty' => $bond->Issue_Pack,
                ];
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

public function process_loan_repay(Request $request){
    $validator = Validator::make($request->all(),[
        'org_id' => 'required',
        'repay_date' => 'required',
        'account_id' => 'required',
        'prn_refund' => 'required',
        'intt_refund' => 'required',
        'actual_intt' => 'required',
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

        $sql = DB::connection('chill')->statement("Call USP_ADD_LOAN_REPAY(?,?,?,?,?,?,?,?,?,@error,@message);",[$request->org_id,$request->repay_date,$request->account_id,$request->prn_refund,$request->intt_refund,$request->actual_intt,$request->bank_id,$request->fin_id,auth()->user()->Id]);

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

public function process_loan_ledger(Request $request){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("Call USP_RPT_LOAN_LEDGER(?,?)",[$request->acct_no,$request->date]);

        if (empty($sql)) {
            // Custom validation for no data found
            return response()->json([
                'message' => 'No Data Found',
                'details' => null,
            ], 202);
        }
        $bond_data = [];
        foreach ($sql as $bond) {
            if(!isset($bond_data[$bond->Account_No])){
                $bond_data[$bond->Account_No]=[
                    'Account_No' => $bond->Account_No,
                    'Customer_Name' => $bond->Cust_Name,
                    'Relation_Name' => $bond->Relation_Name,
                    'Address' => $bond->Address,
                    'Mobile' => $bond->Mobile,
                    'Manual_Account_No' => $bond->Manual_Acct_No,
                    'Ledger_Folio' => $bond->Ledg_Fol,
                    'Roi' => $bond->Roi,
                    'Duration' => $bond->Duration,
                    'Repay_Data' => [],
                    'Bond_Data' => [],
                   ];
            }
            if($bond->Bond_Id){
                if(!isset($bond_data[$bond->Account_No]['Bond_Data'][$bond->Bond_Id])){
                    $bond_data[$bond->Account_No]['Bond_Data'][] = [
                        'Book_No' => $bond->Book_No,
                        'Bond_No' => $bond->Bond_No,
                        'Issue_Date' => $bond->Issue_Date,
                        'Issue_Qnty' => $bond->Issue_Qnty,
                    ];
                }
            }
            
            if($bond->Trans_Id){
                if(!isset($bond_data[$bond->Account_No]['Repay_Data'][$bond->Trans_Id])){
                    $bond_data[$bond->Account_No]['Repay_Data'][] = [
                        'Trans_Date' => $bond->Trans_Date,
                        'Loan_Amt' => $bond->Loan_Amt,
                        'Repay_Prn' => $bond->Prn_Paid,
                        'Repay_Intt' => $bond->Intt_Paid,
                        'Balance' => $bond->Balance,
                        'Due_Intt' => $bond->Due_Intt
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