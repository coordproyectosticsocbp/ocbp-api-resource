<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuxClinicosController extends Controller
{
    //
    public function __construct()
    {

        return $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/clinical-assistants/get/clinical-assistants",
     *     operationId="ClinicalAssistants",
     *     tags={"AuxiliaresClinicos"},
     *     summary="Get Clinical Assistants Info",
     *     description="Returns Get Clinical Assistants Info",
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
    public function getClinicalAssistants(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $query_clinical_assistants = DB::connection('sqlsrv_kactusprod')
                        ->select("SELECT * FROM AUX_CLINICOS_PERSONAL() ORDER BY Nombre");

                    if (count($query_clinical_assistants) > 0) {

                        $clinic_assistants = [];

                        foreach ($query_clinical_assistants as $item) {
                            $temp_clinical_assistants = array(
                                'caDocument' => $item->DOC,
                                'caDocumentType' => $item->TIP_DOC,
                                'caName' => $item->Nombre,
                                'caLastName' => $item->Apellidos,
                                'caGender' => $item->sexo,
                                'caEmail' => $item->Email,
                                'caPhone' => $item->Telefono,
                                'caAddress' => $item->Direccion,
                                'caBirthDate' => $item->Fecha_Nacimiento,
                                'caInmediateBoss' => $item->JEFE_INMEDIATO,
                                'caPosition' => $item->Cargo,
                                'caCostCenter' => $item->CENTRO_COSTO,
                                'caStatus' => $item->ESTADO_EMPLEADO,
                            );

                            $clinic_assistants[] = $temp_clinical_assistants;
                        }

                        if (count($clinic_assistants) > 0) {
                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'data' => $clinic_assistants
                                ], 200);
                        } else {
                            return response()
                                ->json([
                                    'msg' => 'Empty ClinicAssistants Array',
                                    'status' => 204,
                                    'data' => []
                                ]);
                        }
                    } else {
                        return response()
                            ->json([
                                'msg' => 'Empty ClinicalAssistants Query',
                                'status' => 204,
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
     *     path="/api/v1/clinical-assistants/get/patient-info-by-hab-code/{hab?}",
     *     operationId="getPatientInfoByHabCode",
     *     tags={"AuxiliaresClinicos"},
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
                            ->select("SELECT * FROM AUX_CLINICOS_PACIENTE_X_HABITACION('$habCode')");

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
                                    'PatPavilionCode' => trim($item->PABELLON_CODIGO),
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
                                        'msg' => 'Empyt Patient Array',
                                        'data' => [],
                                        'status' => 204
                                    ], 400);
                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty ClinicAssistants Query',
                                    'data' => [],
                                    'status' => 204
                                ], 400);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Parameters Cannot be Empty',
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
     *     path="/api/v1/clinical-assistants/patient/{patientdoc?}/type/{patientdoctype?}",
     *     operationId="initialPatientInfo",
     *     tags={"AuxiliaresClinicos"},
     *     summary="Get Patient Informations",
     *     description="Returns Patient Information",
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
     *          description="Documento del Paciente",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype?",
     *          description="Tipo de Documento del Paciente - CC, TI, RC, PE",
     *          required=true,
     *          in="path",
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
    public function initialPatientInfo(Request $request, $patientDoc = '', $patientTipoDoc = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    if ($patientDoc != "" && $patientTipoDoc != "") {

                        //$dt = Carbon::now()->format('Y-m-d');

                        $query_patient_info = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM AUX_CLINICOS_PACIENTE_INFO_X_CEDULA('$patientDoc', '$patientTipoDoc')");

                        if (count($query_patient_info) > 0) {

                            $patient_info = [];
                            $deathDateV = "";
                            $deathState = 0;

                            foreach ($query_patient_info as $item) {

                                if ($item->FECHA_DEFUNCION === '1753-01-01 00:00:00.000') {
                                    $deathDateV = "";
                                    $deathState = 0;
                                } else {
                                    $deathDateV = $item->FECHA_DEFUNCION;
                                    $deathState = 1;
                                }

                                $temp = array(
                                    'document' => $item->NUM_HISTORIA,
                                    'tipDoc' => $item->TI_DOC,
                                    'admConsecutive' => $item->INGRESO,
                                    'admDate' => $item->FECHA_INGRESO,
                                    'fName' => $item->PRIMER_NOMBRE,
                                    'sName' => $item->SEGUNDO_NOMBRE,
                                    'fLastname' => $item->PRIMER_APELLIDO,
                                    'sLastname' => $item->SEGUNDO_APELLIDO,
                                    'birthDate' => $item->FECHA_NAC,
                                    'age' => $item->EDAD,
                                    'gender' => $item->SEXO,
                                    'civilStatus' => $item->ESTADOCIVIL,
                                    'patientCompany' => $item->EPS,
                                    'patientContract' => $item->CONTRATO,
                                    'primaryDxCode' => $item->DX_COD,
                                    'primaryDxDescription' => $item->DX,
                                    'patientHabitation' => $item->CAMA,
                                    'patientPavilion' => $item->PABELLON,
                                    'patientDeathStatus' => $deathState,
                                    'patientDeathDate' => $deathDateV,
                                    'realStay' => $item->EstanciaReal,
                                );

                                $patient_info[] = $temp;
                            }

                            if (count($patient_info) > 0) {

                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $patient_info,
                                    ], 200);
                            } else {

                                return response()
                                    ->json([
                                        'msg' => 'Empty Patient Info Array',
                                        'status' => 200,
                                        'data' => [],
                                    ], 200);
                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Patient Info Query Request',
                                    'status' => 200,
                                    'data' => [],
                                ], 200);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Parameters Cannot be Empty',
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
     *     path="/api/v1/clinical-assistants/get-services",
     *     operationId="getServices",
     *     tags={"AuxiliaresClinicos"},
     *     summary="Get Services Informations",
     *     description="Returns Services Information",
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
    public function getPavilionsToMakeServices(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $queryServices = DB::connection('sqlsrv')
                        ->select("SELECT * FROM AUX_CLINICOS_GET_PAVILIONS_TO_MAKE_SERVICES()");

                    if (count($queryServices) > 0) {

                        $pavilions = [];

                        foreach ($queryServices as $item) {

                            $tempServices = array(
                                'serviceName' => trim($item->servName),
                                'serviceDescription' => trim($item->servDescription),
                                'serviceTowerCode' => $item->serTowerCode,
                                'serviceTowerName' => $item->TORRE_DESCRIP,
                                'serviceFloor' => $item->serFloor,
                            );

                            $pavilions[] = $tempServices;
                        }

                        if (count($pavilions) > 0) {

                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'data' => $pavilions,
                                ], 200);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Pavilions Array',
                                    'status' => 200,
                                    'data' => [],
                                ], 200);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty Query Request',
                                'status' => 200,
                                'data' => [],
                            ], 200);
                    }
                } catch (\Throwable $e) {
                    throw $e;
                }
            }
        }
    }
}
