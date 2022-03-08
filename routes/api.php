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

    Route::get(
        '/hito/get/active-nurses',
        [
            \App\Http\Controllers\HitoController::class,
            'getNursesList'
        ]
    );

    Route::get(
        '/hito/get/active-doctors',
        [
            \App\Http\Controllers\HitoController::class,
            'getDoctorsList'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* AGOTADOS */
    Route::get(
        '/agotados/get/drugs-by-code/{sumcod}',
        [
            \App\Http\Controllers\AgotadosController::class,
            'drugsByCode'
        ]
    );

    Route::get(
        '/agotados/get/purchase-order/not-greater-than-21',
        [
            \App\Http\Controllers\AgotadosController::class,
            'getPurchaseOrders'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* ETHEREUM */
    Route::get(
        '/ethereum/get/specialties',
        [
            \App\Http\Controllers\EthereumController::class,
            'getSpecialties'
        ]
    );

    Route::get(
        '/ethereum/get/doctors-with-spe-regm',
        [
            \App\Http\Controllers\EthereumController::class,
            'getDoctorsWithSpecialty'
        ]
    );

    Route::get(
        '/ethereum/get/diagnostics',
        [
            \App\Http\Controllers\EthereumController::class,
            'getDiagnostics'
        ]
    );

    Route::get(
        '/ethereum/get/patient/{patientdoc}/type/{patientdoctype}/information',
        [
            \App\Http\Controllers\EthereumController::class,
            'initialPatientInfo'
        ]
    );


    /*-------------------------------------------------------------------------------------------------------------*/

    /* HYGEA */
    Route::get(
        '/hygea/get/warehouses',
        [
            \App\Http\Controllers\HygeaController::class,
            'getWarehouses'
        ]
    );

    Route::get(
        '/hygea/get/providers',
        [
            \App\Http\Controllers\HygeaController::class,
            'getProviders'
        ]
    );

    Route::get(
        '/hygea/get/total-providers',
        [
            \App\Http\Controllers\HygeaController::class,
            'getAllProviders'
        ]
    );

    Route::get(
        '/hygea/get/purchase-orders/{init?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getPurchaseOrders'
        ]
    );

    Route::get(
        '/hygea/get/drugs-inventory',
        [
            \App\Http\Controllers\HygeaController::class,
            'drugsInventory'
        ]
    );

    Route::get(
        '/hygea/get/all-drugs',
        [
            \App\Http\Controllers\HygeaController::class,
            'allDrugs'
        ]
    );

    Route::get(
        '/hygea/get/purchase-orders/{init?}/{end?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getPurchaseOrdersByDateRange'
        ]
    );

    Route::get(
        '/hygea/get/medical-orders/{orderdate?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getMedicalOrders'
        ]
    );

    Route::get(
        '/hygea/get/mov-transac/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getMovTransacWarehousesWithDateFilter'
        ]
    );

    Route::get(
        '/hygea/get/drug-rotation/{sumcod?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getProductRotationPurchasesOutputBySumCod'
        ]
    );

    Route::get(
        '/hygea/get/lot/{sumcod?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getLoteBySumCod'
        ]
    );

    Route::get(
        '/hygea/get/last-patient-evo/{docpac?}/{doctype?}/folio/{folio?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getPatientLastEvolution'
        ]
    );

    Route::get(
        '/hygea/get/billed-drugs-by-code/{sumcod?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getBilledDrugsByCode'
        ]
    );

    Route::get(
        '/hygea/get/super-ac-dispatch/{sumcod?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getSumDespachosSuperAC'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* MACNA */
    Route::get(
        '/macna/patient/{patientdoc}/type/{patientdoctype}/information',
        [
            \App\Http\Controllers\MacnaController::class,
            'getPatientInfoByDocument'
        ]
    );

    Route::get(
        '/macna/get/patient/alto-cost-format/{patientdocument?}/type/{patientdoctype?}/folio/{patientfolio?}',
        [
            \App\Http\Controllers\MacnaController::class,
            'getAltCostoFormat'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* EVALUACION Y DESEMPEÑO */
    Route::get(
        '/eva-des/get/employees-database/status/{status?}',
        [
            \App\Http\Controllers\EvaluacionDesempenoController::class,
            'getEmployeesDatabase'
        ]
    );

    Route::get(
        '/eva-des/get/novelties-concepts/from/{init?}/{end?}',
        [
            \App\Http\Controllers\EvaluacionDesempenoController::class,
            'getNoveltiesConcepts'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* NÓMINA */
    Route::get(
        '/nomina/get/employees-database',
        [
            \App\Http\Controllers\NominaController::class,
            'getEmployeesDatabaseWithSalary'
        ]
    );

    Route::get(
        '/nomina/get/employees-biometric-marks/{document?}/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\NominaController::class,
            'getBiometricMarks'
        ]
    );

    Route::get(
        '/nomina/get/employees-biometric-marks-by-date-range/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\NominaController::class,
            'getBiometricMarksByDateRange'
        ]
    );

    Route::get(
        '/nomina/get/immediate-bosses',
        [
            \App\Http\Controllers\NominaController::class,
            'getAllImmediateBoss'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* EXITUS */

    Route::get(
        '/financial-exitus/get/bills-by-date/{startdate?}/end/{endddate?}',
        [
            \App\Http\Controllers\ExitusController::class,
            'getBillByDateRange'
        ]
    );

    Route::get(
        '/exitus/get/occupation-with-real-stay',
        [
            \App\Http\Controllers\ExitusController::class,
            'getCenso'
        ]
    );

    Route::get(
        '/exitus/get/patient-info-by-hab-code/{hab?}',
        [
            \App\Http\Controllers\ExitusController::class,
            'getPatientInfoByHabCode'
        ]
    );

    Route::get(
        '/exitus/get/patient-adm-by-document/{document?}/document-type/{doctype?}',
        [
            \App\Http\Controllers\ExitusController::class,
            'getAdmPatientInfoByDocumentToPrintHandle'
        ]
    );



    /*-------------------------------------------------------------------------------------------------------------*/

    /* AUXILIARES CLÍNICOS */

    Route::get(
        '/clinical-assistants/get/clinical-assistants',
        [
            \App\Http\Controllers\AuxClinicosController::class,
            'getClinicalAssistants'
        ]
    );

    Route::get(
        '/clinical-assistants/get/patient-info-by-hab-code/{hab?}',
        [
            \App\Http\Controllers\AuxClinicosController::class,
            'getPatientInfoByHabCode'
        ]
    );

    Route::get(
        '/clinical-assistants/patient/{patientdoc?}/type/{patientdoctype?}',
        [
            \App\Http\Controllers\AuxClinicosController::class,
            'initialPatientInfo'
        ]
    );

    Route::get(
        '/clinical-assistants/get-services',
        [
            \App\Http\Controllers\AuxClinicosController::class,
            'getPavilionsToMakeServices'
        ]
    );

    /*-------------------------------------------------------------------------------------------------------------*/

    /* DOCTOR CLINIC */

    Route::get(
        '/doctor-clinic/patient/{patientdoc?}/type/{patientdoctype?}/information',
        [
            \App\Http\Controllers\DoctorClinicController::class,
            'initialPatientInfo'
        ]
    );



    /*-------------------------------------------------------------------------------------------------------------*/

    /* COCO */
    Route::get(
        '/coco/patient/{patientdoc}/type/{patientdoctype}/information',
        [
            \App\Http\Controllers\CocoController::class,
            'initialPatientInfo'
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
