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

class ProcessProcessing extends Controller
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

    public function process_general_booking(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'booking_date' => 'required',
            'cust_name' => 'required',
            'relation_name' => 'required',
            'village' => 'required',
            'post' => 'required',
            'pin' => 'required',
            'dist' => 'required',
            'mob' => 'required',
            'qunty' => 'required',
            'amount' => 'required',
            'valid_till' => 'required',
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
            $sql = DB::connection('chill')->statement("Call USP_ADD_GEN_BOOKING(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@book);",[$request->org_id,$request->cust_id,$request->booking_date,$request->cust_name,$request->relation_name,$request->village,$request->post,$request->pin,$request->dist,$request->mob,$request->qunty,$request->amount,$request->bank_id,$request->valid_till,$request->ref_vouch,auth()->user()->Id,$request->fin_id]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::connection('chill')->select("Select @error As Error_No,@message As Message,@book As Bok_Id;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;
            $booking_Id = $result[0]->Bok_Id;

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
                    'details' => $booking_Id,
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

    public function get_gen_booking(Int $org_id,Int $book_id){
        try {
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.chill', $db);

            $sql = DB::connection('chill')->select("Call USP_GET_BOOKING_DATA(?);",[$book_id]);

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

    public function get_customer(Int $org_id, String $keyword)
{
    try {
        // Step 1: Retrieve the organization's schema name
        $schemaQuery = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db", [$org_id]);
        if (!$schemaQuery || !isset($schemaQuery[0]->db)) {
            throw new Exception("Schema not found for the provided organization ID: $org_id");
        }

        $org_schema = $schemaQuery[0]->db;

        // Step 2: Dynamically update database configuration
        $dbConfig = Config::get('database.connections.mysql');
        if (!$dbConfig) {
            throw new Exception("MySQL connection configuration is missing.");
        }

        $dbConfig['database'] = $org_schema; // Set schema dynamically
        config()->set('database.connections.chill', $dbConfig);

        // Step 3: Perform the database query on the dynamic connection
        $customerQuery = DB::connection('chill')->select(
            "SELECT Id, Cust_Name, Relation_Name, Village 
             FROM mst_customer 
             WHERE Cust_Name LIKE ?",
            ["%$keyword%"]
        );

        // Step 4: Handle the case where no data is found
        if (empty($customerQuery)) {
            return response()->json([
                'message' => 'No Data Found',
                'details' => null,
            ], 202);
        }

        // Step 5: Return successful response with data
        return response()->json([
            'message' => 'Data Found',
            'details' => $customerQuery,
        ], 200);

    } catch (Exception $ex) {
        // Log the error for debugging
        Log::error('Error in fetching customer data:', ['error' => $ex->getMessage()]);

        // Return error response
        return response()->json([
            'message' => 'Error Found',
            'details' => $ex->getMessage(),
        ], 500);
    }
}


}