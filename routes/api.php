<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin Controller Start
use App\Http\Controllers\Admin\AdminLogin;
use App\Http\Controllers\Admin\ProcessOrgination;

// Admin Controller End

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

    Route::get('/Admin/GetDashboard',[AdminLogin::class,'get_admin_dashboard']);
    Route::get('/Admin/ProcessLogOut',[AdminLogin::class,'process_log_out']);
    Route::post('/Admin/AddUser',[AdminLogin::class,'process_admin_user']);
    Route::get('/Admin/GetUserList',[AdminLogin::class,'get_user_list']);
    Route::get('/Admin/GetModuleList',[AdminLogin::class,'get_module_list']);
    Route::post('/Admin/MapUserModule',[AdminLogin::class,'process_map_user_module']);

    // Login & Dashboard Route

    // Process Orgination Route Start Here



    // Orgination Route End Here
});

// login route end

//admin route end area