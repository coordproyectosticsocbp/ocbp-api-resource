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

Route::post('login', [\App\Http\Controllers\LoginController::class, 'Login']);
Route::post('logout', [\App\Http\Controllers\LoginController::class, 'Logout']);

Route::group([
    'prefix' => 'v1',
    'middleware' => 'auth.apikey'
], function () {

    /* INTEL OPTIONS */
    Route::get('/hs/populations/age/{age}/date/{init?}/{end?}', [
        \App\Http\Controllers\IntelOptionController::class,
        'getDataUsersPopulation'
    ]);

    Route::get(
        '/hs/patients-c90',
        [
            \App\Http\Controllers\LyaElectronicController::class,
            'getPatientsData'
        ]
    );

    Route::get(
        '/hs/billing/init-date/{initdate}/end-date/{enddate}',
        [
            \App\Http\Controllers\ThOcbpController::class,
            'getPendingForBilling'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* ESPARTA */
    Route::get(
        '/esparta/patient/{patientdoc}/type/{patientdoctype}/information',
        [
            \App\Http\Controllers\EspartaController::class,
            'initialPatientInfo'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* HITO */
    Route::get(
        '/hito/get/occupation-with-real-stay',
        [
            \App\Http\Controllers\HitoController::class,
            'getCenso'
        ]
    );

    Route::get(
        '/hito/get/patient-info-by-hab-code/{hab?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getPatientInfoByHabCode'
        ]
    );

    Route::get(
        '/hito/get/patient/{patientdoc?}/type/{patientdoctype?}/information',
        [
            \App\Http\Controllers\HitoController::class,
            'initialPatientInfo'
        ]
    );

    Route::get(
        '/hito/get/patient-adm-output-info/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getAdmOutDateByDocument'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* AGOTADOS */
    Route::get('/agotados/get/drugs-by-code/{sumcod}',
        [
            \App\Http\Controllers\AgotadosController::class,
            'drugsByCode'
        ]
    );

    Route::get('/agotados/get/purchase-order/not-greater-than-21',
        [
            \App\Http\Controllers\AgotadosController::class,
            'getPurchaseOrders'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* ETHEREUM */
    Route::get('/ethereum/get/specialties',
        [
            \App\Http\Controllers\EthereumController::class,
            'getSpecialties'
        ]
    );

    Route::get('/ethereum/get/doctors-with-spe-regm',
        [
            \App\Http\Controllers\EthereumController::class,
            'getDoctorsWithSpecialty'
        ]
    );

    Route::get('/ethereum/get/diagnostics',
        [
            \App\Http\Controllers\EthereumController::class,
            'getDiagnostics'
        ]
    );


    /*-------------------------------------------------------------------------------------------------------------*/
    /* HYGEA */
    Route::get('/hygea/get/warehouses',
        [
            \App\Http\Controllers\HygeaController::class,
            'getWarehouses'
        ]
    );

    Route::get('/hygea/get/providers',
        [
            \App\Http\Controllers\HygeaController::class,
            'getProviders'
        ]
    );


});


/*Route::group([
    'prefix' => 'v2'
], function () {

    Route::get('/hub/hs/pavilions',
        [
            \App\Http\Controllers\HitoController::class,
            'getServicios'
        ]
    );

});*/
