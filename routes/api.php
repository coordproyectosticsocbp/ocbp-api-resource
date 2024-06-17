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
    //'middleware' => ['auth.apikey', 'cors']
], function () {

    /* INTEL OPTIONS */
    Route::get('/hs/populations/age/{age?}/date/{init?}/{end?}', [
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
        '/esparta/patient/{patientdoc?}/type/{patientdoctype?}/information',
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
        '/hito/get/occupation-with-real-stay-two',
        [
            \App\Http\Controllers\HitoController::class,
            'getCensoNew'
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

    Route::get(
        '/hito/get/audit-get-patient-procedures/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getOrderedProceduresByPatientDoc'
        ]
    );

    Route::get(
        '/hito/get/audit-patient-admision-history/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getPatientAdmisionHistory'
        ]
    );

    Route::get(
        '/hito/get/active-doctors-to-audit',
        [
            \App\Http\Controllers\HitoController::class,
            'getDoctorsListForAudit'
        ]
    );

    Route::get(
        'hito/get/patient-info/turn-delivery/{bedCode?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getPatientInfoForTurnDelivery'
        ]
    );

    Route::get(
        'hito/get/pavilion-beds/turn-delivery/{pavName?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getBedsOfPavilionByPavName'
        ]
    );

    /* Route::get(
        '/hito/get/audit-get-patient-two/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\HitoController::class,
            'getOrderedProceduresByPatientDocTwo'
        ]
    ); */

    /** ------------------------------------------------------------------------------------------------------ */

    /** HITO AUDITORÍA */
    ///api/v1/hito-auditoria/get/occupation
    Route::get(
        '/hito-auditoria/get/occupation',
        [
            \App\Http\Controllers\HitoAuditoriaController::class,
            'getCenso'
        ]
    );

    Route::get(
        '/hito-auditoria/get/patient/{patientdoc?}/type/{patientdoctype?}/adm/{admnum?}',
        [
            \App\Http\Controllers\HitoAuditoriaController::class,
            'getPatientInfoDetail'
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

    Route::get(
        '/hygea/get/active-mixing-center-users',
        [
            \App\Http\Controllers\HygeaController::class,
            'getMixingCenterUsers'
        ]
    );

    Route::get(
        '/hygea/get/active-pharm-service-users',
        [
            \App\Http\Controllers\HygeaController::class,
            'getPharmServiceUsers'
        ]
    );

    Route::get(
        '/hygea/get/suggested-pending/{init?}/{end?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getSuggestedPending'
        ]
    );

    Route::get(
        '/hygea/get/purchase-invoices/{sumcod?}/{month?}/{year?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getInvoiceDetailsInPurchases'
        ]
    );

    Route::get(
        '/hygea/get/patient-basic-info/{document?}/{doctype?}',
        [
            \App\Http\Controllers\HygeaController::class,
            'getHygeaPatientInfo'
        ]
    );

    Route::get(
        '/hygea/get/all-repacking-drugs',
        [
            \App\Http\Controllers\HygeaController::class,
            'getRepackingProducts'
        ]
    );

    Route::get(
        '/hygea/get/rotacion_fcia',
        [
            \App\Http\Controllers\HygeaController::class,
            'DB_ROTACION_FCIA'
        ]
    );

    Route::get(
        '/hygea/get/ordenes_compra',
        [
            \App\Http\Controllers\HygeaController::class,
            'DB_ORDENES_DE_COMPRA_V2'
        ]
    );
    Route::get(
        '/hygea/get/rotacion_diaria',
        [
            \App\Http\Controllers\HygeaController::class,
            'DB_ROTACION_DIARIA_FCIA'
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

    Route::get(
        '/nomina/get/immediate-bosses-by-document/{document?}',
        [
            \App\Http\Controllers\NominaController::class,
            'getAllImmediateBossByDocument'
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

    /* CIRUGÍA */
    Route::get(
        '/cirugia/get/patient-procedures/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getLastPendingProcedure'
        ]
    );

    Route::get(
        '/cirugia/get/all-procedures/{procedurecode?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getAllQxProcedures'
        ]
    );

    Route::get(
        '/cirugia/get/scheduled-procedures-by-date/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getScheduledProceduresByDate'
        ]
    );

    Route::get(
        '/cirugia/get/scheduled-procedures-by-document/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getScheduledProceduresByDocument'
        ]
    );

    Route::get(
        '/cirugia/get/scheduled-procedures-with-tags/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getScheduledProceduresByDateWithTags'
        ]
    );

    Route::get(
        '/cirugia/get/surgery-details/{sugerycode?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getSurgeryDetailBySurgeryCode'
        ]
    );

    Route::get(
        '/cirugia/get/top-completed-surgeries/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getTopCompletedSurgeries'
        ]
    );

    Route::get(
        '/cirugia/get/top-canceled-surgeries/{initdate?}/{enddate?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getTopCanceledSurgeries'
        ]
    );

    Route::get(
        '/cirugia/get/patient-info-by-document/{patientdoc?}/{patientdoctype?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getPatientInfoByDocument'
        ]
    );

    Route::get(
        '/cirugia/get/proc-info-by-code/{procCode?}',
        [
            \App\Http\Controllers\CirugiaController::class,
            'getProcedureInfoByCode'
        ]
    );


    /*-------------------------------------------------------------------------------------------------------------*/
    /* CALADRIUS */
    Route::get(
        '/caladrius/get/contracts-general-info/{contract?}',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getContractsWithPortfoliosAndServices'
        ]
    );

    Route::get(
        '/caladrius/get/amb-ordered-procedures/{document?}/{doctype?}/{folio?}',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getOrderedProcedures'
        ]
    );

    /** Ambulatorio */
    Route::get(
        '/caladrius/get/patient-basic-info/{document?}/{doctype?}',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getPatientInfo'
        ]
    );

    Route::get(
        '/caladrius/get/folios-info-by-document/{document?}/{doctype?}/folio/{folio?}',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getPatientFoliosInfo'
        ]
    );


    /** Hospitalización */
    Route::get(
        '/caladrius/get/patient-basic-info-hosp/{document?}/{doctype?}',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getPatientInfoHosp'
        ]
    );

    Route::get(
        '/caladrius/get/folios-info-by-document-hosp/{document?}/{doctype?}/folio/{folio?}',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getPatientFoliosInfoHosp'
        ]
    );



    /** =========================================== */

    Route::get(
        '/caladrius/get/active-contracts',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getAllActiveContracts'
        ]
    );

    Route::get(
        '/caladrius/get/active-diagnoses',
        [
            \App\Http\Controllers\CaladriusController::class,
            'getAllActiveDiagnoses'
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
    /* Route::get(
        '/coco/patient/{patientdoc}/type/{patientdoctype}/information',
        [
            \App\Http\Controllers\CocoController::class,
            'initialPatientInfo'
        ]
    ); */

    /*-------------------------------------------------------------------------------------------------------------*/

    /* ENTREGA DE TURNOS */

    Route::get(
        '/turnos/get/turns-by-date/{pavilion?}/{date?}',
        [
            \App\Http\Controllers\TurnDeliveryController::class,
            'getTurnsByDate'
        ]
    );
    /* ------------------------------------------------------------------------------- */
    /* INDICADORES PQRSF */

    Route::get(
        '/indicadores/get/felicitacionesvsquejas/{fechaInicial?}/{fechaFinal?}',
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getFelicitacionesVsQuejas'
        ]
    );
    Route::get(
        '/indicadores/get/porcentaje/{fechaInicial?}/{fechaFinal?}/{idType?}',
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getPorcentajePQR'
        ]
    );
    Route::get(
        '/indicadores/get/tiempopromedio/{fechaInicial?}/{fechaFinal?}/{idType?}',
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getTiempoPromedio'
        ]
    );
    Route::get(
        ('/indicadores/get/oportunidadpqr/{fechaInicial?}/{fechaFinal?}/{idType?}'),
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getOportunidadPQR'
        ]
    );
    Route::get(
        ('/indicadores/get/felicitacionesvsquejasporarea/{fechaInicial?}/{fechaFinal?}'),
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getFelicitacionesVsQuejasPorArea'
        ]
    );

    Route::get(
        ('/indicadores/get/halconreportegeneralcasos/{fechaInicial?}/{fechaFinal?}'),
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getHalconReporteGeneralCasos'
        ]
    );


    /* INDICADORES SEGURIDAD DEL PACIENTE */
    Route::get(
        ('/indicadores/get/SeguridadPaciente/TrazabilidadEgresos/{fechaInicial?}/{fechaActual?}'),
        [
            \App\Http\Controllers\TorreControl\SeguridadDelPacienteController::class,
            'getTrazabilidadEgresos'
        ]
    );

    /** INDICADORES ESTANCIA */
    Route::get(
        ('/indicadores/get/censo-estancias'),
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getCensoForControlTower'
        ]
    );

    /* RONDAS */

    Route::get(
        ('/rondas/get/informacionempleado/{cedula?}'),
        [
            \App\Http\Controllers\RondasController::class,
            'getInformacionEmpleados'
        ]
    );
    Route::get(
        ('/rondas/get/estanciapaciente/{cedula?}'),
        [
            \App\Http\Controllers\RondasController::class,
            'getLugarEstanciaActual'
        ]
    );
    Route::get(
        ('/rondas/get/datosbasicospaciente/{cedula}/{tipo_doc}'),
        [
            \App\Http\Controllers\RondasController::class,
            'getDatosBasicosPaciente'
        ]
    );
    Route::get(
        ('/rondas/get/censoreal'),
        [
            \App\Http\Controllers\RondasController::class,
            'getCensoReal'
        ]
    );
    Route::get(
        ('/rondas/get/medicosyespecialistas'),
        [
            \App\Http\Controllers\RondasController::class,
            'getMedicosEspecialistas'
        ]
    );
    Route::get(
        ('/rondas/get/dataqsystem'),
        [
            \App\Http\Controllers\RondasController::class,
            'dataqsystem'
        ]
    );
    Route::get(
        ('/rondas/get/tiposolicitud/{servicio_id}'),
        [
            \App\Http\Controllers\RondasController::class,
            'getTipoSolicitud'
        ]
    );

    /* ALICANTO */

    Route::get(
        ('/alicanto/get/auditorias'),
        [
            \App\Http\Controllers\Alicanto\ReportesController::class,
            'getAuditoria'
        ]
    );
    Route::get(
        ('/alicanto/get/comite'),
        [
            \App\Http\Controllers\Alicanto\ReportesController::class,
            'getComites'
        ]
    );
    Route::get(
        ('/alicanto/get/asistenciacomite'),
        [
            \App\Http\Controllers\Alicanto\ReportesController::class,
            'getAsitenciaComite'
        ]
    );
    /* CHATBOT */
    Route::get(
        ('/chatbot/get/citashoy'),
        [
            \App\Http\Controllers\RondasController::class,
            'getChatbotCitas'
        ]
    );

    /* ESTANCIAS */
    Route::get(
        ('/estancias/get/censo'),
        [
            \App\Http\Controllers\TorreControl\EstanciasController::class,
            'getCenso'
        ]
    );

    /**ORDENES DE COMPRA FARMACIA */

    Route::get(
        ('/OrderFarmacia/get/{num_order}/{token}'),
        [
            \App\Http\Controllers\RondasController::class,
            'obtenerOrdenes'
        ]
    );
});






