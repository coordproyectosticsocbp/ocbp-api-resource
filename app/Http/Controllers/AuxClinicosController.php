<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
