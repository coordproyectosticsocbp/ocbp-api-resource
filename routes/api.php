<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'v1',
    'middleware' => 'auth.apikey'
], function () {

    Route::get('/hs/populations/age/{age}',
        [
            \App\Http\Controllers\IntelOptionController::class,
            'getDataUsersPopulation'
        ]);

    Route::get('/hs/patients-c90',
        [
            \App\Http\Controllers\LyaElectronicController::class,
            'getPatientsData'
        ]);

    Route::get('/hs/billing/init-date/{initdate}/end-date/{enddate}',
        [
            \App\Http\Controllers\ThOcbpController::class,
            'getPendingForBilling'
        ]);

});