//INDICADORES TORRE DE CONTROL

Route::group([
    'prefix' => 'v2',
    'middleware' => 'auth.apikey'
], function () {

    /* URGENCIAS */
    Route::get(
        ('/indicadores/get/triagecount'),
        [
            \App\Http\Controllers\TorreControl\Urgencias::class,
            'getTriageCount'
        ]
    );

    Route::get(
        ('/indicadores/get/reentryurgency'),
        [
            \App\Http\Controllers\TorreControl\Urgencias::class,
            'getReEntryUrgency'
        ]
    );
    /*INDICADORES - CIRUGIAS */

    Route::get(
        ('/indicadores/get/cirugias'),
        [
            \App\Http\Controllers\TorreControl\Cirugias::class,
            'getCirugias'
        ]
    );

    Route::get(
        ('/indicadores/get/cirugyPatientMortality'),
        [
            \App\Http\Controllers\TorreControl\Cirugias::class,
            'patientMortality'
        ]
    );

    Route::get(
        ('/indicadores/get/cirugyEficiency'),
        [
            \App\Http\Controllers\TorreControl\Cirugias::class,
            'getsurgeryEfficiency'
        ]
    );
    Route::get(
        ('/indicadores/get/cirugyOportunity'),
        [
            \App\Http\Controllers\TorreControl\Cirugias::class,
            'getOportunity'
        ]
    );
    /* ------------------------------------------------------------------------------- */
    /* INDICADORES PQRSF */
    Route::get(
        ('/indicadores/get/prioridadcasos/{fechaInicial?}/{fechaFinal?}'),
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getPrioridadCasos'
        ]
    );
    Route::get(
        ('/indicadores/get/prioridadcasosyear/{fechaInicial?}/{fechaFinal?}'),
        [
            \App\Http\Controllers\TorreControl\PQRSFController::class,
            'getPrioridadCasosYear'
        ]
    );
    //
    /* ------------------------------------------------------------------------------- */
});
