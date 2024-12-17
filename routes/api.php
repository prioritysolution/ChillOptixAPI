<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin Controller Start
use App\Http\Controllers\Admin\AdminLogin;
use App\Http\Controllers\Admin\ProcessOrgination;

// Admin Controller End

// User Controller Start
use App\Http\Controllers\Organisation\UserLogin;

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
    Route::post('/Admin/ProcessOrg/AddFinYear',[ProcessOrgination::class,'process_org_finyear']);
    Route::get('/Admin/ProcessOrg/CheckRental/{org_id}',[ProcessOrgination::class,'check_org_rental']);
    Route::post('/Admin/ProcessOrg/AddRental',[ProcessOrgination::class,'process_rental']);
    Route::get('/Admin/ProcessOrg/GetUserRole',[ProcessOrgination::class,'get_org_user_role']);
    Route::post('/Admin/ProcessOrg/AddUser',[ProcessOrgination::class,'process_org_user']);


    // Orgination Route End Here
});

// login route end

//admin route end area

// User Route Area

// Login Route

Route::post('/Org/ProcessLogin',[UserLogin::class,'process_user_login'])->middleware('api_access');


Route::group([
    'middleware' => ['auth:sanctum',]
],function(){

// Dashboard Route Start
Route::get('/Org/GetSideBar',[UserLogin::class,'get_user_sidebar']);

// Dashboard End


});





// End User Route Area