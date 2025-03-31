<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin Controller Start
use App\Http\Controllers\Admin\AdminLogin;
use App\Http\Controllers\Admin\ProcessOrgination;

// Admin Controller End

// User Controller Start
use App\Http\Controllers\Organisation\UserLogin;
use App\Http\Controllers\Organisation\ProcessMaster;
use App\Http\Controllers\Organisation\ProcessProcessing;
use App\Http\Controllers\Organisation\ProcessRectify;
use App\Http\Controllers\Organisation\ProcessVoucher;
use App\Http\Controllers\Organisation\ProcessGeneralReport;
use App\Http\Controllers\Organisation\ProcessLoan;
use App\Http\Controllers\Organisation\ProcessAccountingReport;
// User Controller End Here
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// admin route area

// login route start
Route::post('/Admin/ProcessLogin',[AdminLogin::class,'process_admin_login'])->middleware('api_access');

Route::group([
    'middleware' => ['auth:sanctum',]
],function(){

    // login & Dashboard Route

    Route::get('/Admin/GetSideBar',[AdminLogin::class,'get_admin_dashboard']);
    Route::get('/Admin/ProcessLogOut',[AdminLogin::class,'process_log_out']);
    Route::post('/Admin/AddUser',[AdminLogin::class,'process_admin_user']);
    Route::get('/Admin/GetUserList',[AdminLogin::class,'get_user_list']);
    Route::get('/Admin/GetModuleList',[AdminLogin::class,'get_module_list']);
    Route::post('/Admin/MapUserModule',[AdminLogin::class,'process_map_user_module']);

    // Login & Dashboard Route

    // Process Orgination Route Start Here

    Route::post('/Admin/ProcessOrg/AddOrg',[ProcessOrgination::class,'process_org']);
    Route::get('/Admin/ProcessOrg/GetOrgList',[ProcessOrgination::class,'get_org_list']);
    Route::post('/Admin/ProcessOrg/UpdateOrg',[ProcessOrgination::class,'update_org']);
    Route::post('/Admin/ProcessOrg/AddFinYear',[ProcessOrgination::class,'process_org_finyear']);
    Route::get('/Admin/ProcessOrg/CheckRental/{org_id}',[ProcessOrgination::class,'check_org_rental']);
    Route::post('/Admin/ProcessOrg/AddRental',[ProcessOrgination::class,'process_rental']);
    Route::get('/Admin/ProcessOrg/GetUserRole',[ProcessOrgination::class,'get_org_user_role']);
    Route::post('/Admin/ProcessOrg/AddUser',[ProcessOrgination::class,'process_org_user']);
    Route::get('/Admin/ProcessOrg/GetAcctMainHead',[ProcessOrgination::class,'get_account_head']);
    Route::post('/Admin/ProcessOrg/AddAccountHead',[ProcessOrgination::class,'process_acct_head']);
    Route::get('/Admin/ProcessOrg/GetAccountHead',[ProcessOrgination::class,'get_acct_head_list']);
    Route::put('/Admin/ProcessOrg/UpdateAccountHead',[ProcessOrgination::class,'update_acct_head']);
    Route::post('/Admin/ProcessOrg/AddAccountLedger',[ProcessOrgination::class,'process_acct_ledger']);
    Route::get('/Admin/ProcessOrg/GetAccountLedger',[ProcessOrgination::class,'get_acct_ledger_list']);
    Route::put('/Admin/ProcessOrg/UpdateAccountLedger',[ProcessOrgination::class,'update_acct_ledger']);
    Route::post('/Admin/ProcessOrg/MapDefaultLedger',[ProcessOrgination::class,'process_default_ledger']);


    // Orgination Route End Here
});

// login route end

//admin route end area





// User Route Area

// Login Route

Route::post('/Org/ProcessLogin',[UserLogin::class,'process_user_login'])->middleware('api_access');
Route::get('/Org/GetOtpForForgotPassword',[UserLogin::class,'genereate_otp']);
Route::get('/Org/VerifyOtp',[UserLogin::class,'verify_otp']);
Route::put('/Org/ForgotUserPassword',[UserLogin::class,'forgot_passsword']);

