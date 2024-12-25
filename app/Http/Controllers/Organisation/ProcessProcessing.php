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

            $sql = DB::connection('chill')->select("Call USP_GET_BOOKING_DATA(?,?);",[$book_id,1]);

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
            "SELECT Id, Cust_Name, Relation_Name, Village,Post_Off,Pin_Code,Dist,Mobile 
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

public function get_booking_details(Int $org_id,Int $book_no){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("Call USP_GET_BOOKING_DATA(?,?);",[$book_no,2]);

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

public function search_booking_data(Int $org_id,Int $type,String $keyword){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("Call USP_SEARCH_BOOKING(?,?);",[$type,$keyword]);

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

public function process_bond(Request $request){
    $validator = Validator::make($request->all(),[
        'org_id' => 'required',
        'book_id' => 'required',
        'bond_date' => 'required',
        'bond_data' => 'required',
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

        $drop_table = DB::connection('chill')->statement("Drop Temporary Table If Exists tempbonddata;");
        $create_tabl = DB::connection('chill')->statement("Create Temporary Table tempbonddata
                                        (
                                            Bond_Qnty				Numeric(18,2),
                                            Bond_Pack				Int,
                                            Is_Verified				Int
                                        );");
        $bond_data = $this->convertToObject($request->bond_data);
        foreach ($bond_data as $bond) {
            DB::connection('chill')->statement("Insert Into tempbonddata (Bond_Qnty,Bond_Pack,Is_Verified) Values (?,?,?);",[$bond->bond_qnty,$bond->bond_pack,$bond->verified]);
         }
        $sql = DB::connection('chill')->statement("Call USP_POST_BOND_ENTRY(?,?,?,?,@error,@message,@bond);",[$request->book_id,$request->bond_date,$request->fin_id,auth()->user()->Id]);

        if(!$sql){
            throw new Exception('Operation Error Found !!');
        }
        $result = DB::connection('chill')->select("Select @error As Error_No,@message As Message,@bond As bond_details;");
        $error_No = $result[0]->Error_No;
        $message = $result[0]->Message;
        $booking_Id = $result[0]->bond_details;

        $bond_details_decoded = json_decode($booking_Id);

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
                'details' => $bond_details_decoded,
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

public function search_bond(Int $org_id, Int $type,String $keyword){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("Call USP_SEARCH_BOND(?,?);",[$keyword,$type]);

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

public function get_bond_details(Int $org_id,Int $bond_id){
    try {
        $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
        if(!$sql){
          throw new Exception;
        }
        $org_schema = $sql[0]->db;
        $db = Config::get('database.connections.mysql');
        $db['database'] = $org_schema;
        config()->set('database.connections.chill', $db);

        $sql = DB::connection('chill')->select("Call USP_GET_BOND_DETAILS(?,?);",[$bond_id,2]);

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

public function process_rack_posting(Request $request){
    $validator = Validator::make($request->all(),[
        'org_id' => 'required',
        'bond_id' => 'required',
        'post_date' => 'required',
        'rack_details' => 'required'
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

        $drop_table = DB::connection('chill')->statement("Drop Temporary Table If Exists temprackdata;");
        $create_tabl = DB::connection('chill')->statement("Create Temporary Table temprackdata
                                        (
                                            floor_id		Int,
                                            chamb_id		Int,
                                            rack_id			Int,
                                            pocket_id		Int,
                                            no_pack			Int
                                        );");
        $rack_details = $this->convertToObject($request->rack_details);
        foreach ($rack_details as $rack) {
            DB::connection('chill')->statement("Insert Into tempbonddata (floor_id,chamb_id,rack_id,pocket_id,no_pack) Values (?,?,?,?,?);",[$rack->floor,$rack->chamber,$rack->rack,$rack->pocket,$rack->no_pack]);
         }
        $sql = DB::connection('chill')->statement("Call USP_ADD_RACK_POSTING(?,?,?);",[$request->bond_id,$request->post_date,auth()->user()->Id]);

        if(!$sql){
            throw new Exception('Operation Error Found !!');
        }
            DB::connection('chill')->commit();
            return response()->json([
                'message' => 'Rack Posting Is Successfull !!',
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

}