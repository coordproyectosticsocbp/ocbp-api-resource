<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class CaladriusController extends Controller
{

    // ============================================================
    // Middleware to catch the API token
    // ============================================================
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    // ============================================================
    // Function to return the patient Information
    // ============================================================

    /**
     * @OA\Get (
     *     path="/api/v1/caladrius/get/patient-basic-info/{document?}/{doctype?}",
     *     operationId="getCaladriusPatientInfo",
     *     tags={"Caladrius"},
     *     summary="Get getCaladriusPatientInfo",
     *     description="Returns getCaladriusPatientInfo",
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
     *          description="Número de Documento - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="doctype?",
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
    public function getPatientInfo(Request $request, $patientDoc = '', $patientDocType = '')
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
                    }

                    $queryPatientInfo = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CALADRIUS_2_INFORMACION_BASICA_PACIENTE('$patientDoc', '$patientDocType')");

                    if (sizeof($queryPatientInfo) > 0) {

                        $patients = [];

                        foreach ($queryPatientInfo as $item) {
                            $patients[] = [
                                'patientFirstName' => $item->PRIMER_NOMBRE,
                                'patientSecondName' => $item->SEGUNDO_NOMBRE,
                                'patientFirstLastName' => $item->PRIMER_APELLIDO,
                                'patientSecondLastName' => $item->SEGUNDO_APELLIDO,
                                'patientDocument' => $item->DOCUMENTO,
                                'patientDocType' => $item->T_DOC,
                                'patientBirthDate' => $item->FECHA_NAC,
                                'patientAge' => $item->EDAD,
                                'patientGender' => $item->SEXO,
                                'patientBloodType' => $item->GRUPO_SANGUINEO == null ? "" : $item->GRUPO_SANGUINEO,
                                'patientPhone' => $item->TELEFONO1,
                                'patientEmail' => $item->EMAIL,
                                'patientAddress' => $item->DIRECCION
                            ];
                        }

                        if (sizeof($patients) > 0) {

                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'count' => count($patients),
                                    'status' => 200,
                                    'data' => $patients
                                ]);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Patient Array',
                                    'status' => 204,
                                    'data' => []
                                ]);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty PatientInfo Query',
                                'status' => 204,
                                'data' => []
                            ]);
                    }

                    //
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


    // ============================================================
    // Function to return Folios Information
    // ============================================================
    /**
     * @OA\Get (
     *     path="/api/v1/caladrius/get/folios-info-by-document/{document?}/{doctype?}",
     *     operationId="getPatientFoliosInfo",
     *     tags={"Caladrius"},
     *     summary="Get getPatientFoliosInfo",
     *     description="Returns getPatientFoliosInfo",
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
     *          description="Número de Documento - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="doctype?",
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
    public function getPatientFoliosInfo(Request $request, $patientDoc = '', $patientDocType = '')
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
                    }

                    $queryFoliosInfo = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CALADRIUS_2_FOLIOS_CONSULTAS_AMBU('$patientDoc', '$patientDocType') ORDER BY INGRESO DESC, FOLIO + 0 DESC");

                    if (sizeof($queryFoliosInfo) > 0) {

                        $folios = [];
                        $procedures = [];
                        $medicines = [];

                        foreach (json_decode(json_encode($queryFoliosInfo), true) as $item) {

                            if (!isset($folios[$item['CEDULA']])) {
                                $folios[$item['CEDULA']] = array(
                                    'patientDocument' => trim($item['CEDULA']),
                                    'patientDocType' => trim($item['TIP_DOC'])
                                );
                                unset(
                                    $folios[$item['CEDULA']]['INGRESO'],
                                    $folios[$item['CEDULA']]['FECHA_INGRESO'],
                                    $folios[$item['CEDULA']]['FECHA_EGRESO'],
                                    $folios[$item['CEDULA']]['FOLIO'],
                                    $folios[$item['CEDULA']]['HORA_CONSULTA'],
                                    $folios[$item['CEDULA']]['TIPO_ATENCION_ACTUAL'],
                                    $folios[$item['CEDULA']]['TIPO_ATENCION_ACTUAL_DESC'],
                                    $folios[$item['CEDULA']]['DX_EGRESO1'],
                                    $folios[$item['CEDULA']]['DESCRIPCION_PRIMER_DX_EGRESO'],
                                    $folios[$item['CEDULA']]['RECOMENDACION'],
                                    $folios[$item['CEDULA']]['MOTIVO_CONSULTA'],
                                    $folios[$item['CEDULA']]['ENF_ACTUAL'],
                                    $folios[$item['CEDULA']]['CODIGO_CONTRATO'],
                                    $folios[$item['CEDULA']]['CONTRATO_DESCRIPCION'],
                                );
                                $folios[$item['CEDULA']]['folios'] = [];
                            }


                            $tdoc = $item['TIP_DOC'];
                            $queryOrderedProcedures = DB::connection('sqlsrv_hosvital')
                                ->select("SELECT * FROM CALADRIUS_2_PROCEDIMIENTOS_ORDENADOS(" . trim($item['CEDULA']) . ", '$tdoc', " . $item['FOLIO'] . ")");

                            $queryOrderedMedicines = DB::connection('sqlsrv_hosvital')
                                ->select("SELECT * FROM CALADRIUS_2_SUMINISTROS_ORDENADOS(" . trim($item['CEDULA']) . ", '$tdoc', " . $item['FOLIO'] . ")");

                            if (sizeof($queryOrderedProcedures) > 0) {

                                foreach ($queryOrderedProcedures as $procedure) {
                                    $procedures[] = [
                                        'orderPDocument' => $procedure->NRO_DOC,
                                        'orderPDocType' => $procedure->TI_DOC,
                                        'orderPAdmConsecutive' => (int) $procedure->INGRESO,
                                        'orderPFolio' => (int) $procedure->FOLIO,
                                        'orderPContractCode' => trim($procedure->CODIGO_CONTRATO),
                                        'orderPContractDescription' => trim($procedure->CONTRATO),
                                        'orderPPortfolio' => (int) $procedure->CODIGO_PORTAFOLIO,
                                        'orderPProcedureCode' => (int) $procedure->CODIGO_PROCEDIMIENTO,
                                        'orderPProcedureDescription' => trim($procedure->DESCRIPCION_PROCEDIMIENTO),
                                        'orderPProcedurePrice' => trim($procedure->VALOR),
                                        'orderPProcedureQuantity' => (int) $procedure->CANTIDAD,
                                        'orderPProcedureServiceGroup' => trim($procedure->GRUPO_SERVICIO),
                                        'orderPProcedureOrderDate' => $procedure->FECHA_ORDEN,
                                        'orderPProcedureObservation' => trim($procedure->OBSERVACION),
                                    ];
                                }

                                if (sizeof($procedures) < 0) {
                                    $procedures = [];
                                }
                            }

                            if (sizeof($queryOrderedMedicines) > 0) {

                                foreach ($queryOrderedMedicines as $medicine) {
                                    $medicines[] = [
                                        'orderDDocument' => $medicine->DOCUMENTO,
                                        'orderDDocType' => $medicine->TIP_DOC,
                                        'orderDFolio' => (int) $medicine->FOLIO,
                                        'orderDSumCode' => trim($medicine->COD_MED),
                                        'orderDSumDescription' => trim($medicine->DESCRIPCION_MEDICAMENTO),
                                        'orderDSumDose' => (int) $medicine->DOSIS,
                                        'orderDSumQuantity' => (int) $medicine->CANTIDAD,
                                        'orderDSumUnity' => trim($medicine->UNIDAD_MEDIDA),
                                        'orderDSumFrecuency' => trim($medicine->FRECUENCIA),
                                        'orderDSumContractCode' => (int) $medicine->CODIGO_CONTRATO,
                                        'orderDSumContractDescription' => trim($medicine->CONTRATO),
                                        'orderDSumPrice' => $medicine->VALOR_MEDICAMENTO,
                                        'orderDSumOrderDate' => trim($medicine->FECHA),
                                    ];
                                }

                                if (sizeof($medicines) < 0) {
                                    $medicines = [];
                                }
                            }

                            $folios[$item['CEDULA']]['folios'][] = array(
                                'attentionAdmConsecutive' => (int) trim($item['INGRESO']),
                                'attentionAdmDate' => trim($item['FECHA_INGRESO']),
                                'attentionFolio' => (int) trim($item['FOLIO']),
                                'attentionDate' => trim($item['HORA_CONSULTA']),
                                'attentionTypeCode' => (int) trim($item['TIPO_ATENCION_ACTUAL']),
                                'attentionTypeDescription' => trim($item['TIPO_ATENCION_ACTUAL_DESC']),
                                'attentionDxCode' => trim($item['DX_EGRESO1']),
                                'attentionDxDescription' => trim($item['DESCRIPCION_PRIMER_DX_EGRESO']),
                                'attentionRecomendation' => trim($item['RECOMENDACION']),
                                'attentionAppointmentReason' => trim($item['MOTIVO_CONSULTA']) === null ? "" : trim($item['MOTIVO_CONSULTA']),
                                'attentionCurrentIllness' => trim($item['ENF_ACTUAL']),
                                'attentionContractCode' => trim($item['CODIGO_CONTRATO']),
                                'attentionContractDescription' => trim($item['CONTRATO_DESCRIPCION']),
                                'orderedProcedures' => $procedures,
                                'orderedDrugs' => $medicines,
                            );
                        }

                        if (sizeof($folios) > 0) {

                            $folios = array_values($folios);
                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'count' => count($folios),
                                    'data' => $folios
                                ]);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty PatientInfo Query',
                                'status' => 204,
                                'data' => []
                            ]);
                    }


                    //
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

    // ============================================================
    // Function to return contract with portfolio and services
    // ============================================================
    /* public function getContractsWithPortfoliosAndServices(Request $request, $contractNum = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");


            if (count($user) > 0) {

                try {

                    if (!$contractNum) {

                        $contractNum = null;
                    }

                    $queryContracts = DB::connection('sqlsrv_hosvital')
                        ->select('EXEC CALADRIUS_2_CONTRATOS_INFORMACION_GENERAL ?', [
                            $contractNum
                        ]);

                    if (sizeof($queryContracts) > 0) {
                        $contracts = [];


                        foreach ($queryContracts as $item) {

                            $contractObs = $this->replaceCharacter(trim($item->OBSERVACIONES_CONTRATO));

                            $contracts[] = [
                                'contractEpsNit' => trim($item->CODIGO_NIT),
                                'contractEpsDescription' => trim($item->RAZON_SOCIAL_CLIENTE),
                                'contractCode' => trim($item->CODIGO_CONTRATO),
                                'contractDescription' => trim($item->DESCRIPCION_CONTRATO),
                                'contractStatus' => trim($item->ESTADO_CONTRATO),
                                'contractUseCoPago' => trim($item->MANEJA_COPAGO),
                                'contractUseFee' => trim($item->MANEJA_MODERADORA),
                                'contractIsCapitado' => trim($item->ES_CAPITADO),
                                'contractObservations' => $contractObs
                            ];
                        }

                        if (sizeof($contracts) < 0) {

                            return response()
                                ->json([
                                    'msg' => 'Contracts Array is Empty',
                                    'status' => 204,
                                    'data' => []
                                ]);
                        }


                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'status' => 200,
                                'count' => count($contracts),
                                'data' => $contracts
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
    } */



    // ============================================================
    // Function to return all ordered procedures
    // ============================================================

    /**
     * @OA\Get (
     *     path="/api/v1/caladrius/get/amb-ordered-procedures/{document?}/{doctype?}/{folio?}",
     *     operationId="getOrderedProcedures",
     *     tags={"Caladrius"},
     *     summary="Get OrderedProcedures",
     *     description="Returns getOrderedProcedures",
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
     *          description="Número de Documento - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="doctype?",
     *          description="Tipo de Documento - Opcional - RC - TI - CC - CE - NIT - MS - PA - PE - AS",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="folio?",
     *          description="Número Folio",
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
    public function getOrderedProcedures(Request $request, $patientDoc = '', $patientDocType = '', $patientFolioNum = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    if (!$patientDoc || !$patientDocType || !$patientFolioNum) {

                        return response()
                            ->json([
                                'msg' => 'Parameters Cannot Be Empty!',
                                'status' => 400
                            ]);

                        //
                    }


                    $queryPatientInfo = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CALADRIUS_2_INFORMACION_BASICA_PACIENTE('$patientDoc', '$patientDocType')");

                    if (sizeof($queryPatientInfo) > 0) {

                        $patients = [];
                        $drugs = [];
                        $procedures = [];

                        foreach (json_decode(json_encode($queryPatientInfo), true) as $patient) {

                            $queryOrderedMedicines = DB::connection('sqlsrv_hosvital')
                                ->select("SELECT * FROM CALADRIUS_2_SUMINISTROS_ORDENADOS('$patientDoc', '$patientDocType', '$patientFolioNum')");

                            $queryOrderedProcedures = DB::connection('sqlsrv_hosvital')
                                ->select("SELECT * FROM CALADRIUS_2_PROCEDIMIENTOS_ORDENADOS('$patientDoc', '$patientDocType', '$patientFolioNum')");


                            if (sizeof($queryOrderedMedicines) > 0) {

                                foreach ($queryOrderedMedicines as  $medicine) {
                                    $drugs[] = [
                                        'sumCod' => $medicine->COD_MED,
                                        'sumDescripticion' => $medicine->DESCRIPCION_MEDICAMENTO,
                                        'sumDosis' => (int) $medicine->DOSIS,
                                        'sumQuantity' => (int) $medicine->CANTIDAD,
                                        'sumUnity' => $medicine->UNIDAD_MEDIDA,
                                        'sumFrecuency' => $medicine->FRECUENCIA,
                                        'sumPrice' => $medicine->VALOR_MEDICAMENTO,
                                        'sumOrderDate' => $medicine->FECHA
                                    ];
                                }

                                if (sizeof($drugs) < 0) {
                                    $drugs = [];
                                }
                            }

                            if (sizeof($queryOrderedProcedures) > 0) {

                                foreach ($queryOrderedProcedures as  $procedure) {
                                    $procedures[] = [
                                        'procedureCod' => trim($procedure->CODIGO_PROCEDIMIENTO),
                                        'procedureDescripticion' => trim($procedure->DESCRIPCION_PROCEDIMIENTO),
                                        'procedureQuantity' => (int) $procedure->CANTIDAD,
                                        'procedurePrice' => $procedure->VALOR,
                                        'procedureServiceGroup' => trim($procedure->GRUPO_SERVICIO),
                                        'procedureOrderDate' => $procedure->FECHA_ORDEN,
                                        'procedureObservation' => trim($procedure->OBSERVACION),
                                        'procedureSpeciality' => trim($procedure->ESPECIALIDAD)
                                    ];
                                }

                                if (sizeof($procedures) < 0) {
                                    $procedures = [];
                                }
                            }

                            $patients[] = [
                                'patientFirstName' => $patient['PRIMER_NOMBRE'],
                                'patientSecondName' => $patient['SEGUNDO_NOMBRE'],
                                'patientFirstLastname' => $patient['PRIMER_APELLIDO'],
                                'patientSecondLastname' => $patient['SEGUNDO_APELLIDO'],
                                'patientDocument' => $patient['DOCUMENTO'],
                                'patientDocumentType' => $patient['TIP_DOC'],
                                'patientEpsNit' => $patient['EPS_NIT'],
                                'patientEpsName' => $patient['EPS_NOMBRE'],
                                'orderedDrugs' => $drugs,
                                'orderedProcedures' => $procedures
                            ];
                        }

                        if (sizeof($patients) > 0) {

                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'count' => count($patients),
                                    'status' => 200,
                                    'data' => $patients
                                ]);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Int Patient Array',
                                    'status' => 204,
                                    'data' => []
                                ]);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Patient Not Found in HV database',
                                'status' => 204,
                                'data' => []
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


    // ============================================================
    // Function to return all Active Contracts
    // ============================================================

    /**
     * @OA\Get (
     *     path="/api/v1/caladrius/get/active-contracts",
     *     operationId="getAllActiveContracts",
     *     tags={"Caladrius"},
     *     summary="Get getAllActiveContracts",
     *     description="Returns getAllActiveContracts",
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
    public function getAllActiveContracts(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $queryContracts = DB::connection('sqlsrv_hosvital')
                        ->select('SELECT * FROM CALADRIUS_CONTRATOS_ACTIVOS()');

                    if (sizeof($queryContracts) < 0) {
                        return response()
                            ->json([
                                'msg' => 'Empty Query Response',
                                'status' => 204,
                            ], 204);
                    }

                    $contracts = [];

                    foreach ($queryContracts as $item) {

                        $contracts[] = [
                            'contractEpsNit' => trim($item->CODIGO_NIT),
                            'contractEpsName' => trim($item->RAZON_SOCIAL_CLIENTE),
                            'contractCode' => trim($item->CODIGO_CONTRATO),
                            'contractName' => trim($item->DESCRIPCION_CONTRATO),
                            'contractUseCopago' => $item->MANEJA_COPAGO === "NO" ? 0 : 1,
                            'contractUseModeratorFee' => $item->MANEJA_MODERADORA === "NO" ? 0 : 1,
                            'contractIsPgp' => $item->ES_CAPITADO === "SI" ? 1 : 0,
                            'contractObservations' => $item->OBSERVACIONES_CONTRATO === null ? "" : strtoupper($this->replaceCharacter($item->OBSERVACIONES_CONTRATO)),
                        ];
                    }


                    if (sizeof($contracts) < 0) {

                        return response()
                            ->json([
                                'msg' => 'Empty Contracts Array',
                                'status' => 204,
                                'data' => []
                            ], 204);
                    }


                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'counter' => count($contracts),
                            'data' => $contracts
                        ], 200);

                    //
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


    // ============================================================
    // Function to return contract with portfolio and services
    // ============================================================

    /**
     * @OA\Get (
     *     path="/api/v1/caladrius/get/contracts-general-info/{contract?}",
     *     operationId="getContractsWithPortfoliosAndServices",
     *     tags={"Caladrius"},
     *     summary="Get getContractsWithPortfoliosAndServices",
     *     description="Returns getContractsWithPortfoliosAndServices",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="contract?",
     *          description="Número de Contrato",
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
    public function getContractsWithPortfoliosAndServices(Request $request, $contractNum = '')
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");


            if (count($user) > 0) {

                try {

                    if (!$contractNum) {

                        return response()
                            ->json([
                                'msg' => 'Parameters Cannot be Empty',
                                'status' => 400
                            ]);
                    }

                    $queryContractsDetail = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CALADRIUS_2_DETALLE_CONTRATO_POR_CODIGO('$contractNum')");

                    if (sizeof($queryContractsDetail) < 0) {
                        return response()
                            ->json([
                                'msg' => 'Empty Contract Detail Query',
                                'status' => 204
                            ]);
                    }

                    $contractsDetails = [];

                    $queryProceduresPortfoliosDetail = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CALADRIUS_2_DETALLE_PORTAFOLIO_PROCEDIMIENTOS_POR_CODIGO_CONTRATO('$contractNum')");

                    if (sizeof($queryProceduresPortfoliosDetail) < 0) {
                        return response()
                            ->json([
                                'msg' => 'Empty Procedures Portfolio Query',
                                'status' => 204
                            ]);
                    }

                    $proceduresPortfolio = [];

                    $queryMedicinesPortfoliosDetail = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM CALADRIUS_2_DETALLE_PORTAFOLIO_MEDICAMENTOS_POR_CODIGO_CONTRATO('$contractNum')");

                    if (sizeof($queryMedicinesPortfoliosDetail) < 0) {
                        return response()
                            ->json([
                                'msg' => 'Empty Medicines Portfolio Query',
                                'status' => 204
                            ]);
                    }

                    $medicinesPortfolio = [];

                    // ==============================================
                    // Iterating the procedures
                    // ==============================================
                    foreach ($queryProceduresPortfoliosDetail as $procedure) {

                        $proceduresPortfolio[] = [
                            'procedurePortfolioCode' => trim($procedure->CODIGO_PORTAFOLIO_PROCEDIMIENTOS),
                            'procedurePortfolioDescription' => trim($procedure->DESCRIPCION_PORTAFOLIO_PROCEDIMIENTOS),
                            'procedurePortfolioValididty' => $procedure->ULTIMA_VIGENCIA_PORTAFOLIO,
                            'procedureCode' => trim($procedure->CODIGO_PROCEDIMIENTO),
                            'procedureDescription' => trim($procedure->DESCRIPCION_PROCEDIMIENTO),
                            'procedureGroupCode' => trim($procedure->CODIGO_CONCEPTO),
                            'procedureGroupDescription' => trim($procedure->DESCRIPCION_CONCEPTO),
                            'procedurePrice' => $procedure->VALOR_FORLIQ === null || $procedure->VALOR_FORLIQ === '' ? (float) $procedure->VALOR_FIJO : (float) $procedure->VALOR_FORLIQ,
                        ];
                    }

                    if (sizeof($proceduresPortfolio) < 0) {
                        $proceduresPortfolio = [];
                    }

                    // ==============================================
                    // Iterating the medicines
                    // ==============================================
                    foreach ($queryMedicinesPortfoliosDetail as $medicine) {

                        $medicinesPortfolio[] = [
                            'medicinePortfolioCode' => trim($medicine->CODIGO_PORTAFOLIO),
                            'medicinePortfolioDescription' => trim($medicine->DESCRIPCION_PORTAFOLIO),
                            'medicinePortfolioValididty' => $medicine->ULTIMA_VIGENCIA_PORTAFOLIO,
                            'medicineCode' => trim($medicine->CODIGO_SUMINISTRO),
                            'medicineDescription' => trim($medicine->DESCRIPCION_SUMINISTRO),
                            'medicineGroupCode' => trim($medicine->COD_CPTO),
                            'medicineGroupDescription' => trim($medicine->DESCRIPCION_CONCEPTO),
                            'medicinePrice' => $medicine->VALOR_FORLIQ === null || $medicine->VALOR_FORLIQ === '' ? (float) $medicine->VALOR_FIJO : (float) $medicine->VALOR_FORLIQ,
                        ];
                    }

                    if (sizeof($medicinesPortfolio) < 0) {
                        $medicinesPortfolio = [];
                    }


                    // ==============================================
                    // Iterating the contract array
                    // ==============================================
                    foreach ($queryContractsDetail as $item) {

                        $contractsDetails[] = [
                            'contractCode' => trim($item->CODIGO_CONTRATO),
                            'contractDescription' => $item->DESCRIPCION_CONTRATO,
                            'contractUseCopago' => $item->MANEJA_COPAGO,
                            'contractWithModFee' => $item->MANEJA_MODERADORA,
                            'contractisPGP' => $item->ES_CAPITADO,
                            'contractObservations' => $item->OBSERVACIONES_CONTRATO,
                            'contractStatus' => $item->ESTADO_CONTRATO,
                            'procedures' => $proceduresPortfolio,
                            'medicines' => $medicinesPortfolio
                        ];
                    }



                    if (sizeof($contractsDetails) > 0) {

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'status' => 200,
                                'count' => count($contractsDetails),
                                'data' => $contractsDetails
                            ]);
                    }

                    //
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


    // ============================================================
    // Function to place Special Characters
    // ============================================================

    public function replaceCharacter($text)
    {
        if (!$text) {
            return false;
        }
        return str_replace(array("\r", "\n"), '', $text);
    }
}