Route::group([
    'middleware' => ['auth:sanctum',]
],function(){

// Dashboard Route Start
Route::get('/Org/GetSideBar',[UserLogin::class,'get_user_sidebar']);
Route::get('/Org/GetDashboard',[UserLogin::class,'process_dashboard']);
Route::get('/Org/ProcessLogout',[UserLogin::class,'process_log_out']);
Route::get('/Org/GetProfile',[UserLogin::class,'get_user_profile']);
Route::get('/Org/GetUserRole',[UserLogin::class,'get_user_role']);
Route::post('/Org/AddUser',[UserLogin::class,'process_user']);
Route::put('/Org/UpdateProfile',[UserLogin::class,'process_update_profile']);
Route::get('/Org/GetNotification',[UserLogin::class,'process_notification']);
Route::get('/Org/GetRoleUserList',[UserLogin::class,'get_role_user_list']);
Route::get('/Org/GetUserRoleMenue',[UserLogin::class,'get_user_role_menue']);
Route::post('/Org/MapUserRole',[UserLogin::class,'process_map_role']);
Route::get('/Org/CheckFinYear',[UserLogin::class,'process_check_fin']);


// Dashboard End
// Master Route Start

Route::post('/Org/Master/AddFloor',[ProcessMaster::class,'process_floor']);
Route::get('/Org/Master/GetFloor/{org_id}',[ProcessMaster::class,'get_floor_list']);
Route::put('/Org/Master/UpdateFloor',[ProcessMaster::class,'update_floor']);
Route::post('/Org/Master/AddChamber',[ProcessMaster::class,'process_chamber']);
Route::get('/Org/Master/GetChamber/{org_id}',[ProcessMaster::class,'get_chamber_list']);
Route::put('/Org/Master/UpdateChamber',[ProcessMaster::class,'update_chamber']);
Route::get('/Org/Master/GetFloorChamber/{org_id}/{floor_id}',[ProcessMaster::class,'get_flrwise_chamber']);
Route::post('/Org/Master/AddRack',[ProcessMaster::class,'process_rack']);
Route::get('/Org/Master/GetRack/{org_id}',[ProcessMaster::class,'get_rack_list']);
Route::put('/Org/Master/UpdateRack',[ProcessMaster::class,'update_rack']);
Route::post('/Org/Master/AddPocket',[ProcessMaster::class,'process_pocket']);
Route::get('/Org/Master/GetPocket/{org_id}',[ProcessMaster::class,'get_pocket_list']);
Route::put('/Org/Master/UpdatePocket',[ProcessMaster::class,'update_pocket']);
Route::post('/Org/Master/AddAgent',[ProcessMaster::class,'process_agent']);
Route::get('/Org/Master/GetAgent',[ProcessMaster::class,'get_agent_list']);
Route::put('/Org/Master/UpdateAgent',[ProcessMaster::class,'update_agent']);
Route::get('/Org/Master/GetLastRentDate/{org_id}',[ProcessMaster::class,'get_last_rent_date']);
Route::post('/Org/Master/AddRent',[ProcessMaster::class,'process_rent']);
Route::get('/Org/Master/GetBankLedger',[ProcessMaster::class,'get_bank_ledger']);
Route::post('/Org/Master/AddBankAccount',[ProcessMaster::class,'process_bank_account']);
Route::get('/Org/Master/GetBankAccount/{org_id}',[ProcessMaster::class,'get_bank_account_list']);
Route::get('/Org/Master/GetChamberRack/{org_id}/{chamber_id}',[ProcessMaster::class,'get_chamber_rack']);
Route::get('/Org/Master/GetRackPocket/{org_id}/{rack_id}',[ProcessMaster::class,'get_rack_pocket']);
Route::post('/Org/Master/RenewalLicence',[ProcessMaster::class,'process_licence_renewal']);
Route::get('/Org/Master/GetRenewalList/{org_id}',[ProcessMaster::class,'get_renewal_list']);
Route::put('/Org/Master/UpdateRenewal',[ProcessMaster::class,'update_renewal']);
Route::post('/Org/Master/AddPosition',[ProcessMaster::class,'process_position']);
Route::get('/Org/Master/GetPosition/{org_id}',[ProcessMaster::class,'get_position']);
Route::put('/Org/Master/UpdatePosition',[ProcessMaster::class,'update_position']);

// Master Route End

// Processing Route Start

Route::post('/Org/Processing/GeneralBooking',[ProcessProcessing::class,'process_general_booking']);
Route::get('/Org/Processing/GetBookingData/{org_id}/{book_id}',[ProcessProcessing::class,'get_gen_booking']);
Route::get('/Org/Processing/GetCustomer',[ProcessProcessing::class,'get_customer']);
Route::get('/Org/Processing/GetBookingDetails/{org_id}/{book_no}',[ProcessProcessing::class,'get_booking_details']);
Route::get('/Org/Processing/SearchBooking',[ProcessProcessing::class,'search_booking_data']);
Route::post('/Org/Processing/BondEntry',[ProcessProcessing::class,'process_bond']);
Route::get('/Org/Processing/SearchBond/{org_id}/{type}/{keyword}',[ProcessProcessing::class,'search_bond']);
Route::get('/Org/Processing/GetBondDetails/{org_id}/{bond_id}',[ProcessProcessing::class,'get_bond_details']);
Route::get('/Org/Processing/SearchBookForRack',[ProcessProcessing::class,'search_rack_book']);
Route::get('/Org/Processing/GetBondBooking/{org_id}/{book_id}',[ProcessProcessing::class,'get_bond_list']);
Route::post('/Org/Processing/RackPosting',[ProcessProcessing::class,'process_rack_posting']);
Route::post('/Org/Processing/GetRentData',[ProcessProcessing::class,'get_rent_data']);
Route::post('/Org/Processing/CalculateRent',[ProcessProcessing::class,'calculate_rent']);
Route::post('/Org/Processing/CollectRent',[ProcessProcessing::class,'process_rent_collect']);
Route::get('/Org/Processing/GetReleaseOrder',[ProcessProcessing::class,'get_release_order']);
Route::get('/Org/Processing/GetReleaseCount',[ProcessProcessing::class,'get_release_count']);

// Processing Route End

// Voucher Entry Start

Route::get('/Org/Voucher/GetLedgerList',[ProcessVoucher::class,'get_ledger_list']);
Route::post('/Org/Voucher/PostVoucher',[ProcessVoucher::class,'process_voucher']);
Route::post('/Org/Voucher/GetBankBalance',[ProcessVoucher::class,'get_bank_balance']);
Route::post('/Org/Voucher/PostBankDeposit',[ProcessVoucher::class,'process_bank_deposit']);
Route::post('/Org/Voucher/PostBankWithdrw',[ProcessVoucher::class,'process_bank_withdrwan']);
Route::post('/Org/Voucher/PostBankTransfer',[ProcessVoucher::class,'process_bank_transfer']);

// End Voucher Entry

// Loan Route Start

Route::get('/Org/ProcessLoan/GetCustomerBond',[ProcessLoan::class,'get_customer_bond']);
Route::post('/Org/ProcessLoan/ProcessApplication',[ProcessLoan::class,'process_loan']);
Route::get('/Org/ProcessLoan/SearchLoan',[ProcessLoan::class,'search_loan_account']);
Route::get('/Org/ProcessLoan/GetLoanDetails',[ProcessLoan::class,'get_loan_details']);
Route::post('/Org/ProcessLoan/ProcessRepayment',[ProcessLoan::class,'process_loan_repay']);
Route::get('/Org/ProcessLoan/GetLedger',[ProcessLoan::class,'process_loan_ledger']);

// End Loan Route

// General Report Area

Route::post('/Org/GeneralReport/BookingRegister',[ProcessGeneralReport::class,'process_booking_register']);
Route::post('/Org/GeneralReport/BondRegister',[ProcessGeneralReport::class,'process_bond_register']);
Route::post('/Org/GeneralReport/CollectionRegister',[ProcessGeneralReport::class,'process_collection_register']);
Route::get('/Org/GeneralReport/CustomerEnquery',[ProcessGeneralReport::class,'process_customer_enquery']);

// End General Report Area
// Accounting Report
Route::get('/Org/ProcessFinancialReport/Daybook',[ProcessAccountingReport::class,'process_daybook']);
Route::get('/Og/ProcessFinancialReport/CashAccount',[ProcessAccountingReport::class,'process_cash_acct']);
Route::get('/Org/ProcessFinancialReport/CashBook',[ProcessAccountingReport::class,'process_cashbook']);
Route::get('/Org/ProcessFinancialReport/BankLedger',[ProcessAccountingReport::class,'process_bank_ledger']);
Route::get('/Org/ProcessFinancialReport/GetLedgerList',[ProcessAccountingReport::class,'get_ledger_list']);
Route::get('/Org/ProcessFinancialReport/GetLedger',[ProcessAccountingReport::class,'process_ledger']);
// End Accounting Report

// Rectify route start here
Route::post('/Org/ProcessRectify/CancelBooking',[ProcessRectify::class,'calcel_booking']);
Route::post('/Org/ProcessRectify/CancelBond',[ProcessRectify::class,'cancel_bond']);
// Rectify Route End Here

});





// End User Route Area