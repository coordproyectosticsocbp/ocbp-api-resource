<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CirugiaController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    // ============================================================

    /**
     * @OA\Get (
     *     path="/api/v1/cirugia/get/patient-procedures/{patientdoc?}/{patientdoctype?}",
     *     operationId="getPatientPendingProcedures",
     *     tags={"Cirugia"},
     *     summary="Get Patient Pending Procedures",
     *     description="Returns Patient Pending Procedures",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="patientdoc?",
     *          description="Número de Documento - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype?",
     *          description="Tipo de Documento - Opcional - RC - TI - CC - CE - NIT - MS - PA - PE - AS",
     *          in="path",
     *          required=false,
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
    public function getLastPendingProcedure(Request $request, $patientDoc = '', $patientDocType = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    if (!$patientDoc || !$patientDocType) {

                        return response()
                            ->json([
                                'msg' => 'Parameters Cannot Be Empty!',
                                'status' => 400
                            ]);

                        //
                    } else {

                        $queryPendingProcedures = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM CIRUGIAX_PROCEDIMIENTOS_PENDIENTES('$patientDoc', '$patientDocType') ");

                        if (count($queryPendingProcedures) > 0) {

                            $procedures = [];

                            foreach (json_decode(json_encode($queryPendingProcedures), true) as $item) {

                                if (!isset($procedures[$item['NUM_DOC']])) {
                                    $procedures[$item['NUM_DOC']] = array(
                                        'patientFirstName' => $item['NOMBRE1'],
                                        'patientSecondName' => $item['NOMBRE2'],
                                        'patientLastName' => $item['APELLIDO1'],
                                        'patientSecondLastName' => $item['APELLIDO2'],
                                        'patientDoc' => $item['NUM_DOC'],
                                        'patientDocType' => $item['TIP_DOC'],
                                        'patientBirthDate' => $item['FECHA_NAC'],
                                        'patientAge' => $item['EDAD'],
                                        'patientGender' => $item['SEXO'],
                                        'patientAdmConsecutive' => $item['INGRESO'],
                                        'patientAdmDate' => $item['FECHA_INGRESO'] != '' ? Carbon::parse($item['FECHA_INGRESO'])->format('Y-m-d H:i:s') : '',
                                        'patientEpsCode' => $item['EPS'],
                                        'patientEpsName' => $item['EPS_NOM'],
                                        'patientContract' => $item['CONTRATO'],
                                    );
                                    unset(
                                        $procedures[$item['NUM_DOC']]['PROCEDIMIENTO_ORDER_NUM'],
                                        $procedures[$item['NUM_DOC']]['PROCEDIMIENTO_COD'],
                                        $procedures[$item['NUM_DOC']]['NOMB_PROCED'],
                                        $procedures[$item['NUM_DOC']]['FECHA_PROGRAMACION'],
                                        $procedures[$item['NUM_DOC']]['QX_ESTADO'],
                                    );
                                    $procedures[$item['NUM_DOC']]['procedures'] = [];
                                }

                                $procedures[$item['NUM_DOC']]['procedures'][] = array(
                                    'procedureOrderNum' => $item['PROCEDIMIENTO_ORDER_NUM'],
                                    'procedureCode' => $item['PROCEDIMIENTO_COD'],
                                    'procedureName' => $item['NOMB_PROCED'],
                                    'procedureScheduleDate' => $item['FECHA_PROGRAMACION'],
                                    'procedureState' => $item['QX_ESTADO'],
                                );
                            }

                            if (count($procedures) > 0) {
                                $procedures = array_values($procedures);
                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $procedures
                                    ], 200);
                            } else {
                                return response()
                                    ->json([
                                        'msg' => 'Procedures Array is Empty',
                                        'status' => 204,
                                        'data' => []
                                    ], 204);
                            }

                            //
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Procedures Query Result',
                                    'status' => 204
                                ]);

                            //
                        }
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {
                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ]);
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/cirugia/get/all-procedures/{procedurecode?}",
     *     operationId="getAllQxProcedures",
     *     tags={"Cirugia"},
     *     summary="Get All Qx Procedures",
     *     description="Returns All Qx Procedures",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="procedurecode?",
     *          description="Código del procedimiento",
     *          in="path",
     *          required=false,
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
    public function getAllQxProcedures(Request $request, $procedureCode = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {


                    $queryAllProcedures = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CIRUGIAX_TODOS_PROCEDIMIENTOS('$procedureCode')");

                    if (count($queryAllProcedures)) {

                        $procedures = [];

                        foreach ($queryAllProcedures as $item) {

                            $procedures[] = array(
                                'procedureCode' => $item->CODIGO_PROCEDIMIENTO,
                                'procedureDescription' => $item->DESCRIPCION_PROCEDIMIENTO
                            );
                        }

                        if (count($procedures) > 0) {

                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'data' => $procedures,
                                    'status' => 200
                                ]);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Procedures Array',
                                    'data' => [],
                                    'status' => 204
                                ]);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty Query Result',
                                'data' => [],
                                'status' => 204
                            ]);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/cirugia/get/scheduled-procedures-by-date/{initdate?}/{enddate?}",
     *     operationId="getScheduledProceduresByDate",
     *     tags={"Cirugia"},
     *     summary="Get ScheduledProceduresByDate",
     *     description="Returns ScheduledProceduresByDate",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="initdate?",
     *          description="Fecha Ini Busqueda",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="enddate?",
     *          description="Fecha Fin Busqueda",
     *          in="path",
     *          required=false,
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
    public function getScheduledProceduresByDate(Request $request, $initDate = '', $endDate = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $init = '';
                    $end = '';

                    if (!$initDate) {

                        $init = Carbon::now()->format('Y-m-d');
                    } else {
                        $init = Carbon::parse($initDate)->format('Y-m-d');
                    }

                    if (!$endDate) {

                        $end = Carbon::now()->format('Y-m-d');
                    } else {
                        $end = Carbon::parse($endDate)->format('Y-m-d');
                    }

                    $queryAllScheduledProcedures = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CIRUGIAX_REQUISITOS_PREVIOS_ACTO_QUIRUGICO_POR_FECHA('$init', '$end')");


                    if (count($queryAllScheduledProcedures) > 0) {

                        $scheduledProcedures = [];

                        foreach (json_decode(json_encode($queryAllScheduledProcedures), true) as $item) {


                            if (!isset($scheduledProcedures[$item['NUM_DOC']])) {

                                $scheduledProcedures[$item['NUM_DOC']] = array(
                                    'patientFirstName' => $item['NOMBRE_1'],
                                    'patientSecondName' => $item['NOMBRE_2'],
                                    'patientLastName' => $item['APELLIDO_1'],
                                    'patientSecondLastName' => $item['APELLIDO_2'],
                                    'patientDoc' => $item['NUM_DOC'],
                                    'patientDocType' => $item['TIP_DOC'],
                                    'patientBirthDate' => $item['FECHA_NAC'],
                                    'patientAge' => $item['EDAD'],
                                    'patientGender' => $item['SEXO'],
                                    'patientAdmConsecutive' => $item['INGRESO'] === null ? "" : $item['INGRESO'],
                                    'patientEpsCode' => $item['EPS_NIT'],
                                    'patientEpsName' => $item['EPS'],
                                    'patientSurgeryCode' => trim($item['PROCEDIMIENTO_ORDER_NUM']),
                                    'patientSurgeryDate' => $item['FECHA_PROCEDIMIENTO'],
                                    'patientSurgeryHour' => $item['HORA_PROCEDIMIENTO'],
                                    'patientSurgeryDurationInHours' => $item['HORAS_DURACION'],
                                    'patientSurgeryDurationInMinutes' => $item['MINUTOS_DURACION'],
                                    'patientSurgeryRoomCode' => $item['SALA_CX_CODE'],
                                    'patientSurgeryRoomName' => trim($item['SALA_CX']),
                                    'patientDxCode' => $item['COD_DX'] === null || $item['COD_DX'] === "" ? "" : $item['COD_DX'],
                                    'patientDxDescription' => $item['DX_NOMBRE'] === null ? "" : $item['DX_NOMBRE'],
                                    'patientSurgeryLaterality' => $item['LATERALIDAD'],
                                    'patientSurgeryRequiresPreanestEva' => $item['VAL_PREANES'],
                                    'patientSurgeryOtionCode' => $item['OPCION'],
                                    'patientSurgeryOptionDescription' => $item['OPCION_NOMBRE'],
                                    'patientNeedRoom' => $item['RESERVA_CAMA'],
                                    'patientNeedRoomType' => $item['TIPO_CAMA'],
                                    'patientSurgeryRequiresEE' => $item['EQUIPOS_ESPECIALES'],
                                    'patientSurgeryRequiresME' => $item['REQ_MAT_ESPECIALES'],
                                    'patientSurgeryBookedBy' => $item['RESERVADO'],
                                    'patientSurgeon' => $item['CIRUJANO'],
                                );

                                unset(
                                    $scheduledProcedures[$item['NUM_DOC']]['PROCEDIMIENTO_COD'],
                                    $scheduledProcedures[$item['NUM_DOC']]['NOMB_PROCED'],
                                    $scheduledProcedures[$item['NUM_DOC']]['CIRUJANO'],
                                    $scheduledProcedures[$item['NUM_DOC']]['VIA_COD'],
                                    $scheduledProcedures[$item['NUM_DOC']]['VIA_DESCRIPCION'],
                                );
                                $scheduledProcedures[$item['NUM_DOC']]['procedures'] = [];
                            }

                            $scheduledProcedures[$item['NUM_DOC']]['procedures'][] = array(
                                'procedureCode' => $item['PROCEDIMIENTO_COD'],
                                'procedureDescription' => $item['NOMB_PROCED'],
                                'procedureSurgeon' => $item['CIRUJANO'],
                                'procedureViaCode' => $item['VIA_COD'],
                                'procedureViaDescription' => $item['VIA_DESCRIPCION'],
                            );
                        }

                        if (count($scheduledProcedures) > 0) {
                            $scheduledProcedures = array_values($scheduledProcedures);
                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'count' => count($scheduledProcedures),
                                    'data' => $scheduledProcedures
                                ], 200);
                        } else {
                            return response()
                                ->json([
                                    'msg' => 'ScheduledProcedures Array is Empty',
                                    'status' => 204,
                                    'data' => []
                                ], 204);
                        }

                        //
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty Query Result',
                                'data' => [],
                                'status' => 204
                            ]);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/cirugia/get/scheduled-procedures-by-document/{patientdoc?}/{patientdoctype?}",
     *     operationId="getScheduledProceduresByDocument",
     *     tags={"Cirugia"},
     *     summary="Get ScheduledProceduresByDocument",
     *     description="Returns ScheduledProceduresByDocumento",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="patientdoc?",
     *          description="Número de Documento - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype?",
     *          description="Tipo de Documento - Opcional - RC - TI - CC - CE - NIT - MS - PA - PE - AS",
     *          in="path",
     *          required=false,
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
    public function getScheduledProceduresByDocument(Request $request, $patientDoc = '', $patientDocType = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    if (!$patientDoc || !$patientDocType) {
                        return response()
                            ->json([
                                'msg' => 'Parameters patientDoc or patiendDocType Canno be Empty',
                                'status' => 400
                            ], 400);
                    } else {

                        $queryAllScheduledProcedures = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM CIRUGIAX_REQUISITOS_PREVIOS_ACTO_QUIRUGICO_POR_DOCUMENTO('$patientDoc', '$patientDocType')");


                        if (count($queryAllScheduledProcedures) > 0) {

                            $scheduledProcedures = [];

                            foreach (json_decode(json_encode($queryAllScheduledProcedures), true) as $item) {


                                if (!isset($scheduledProcedures[$item['NUM_DOC']])) {

                                    $scheduledProcedures[$item['NUM_DOC']] = array(
                                        'patientFirstName' => $item['NOMBRE_1'],
                                        'patientSecondName' => $item['NOMBRE_2'],
                                        'patientLastName' => $item['APELLIDO_1'],
                                        'patientSecondLastName' => $item['APELLIDO_2'],
                                        'patientDoc' => $item['NUM_DOC'],
                                        'patientDocType' => $item['TIP_DOC'],
                                        'patientBirthDate' => $item['FECHA_NAC'],
                                        'patientAge' => $item['EDAD'],
                                        'patientGender' => $item['SEXO'],
                                        'patientAdmConsecutive' => $item['INGRESO'] == null ? "" : $item['INGRESO'],
                                        'patientEpsCode' => $item['EPS_NIT'],
                                        'patientEpsName' => $item['EPS'],
                                        'patientSurgeryCode' => trim($item['PROCEDIMIENTO_ORDER_NUM']),
                                        'patientSurgeryDate' => $item['FECHA_PROCEDIMIENTO'],
                                        'patientSurgeryHour' => $item['HORA_PROCEDIMIENTO'],
                                        'patientSurgeryDurationInHours' => $item['HORAS_DURACION'],
                                        'patientSurgeryDurationInMinutes' => $item['MINUTOS_DURACION'],
                                        'patientSurgeryRoomCode' => $item['SALA_CX_CODE'],
                                        'patientSurgeryRoomName' => trim($item['SALA_CX']),
                                        'patientDxCode' => $item['COD_DX'],
                                        'patientDxDescription' => $item['DX_NOMBRE'],
                                        'patientSurgeryLaterality' => $item['LATERALIDAD'],
                                        'patientSurgeryRequiresPreanestEva' => $item['VAL_PREANES'],
                                        'patientSurgeryOtionCode' => $item['OPCION'],
                                        'patientSurgeryOptionDescription' => $item['OPCION_NOMBRE'],
                                        'patientNeedRoom' => $item['RESERVA_CAMA'],
                                        'patientNeedRoomType' => $item['TIPO_CAMA'],
                                        'patientSurgeryRequiresEE' => $item['EQUIPOS_ESPECIALES'],
                                        'patientSurgeryRequiresME' => $item['REQ_MAT_ESPECIALES'],
                                        'patientSurgeryBookedBy' => $item['RESERVADO'],
                                        'patientSurgeon' => $item['CIRUJANO'],
                                    );

                                    unset(
                                        $scheduledProcedures[$item['NUM_DOC']]['PROCEDIMIENTO_COD'],
                                        $scheduledProcedures[$item['NUM_DOC']]['NOMB_PROCED'],
                                        $scheduledProcedures[$item['NUM_DOC']]['CIRUJANO'],
                                        $scheduledProcedures[$item['NUM_DOC']]['VIA_COD'],
                                        $scheduledProcedures[$item['NUM_DOC']]['VIA_DESCRIPCION'],
                                    );
                                    $scheduledProcedures[$item['NUM_DOC']]['procedures'] = [];
                                }

                                $scheduledProcedures[$item['NUM_DOC']]['procedures'][] = array(
                                    'procedureCode' => $item['PROCEDIMIENTO_COD'],
                                    'procedureDescription' => $item['NOMB_PROCED'],
                                    'procedureSurgeon' => $item['CIRUJANO'],
                                    'procedureViaCode' => $item['VIA_COD'],
                                    'procedureViaDescription' => $item['VIA_DESCRIPCION'],
                                );
                            }

                            if (count($scheduledProcedures) > 0) {
                                $scheduledProcedures = array_values($scheduledProcedures);
                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $scheduledProcedures
                                    ], 200);
                            } else {
                                return response()
                                    ->json([
                                        'msg' => 'ScheduledProcedures Array is Empty',
                                        'status' => 204,
                                        'data' => []
                                    ], 204);
                            }

                            //
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Query Result',
                                    'data' => [],
                                    'status' => 204
                                ]);
                        }
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/cirugia/get/scheduled-procedures-with-tags/{initdate?}/{enddate?}",
     *     operationId="getScheduledProceduresByDateWithTags",
     *     tags={"Cirugia"},
     *     summary="Get getScheduledProceduresByDateWithTags",
     *     description="Returns getScheduledProceduresByDateWithTags",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="initdate?",
     *          description="Fecha Ini Busqueda",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="enddate?",
     *          description="Fecha Fin Busqueda",
     *          in="path",
     *          required=false,
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
    public function getScheduledProceduresByDateWithTags(Request $request, $initDate = '', $endDate = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $init = '';
                    $end = '';

                    if (!$initDate) {

                        $init = Carbon::now()->format('Y-m-d');
                    } else {
                        $init = Carbon::parse($initDate)->format('Y-m-d');
                    }

                    if (!$endDate) {

                        $end = Carbon::now()->format('Y-m-d');
                    } else {
                        $end = Carbon::parse($endDate)->format('Y-m-d');
                    }

                    $queryAllScheduledProcedures = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CIRUGIAX_REQUISITOS_PREVIOS_ACTO_QUIRUGICO_POR_FECHA_TAGS('$init', '$end')");


                    if (count($queryAllScheduledProcedures) > 0) {

                        $scheduledProcedures = [];

                        foreach (json_decode(json_encode($queryAllScheduledProcedures), true) as $item) {


                            if (!isset($scheduledProcedures[$item['NUM_DOC']])) {

                                $scheduledProcedures[$item['NUM_DOC']] = array(
                                    'patientFirstName' => $item['NOMBRE_1'],
                                    'patientSecondName' => $item['NOMBRE_2'],
                                    'patientLastName' => $item['APELLIDO_1'],
                                    'patientSecondLastName' => $item['APELLIDO_2'],
                                    'patientDoc' => $item['NUM_DOC'],
                                    'patientDocType' => $item['TIP_DOC'],
                                    'patientBirthDate' => $item['FECHA_NAC'],
                                    'patientAge' => $item['EDAD'],
                                    'patientGender' => $item['SEXO'],
                                    'patientAdmConsecutive' => $item['INGRESO'] ? $item['INGRESO'] : "",
                                    'patientEpsCode' => $item['EPS_NIT'],
                                    'patientEpsName' => $item['EPS'],
                                    'patientSurgeryCode' => trim($item['PROCEDIMIENTO_ORDER_NUM']),
                                    'patientSurgeryDate' => $item['FECHA_PROCEDIMIENTO'],
                                    'patientSurgeryHour' => $item['HORA_PROCEDIMIENTO'],
                                    'patientSurgeryDurationInHours' => $item['HORAS_DURACION'],
                                    'patientSurgeryDurationInMinutes' => $item['MINUTOS_DURACION'],
                                    'patientSurgeryRoomCode' => $item['SALA_CX_CODE'],
                                    'patientSurgeryRoomName' => trim($item['SALA_CX']),
                                    'patientDxCode' => $item['COD_DX'] ? $item['COD_DX'] : "",
                                    'patientDxDescription' => $item['DX_NOMBRE'] ? $item['DX_NOMBRE'] : "",
                                    'patientSurgeryLaterality' => $item['LATERALIDAD'],
                                    'patientSurgeryRequiresPreanestEva' => $item['VAL_PREANES'],
                                    'patientSurgeryOtionCode' => $item['OPCION'],
                                    'patientSurgeryOptionDescription' => $item['OPCION_NOMBRE'],
                                    'patientNeedRoom' => $item['RESERVA_CAMA'],
                                    'patientNeedRoomType' => $item['TIPO_CAMA'],
                                    'patientSurgeryRequiresEE' => $item['EQUIPOS_ESPECIALES'],
                                    'patientSurgeryRequiresME' => $item['REQ_MAT_ESPECIALES'],
                                    'patientSurgeryBookedBy' => $item['RESERVADO'],
                                    'patientSurgeon' => $item['CIRUJANO'],
                                    // Tags
                                    'surgeryHasPreAnestAppointment' => $item['CITA_VAL_PREANESTESICA'] == 'S' ? 1 : 0,
                                    'surgeryWithPendingAuthorization' => $item['AUTORIZADA_O_PDTE'] == 'S' ? 1 : 0,
                                    'surgeryAuthorizationInProcess' => $item['AUTORIZADA_O_PDTE'] == 'N' ? 1 : 0,
                                    'surgeryAuthorized' => $item['AUTORIZADA_O_PDTE'] == 'N' ? 1 : 0,
                                    'surgeryIsOncological' => "",
                                    'surgeryIsUrgent' => $item['PRIORIDAD'] == 'URGENCIA' ? 1 : 0,
                                );

                                unset(
                                    $scheduledProcedures[$item['NUM_DOC']]['PROCEDIMIENTO_COD'],
                                    $scheduledProcedures[$item['NUM_DOC']]['NOMB_PROCED'],
                                    $scheduledProcedures[$item['NUM_DOC']]['CIRUJANO'],
                                    $scheduledProcedures[$item['NUM_DOC']]['VIA_COD'],
                                    $scheduledProcedures[$item['NUM_DOC']]['VIA_DESCRIPCION'],
                                );
                                $scheduledProcedures[$item['NUM_DOC']]['procedures'] = [];
                            }

                            $scheduledProcedures[$item['NUM_DOC']]['procedures'][] = array(
                                'procedureCode' => $item['PROCEDIMIENTO_COD'],
                                'procedureDescription' => $item['NOMB_PROCED'],
                                'procedureSurgeon' => $item['CIRUJANO'],
                                'procedureViaCode' => $item['VIA_COD'],
                                'procedureViaDescription' => $item['VIA_DESCRIPCION'],
                            );
                            $scheduledProcedures[$item['NUM_DOC']]['surgeryHasMoreThanOneProc'] =
                                count($scheduledProcedures[$item['NUM_DOC']]['procedures']) > 1 ? 1 : 0;
                        }

                        if (count($scheduledProcedures) > 0) {

                            $scheduledProcedures = array_values($scheduledProcedures);
                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'count' => count($scheduledProcedures),
                                    'data' => $scheduledProcedures
                                ], 200);
                        } else {
                            return response()
                                ->json([
                                    'msg' => 'ScheduledProcedures Array is Empty',
                                    'status' => 204,
                                    'data' => []
                                ], 204);
                        }

                        //
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty Query Result',
                                'data' => [],
                                'status' => 204
                            ]);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ]);
            }
        }
    }



    // =========================================================================================
    // Function to get Surgery Details By SurgeryCode
    // =========================================================================================
    /**
     * @OA\Get (
     *     path="/api/v1/cirugia/get/surgery-details/{sugerycode?}",
     *     operationId="getSurgeryDetailBySurgeryCode",
     *     tags={"Cirugia"},
     *     summary="Get getSurgeryDetailBySurgeryCode",
     *     description="Returns getSurgeryDetailBySurgeryCode",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="sugerycode?",
     *          description="Código de la Cirugía",
     *          in="path",
     *          required=true,
     *          @OA\Schema (
     *              type="String"
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
    public function getSurgeryDetailBySurgeryCode(Request $request, $surgeryCode = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (!$user) {

                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ]);
            }

            try {

                if (!$surgeryCode) {

                    return response()
                        ->json([
                            'msg' => 'Parameters Cannot be Empty',
                            'status' => 400
                        ]);
                }

                $querySurgeryDetails = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM CIRUGIAX_DETALLE_CIRUGIA('$surgeryCode')");

                if (sizeof($querySurgeryDetails) < 0) {

                    return response()
                        ->json([
                            'msg' => 'Empty SurgeryDetails Query',
                            'status' => 204
                        ]);
                }

                $surgery = [];

                foreach (json_decode(json_encode($querySurgeryDetails), true) as $item) {

                    if (!isset($surgery[$item['CODIGO_CIRUGIA']])) {
                        $surgery[$item['CODIGO_CIRUGIA']] = [
                            'patientDocument'  => trim($item['DOCUMENTO']),
                            'patientDocumentType'  => trim($item['TIP_DOC']),
                            'surgeryCode'  => trim($item['CODIGO_CIRUGIA']),
                            'surgeryDate'  => trim($item['FECHA_PROGRAMACION']),
                            'surgeryHour'  => trim($item['HORA_PROCEDIMIENTO']),
                            'surgeryDurationInHours'  => trim($item['HORAS_DURACION']),
                            'surgeryDurationInMinutes'  => trim($item['MINUTOS_DURACION']),
                            'surgeryRoomCode'  => trim($item['SALA_CX_CODE']),
                            'surgeryRoomDescription'  => trim($item['SALA_CX']),
                            'surgeon' => trim($item['CIRUJANO']),
                            'surgeryObservation1' => trim($item['OBSERVACION_CIRUGIA']),
                            'surgeryObservation2' => trim($item['OBSERVACION_CIRUGIA_2']),
                            'surgerySpecialEquipmentObservation' => trim($item['OBSERVACION_EQUIPOS_ESPECIALES']),
                            'surgeryHemoDObservation' => trim($item['OBSERVACION_HEMODINAMIA']),
                        ];

                        unset(
                            $surgery[$item['CODIGO_CIRUGIA']]['PROCEDIMIENTO_CODIGO'],
                            $surgery[$item['CODIGO_CIRUGIA']]['PROCEDIMIENTO_NOMBRE'],
                        );
                        $surgery[$item['CODIGO_CIRUGIA']]['procedures'] = [];
                    }


                    $surgery[$item['CODIGO_CIRUGIA']]['procedures'][] = [
                        'procedureCode' => $item['PROCEDIMIENTO_CODIGO'],
                        'procedureDescription' => $item['PROCEDIMIENTO_NOMBRE'],
                    ];
                }

                if (sizeof($surgery) > 0) {

                    $surgery = array_values($surgery);

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'counter' => count($surgery),
                            'data' => $surgery
                        ]);

                    //
                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty SurgeryDetails Array',
                            'status' => 204,
                            'counter' => 0,
                            'data' => []
                        ]);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
}
