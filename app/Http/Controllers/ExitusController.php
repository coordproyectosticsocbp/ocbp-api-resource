<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExitusController extends Controller
{
    //
    public function __construct()
    {
        return $this->middleware('auth.apikey');
    }



    /**
     * @OA\Get (
     *     path="/api/v1/financial-exitus/get/bills-by-date/{startdate?}/end/{enddate?}",
     *     operationId="get Bills Info",
     *     tags={"Exitus"},
     *     summary="Get Bills Info",
     *     description="Returns Bills Info",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="startdate?",
     *          description="Initial Date For Search - Optional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="enddate?",
     *          description="End Date For Search - Optional",
     *          required=false,
     *          in="path",
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getBillByDateRange(Request $request, $startDate, $endDate)
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if (!$startDate) {

                    $init = Carbon::now()->format('Y-m-d');
                } else {
                    $init = $startDate;
                }

                if (!$endDate) {

                    $end = Carbon::now()->format('Y-m-d');
                } else {
                    $end = $endDate;
                }


                $query_bills = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM DBO.EXITUS_FACTURAS_ESTADOS() WHERE FECHA_FACTURA BETWEEN '$init' AND '$end'");

                if (count($query_bills) > 0) {

                    $bills = [];

                    foreach ($query_bills as $bill) {
                        $tempBills = array(
                            'checkNumber' => trim($bill->DOCUMENTO . '-' . $bill->TIPO_DOC . '-' . $bill->ING),
                            'invoice' => trim($bill->FACTURA),
                            'invoiceType' => trim($bill->TIPO_FACTURA),
                            'invoiceClass' => trim($bill->CLASE_FACTURA),
                            'invoicePatientDocument' => trim($bill->DOCUMENTO),
                            'invoicePatientDocType' => trim($bill->TIPO_DOC),
                            'invoiceAdmConsecutive' => trim($bill->ING),
                            'invoicePatientName' => trim($bill->PACIENTE),
                            'invoiceAdmDate' => Carbon::parse($bill->FECHA_INGRESO)->format('Y-m-d'),
                            'invoiceOutDate' => Carbon::parse($bill->FECHA_EGRESO)->format('Y-m-d'),
                            'invoiceProviderNit' => trim($bill->NIT_EMPRESA),
                            'invoiceProviderName' => trim($bill->NOM_EMPRESA),
                            'invoiceContract' => trim($bill->CONTRATO),
                            'invoiceDate' => carbon::parse($bill->FECHA_FACTURA)->format('Y-m-d'),
                            'invoiceStatus' => trim($bill->ESTADO_FACTURA),
                            'invoiceValue' => $bill->VALOR_FACTURA,
                        );

                        $bills[] = $tempBills;
                    }

                    if (count($bills) > 0) {
                        return response()
                            ->json([
                                'status' => 200,
                                'message' => 'Success',
                                'counter' => count($bills),
                                'data' => $bills,
                            ]);
                    } else {
                        return response()
                            ->json([
                                'status' => 204,
                                'message' => 'No bills found',
                            ]);
                    }
                } else {
                    return response()
                        ->json([
                            'status' => 204,
                            'message' => 'Empty Bills Query Repsonse',
                        ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized',
                ]);
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/exitus/get/occupation-with-real-stay",
     *     operationId="occupation",
     *     tags={"Exitus"},
     *     summary="Get occupation Info With Real Stay",
     *     description="Returns occupation Name",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getCenso(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {
                    // CONSULTA PATA OBTENER LAS TORRES QUE ESTEN ACTIVAS EN LA ORGANIZACIÓN
                    $query_torres = DB::connection('sqlsrv')
                        ->select("SELECT * FROM TORRES WHERE towerState = 1");

                    if (count($query_torres) > 0) {
                        $torres = [];

                        foreach ($query_torres as $tower) {

                            // CONSULTA PARA OBTENER LA RELACIÓN DE TORRE PABELLÓN TENIENDO COMO PARAMETRO EL CÓDIGO DE LA TORRE
                            $query_torres_pavs = DB::connection('sqlsrv')
                                ->select("SELECT * FROM HITO_TOWER_PAVILIONS('$tower->towerCode') ORDER BY pavFloor DESC");

                            if (count($query_torres_pavs) > 0) {

                                $torres_pav = [];

                                foreach ($query_torres_pavs as $tower_pav) {

                                    // CONSULTA PARA OBTENER LOS PABELLONES TENIIENDO COMO PARAMETRO EL CÓDIGO DEL PABELLÓN
                                    $query = DB::connection('sqlsrv_hosvital')
                                        ->select("SELECT * FROM HITO_PABELLONES('$tower_pav->pavCode')");

                                    if (count($query) > 0) {

                                        $records = [];

                                        foreach ($query as $item) {

                                            // CONSULTA PARA TRAER PACIENTES DE LAS HABITACIONES ENVIANDO COMO PARAMETRO EL CÓDIGO DEL PABELLÓN
                                            $query2 = DB::connection('sqlsrv_hosvital')
                                                ->select("SELECT * FROM EXITUS_CENSO_REAL('$item->CODIGO_PABELLON') ORDER BY CAMA ASC");

                                            if (count($query2) > 0) {

                                                $habs = [];

                                                foreach ($query2 as $cat) {

                                                    if ($cat->PREALTA != null) {
                                                        $cat->PREALTA = 1;
                                                    } else {
                                                        $cat->PREALTA = 0;
                                                    }

                                                    $temp1 = array(
                                                        'pavCode' => $cat->COD_PAB,
                                                        'pavName' => $cat->PABELLON,
                                                        'habitation' => $cat->CAMA,
                                                        'hab_status' => $cat->ESTADO,
                                                        'patient_doc' => $cat->NUM_HISTORIA,
                                                        'patient_doctype' => $cat->TI_DOC,
                                                        'patient_name' => $cat->NOMBRE_PACIENTE,
                                                        'patient_eps_nit' => $cat->EPS_NIT,
                                                        'patient_eps' => $cat->EPS,
                                                        'patient_eps_email' => $cat->EPS_EMAIL,
                                                        'contract' => $cat->CONTRATO,
                                                        'attention_type' => $cat->TIPO,
                                                        'admission_date' => $cat->FECHA_INGRESO,
                                                        'admission_num' => $cat->INGRESO,
                                                        'age' => $cat->EDAD,
                                                        'gender' => $cat->SEXO,
                                                        'real_stay' => $cat->EstanciaReal,
                                                        'diagnosis' => $cat->DX,
                                                        'prealta' => $cat->PREALTA,
                                                        'specialistMedicaltDisDate' => $cat->HORA_ALTA_ESPECIALISTA,
                                                        'specialistMedicaltDisUser' => $cat->MEDICO_ALTA_ESPECIALISTA,
                                                        'epicrisisType' => $cat->TIPO_EPICRISIS,
                                                        'parcialEpicrisisDate' => $cat->FECHA_EPICRISIS_EGRESO_PARCIAL,
                                                        'exitEpicrisisDate' => $cat->FECHA_EPICRISIS_EGRESO,
                                                        'epicrisisDoctor' => $cat->USUARIO_EPICRISIS,
                                                    );

                                                    $habs[] = $temp1;
                                                }

                                                $temp2 = array(
                                                    //'towerCode' => $tower_pav->towerCode,
                                                    'pavCode' => $item->CODIGO_PABELLON,
                                                    'pavName' => $item->NOMBRE_PABELLON,
                                                    'pavFloor' => $tower_pav->pavFloor,
                                                    'habs' => $habs
                                                );

                                                // ARRAY QUE ALMACENA LA INFORMACIÓN DE CADA CAMA POR PABELLÓN
                                                $records[] = $temp2;
                                            } else {
                                                return response()
                                                    ->json([
                                                        'msg' => 'El query de pabellones no ha devuelto niguna respuesta',
                                                        'data' => [],
                                                        'status' => 400
                                                    ]);
                                            }
                                        }
                                    }
                                    // ARRAY QUE ALMACENA PARA CADA PABELLÓN LA INFORMACIÓN DE LA CAMAS
                                    $torres_pav[] = $records;
                                }
                            }

                            $temp5 = array(
                                'towerCode' => $tower->towerCode,
                                'towerDescription' => $tower->towerDescription,
                                'pavilions' => $torres_pav
                            );

                            // ARRAY QUE ALMACENA LA INFORMACIÓN DE LAS TORRES CON LA INFORMACIÓN DE LOS PABELLONES Y CAMAS
                            $torres[] = $temp5;
                        }

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'data' => $torres,
                                'status' => 200
                            ]);
                    } else {

                        return response()
                            ->json([
                                'msg' => 'empty response in towers request',
                                'data' => [],
                                'status' => 400
                            ]);
                    }
                } catch (\Throwable $e) {
                    throw $e;
                }
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/exitus/get/patient-info-by-hab-code/{hab?}",
     *     operationId="getPatientInfoByHabCode",
     *     tags={"Exitus"},
     *     summary="Get Patient Info by Hab Code",
     *     description="Returns Patient Info by Hab Code",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="hab?",
     *          description="Habitation Code - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getPatientInfoByHabCode(Request $request, $habCode = 'ab0100')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    if ($habCode) {

                        $query = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM EXITUS_PACIENTE_X_HABITACION('$habCode')");

                        if (count($query) > 0) {

                            $records = [];

                            foreach ($query as $item) {

                                $temp = array(
                                    'patName1' => trim($item->NOMBRE1),
                                    'patName2' => trim($item->NOMBRE2),
                                    'patLName1' => trim($item->APE1),
                                    'patLName2' => trim($item->APE2),
                                    'PatTDoc' => trim($item->TIP_DOC),
                                    'PatDoc' => trim($item->IDENTIFICACION),
                                    'PatBDate' => $item->FECHA_NACIMIENTO,
                                    'PatAdmDate' => $item->FECHA_INGRESO,
                                    'PatAge' => $item->EDAD,
                                    'patEpsNit' => $item->EPS_NIT,
                                    'patEps' => $item->EPS,
                                    'PatContract' => $item->CONTRATO,
                                    'PatPavilion' => trim($item->PABELLON),
                                    'PatHabitation' => trim($item->HABITACION),
                                    'PatContType' => $item->TIPO_CONTRATO,
                                );

                                $records[] = $temp;
                            }

                            if (count($records) > 0) {

                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $records,
                                    ], 200);
                            } else {

                                return response()
                                    ->json([
                                        'msg' => 'No hay datos en respuesta a la solicitud',
                                        'data' => [],
                                        'status' => 200
                                    ], 400);
                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'No hay datos en respuesta a la solicitud',
                                    'data' => [],
                                    'status' => 200
                                ], 400);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Parametro habitación no recibido',
                                'status' => 400
                            ]);
                    }
                } catch (\Throwable $e) {
                    throw $e;
                }
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/exitus/get/patient-adm-by-document/{document?}/document-type/{doctype?}",
     *     operationId="getAdmPatientInfoByDocumentToPrintHandle",
     *     tags={"Exitus"},
     *     summary="Get Patient Admission by Document",
     *     description="Returns Patient Admission by Document",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="document?",
     *          description="Habitation Code - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="doctype?",
     *          description="Habitation Code - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getAdmPatientInfoByDocumentToPrintHandle(Request $request, $patientDoc = '', $patientDocType = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    if ($patientDoc == "" || $patientDocType == "") {

                        return response()
                            ->json([
                                'msg' => 'Parameters cannot be empty',
                            ]);
                    }

                    $queryPatientToPrintHandle = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM EXITUS_ADMISION_PACIENTE_MANILLA('$patientDoc', '$patientDocType') ORDER BY INGRESO + 0 DESC");

                    if (count($queryPatientToPrintHandle) > 0) {

                        $arrayPatientToPrintHandle = [];

                        foreach ($queryPatientToPrintHandle as $item) {
                            $tempPatientToPrintHandle = array(
                                'patientFirstName' => trim($item->NOMBRE1),
                                'patientSecondName' => trim($item->NOMBRE2),
                                'patientLastName' => trim($item->APE1),
                                'patientSecondLastName' => trim($item->APE2),
                                'patientDocumentType' => trim($item->TIP_DOC),
                                'patientDocument' => trim($item->CEDULA),
                                'patientGender' => trim($item->SEXO),
                                'patientAge' => trim($item->EDAD),
                                'patientBloodType' => trim($item->GRUPO_SANGUINEO),
                                'patientAdmissionNum' => trim($item->INGRESO),
                                'patientAdmissionDate' => trim($item->FECHA_INGRESO),
                                'patientPavilion' => trim($item->PABELLON),
                                'patientHabitation' => trim($item->HABITACION),
                                'patientActualAttentionType' => trim($item->TIPO_ATENCION_ACTUAL_DESC),
                            );

                            $arrayPatientToPrintHandle[] = $tempPatientToPrintHandle;
                        }

                        if (count($arrayPatientToPrintHandle) > 0) {

                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'data' => $arrayPatientToPrintHandle,
                                ], 200);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty ArrayPatientToPrintHandle',
                                    'data' => [],
                                    'status' => 200
                                ], 400);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty QueryPatientToPrintHandle Array',
                                'data' => [],
                                'status' => 200
                            ], 400);
                    }
                } catch (\Throwable $e) {
                    throw $e;
                }
            }
        }
    }
}
