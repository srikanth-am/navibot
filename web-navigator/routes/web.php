<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->to('/login');
});
// Auth::routes();
Auth::routes(['verify' => true]);
Route::post('/login', 'AuthController@Login')->name('login');
Route::get('/signup', 'AuthController@Get_Signup')->name('signup');
Route::get('/email-verification/{id}/{token}', 'AuthController@Get_ConfirmationView')->name('email-verification');
Route::post('/email-verification', 'AuthController@VerifyEmail')->name('email-verification');
Route::post('/signup', 'AuthController@Signup')->name('signup');
Route::post('/forgot-password', 'AuthController@ForgotPassword')->name('forgot-password');
Route::post('/reset-password', 'AuthController@ResetPassword')->name('reset-password');
// 
Route::get('/dashboard', 'DashboardController@index')->name('dashboard')->middleware('Production');
Route::get('/time-now', 'DashboardController@get_time_now')->name('time-now');
Route::get('/add-domain', 'ExtractUrlsController@index')->name('url-extract')->middleware('Production');
Route::post('/web-navigator', 'ExtractUrlsController@get_urls')->name('url-extract')->middleware('Production');
Route::post('/export-report/', 'ExtractUrlsController@ExportReport')->name('export-report')->middleware('Production');
Route::post('/start-bg-process', 'ExtractUrlsController@start_bg_process')->name('start-bg-process')->middleware('Production');
//Admin Only Routes
Route::get('/settings', function () {return view('settings');})->name('settings')->middleware('AdminOnly');
Route::get('/manage-websites/{id}', 'ManageWebsitesController@DeleteDomainData')->name('manage-websites')->middleware('AdminOnly');
Route::get('/settings/users', 'Admin\UserController@index')->name('users')->middleware('AdminOnly');
Route::get('/settings/users/add', 'Admin\UserController@RegisterUser')->name('add-user')->middleware('AdminOnly');
Route::get('/settings/users/edit/{id}', 'Admin\UserController@RegisterUser')->name('edit-user')->middleware('AdminOnly');//return view
Route::get('/settings/users/user-data/{id}', 'Admin\UserController@GetEditUser')->name('user-data')->middleware('AdminOnly');//return edit data
Route::post('/settings/user/save', 'Admin\UserController@SaveUser')->name('save-user')->middleware('AdminOnly');//Submit edit data
Route::post('/settings/user/delete', 'Admin\UserController@DeleteUser')->name('delete-user')->middleware('AdminOnly');

//
Route::get('/settings/user-roles', 'Admin\UserController@GetUserRoleView')->name('user-roles')->middleware('AdminOnly');//list
Route::get('/settings/user-roles/add', 'Admin\UserController@GetUserRoleMange')->name('user-role-add')->middleware('AdminOnly');//return form view
Route::get('/settings/user-roles/edit/{id}', 'Admin\UserController@GetUserRoleMange')->name('user-role-edit')->middleware('AdminOnly');//return form view
Route::get('/settings/user-roles/{id}', 'Admin\UserController@GetUserRole')->name('user-role-get')->middleware('AdminOnly');//return edit data
Route::post('/settings/user-roles/save', 'Admin\UserController@SaveRoles')->name('save-roles')->middleware('AdminOnly');//Submit edit data
Route::post('/settings/user-roles/change_status', 'Admin\UserController@ChangeStatus')->name('change-role-status')->middleware('AdminOnly');//Submit edit data
Route::get('/settings/user-roles/delete/{id}', 'Admin\UserController@DeleteUserRole')->name('delete-user-role')->middleware('AdminOnly');
//
Route::get('/settings/sales-domains', 'Admin\SalesDomainsController@index')->name('sales-domains')->middleware('AdminOnly');//list
Route::get('/settings/sales-domains/delete/{id}', 'Admin\SalesDomainsController@DeleteSalesDomain')->name('sales-domains-delete')->middleware('AdminOnly');//list

//
Route::group(['prefix' => 'sales'], function(){
    Route::get('/', function () { return redirect()->to('/sales/dashboard'); });
    Route::get('/dashboard', 'SalesDashboardController@index')->name('dashboard')->middleware('Sales');
    Route::get('/add-domain', 'SalesExtractUrlsController@index')->name('sales-add-domain')->middleware('Sales');
    Route::get('/time-now', 'SalesDashboardController@get_time_now')->name('time-now')->middleware('Sales');
    Route::post('/add-domain', 'SalesExtractUrlsController@get_urls')->name('url-extract')->middleware('Sales');
    Route::post('/export-report/', 'SalesDashboardController@ExportReport')->name('export-sales-report')->middleware('Sales');
    Route::get('/sales-domain-delete/{id}', 'SalesDashboardController@DeleteDomain')->name('sales-domain-delete')->middleware('Sales');
});
Route::group(['middleware' => 'role:Admin'], function () {
   
});