<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HitoAuditoriaController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }



    /**
     * @OA\Get (
     *     path="/api/v1/hito-auditoria/get/occupation",
     *     operationId="getCensoHitoAuditoria",
     *     tags={"HitoAuditoria"},
     *     summary="Get getCensoHitoAuditoria",
     *     description="Returns censoHitoAuditoria",
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

        try {

            if (!$request->hasHeader('X-Authorization')) return response()->json([
                'msg' => 'Api token not found in Header, please Check it!',
                'status' => 500
            ], 500);


            // VALIDACIÓN SI ENCUENTRA USUARIO CON TOKEN EN BD
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized!',
                'status' => 401
            ]);

            // CONSULTA PATA OBTENER LAS TORRES QUE ESTEN ACTIVAS EN LA ORGANIZACIÓN
            $query_torres = DB::connection('sqlsrv')
                ->select("SELECT towerCode, towerDescription FROM TORRES WHERE towerState = 1");

            if (count($query_torres) < 0) return response()->json([
                'msg' => 'Empty Torres Query Result',
                'status' => 204
            ]);

            $torres = [];

            foreach ($query_torres as $tower) {

                // CONSULTA PARA OBTENER LA RELACIÓN DE TORRE PABELLÓN TENIENDO COMO PARAMETRO EL CÓDIGO DE LA TORRE
                $query_torres_pavs = DB::connection('sqlsrv')
                    ->select("SELECT pavCode, pavFloor FROM HITO_TOWER_PAVILIONS('$tower->towerCode') ORDER BY pavFloor DESC");

                if (count($query_torres_pavs) < 0) return response()->json([
                    'msg' => 'Empty Torres Pav Query Result',
                    'status' => 204
                ]);

                $torres_pav = [];

                foreach ($query_torres_pavs as $tower_pav) {

                    // CONSULTA PARA OBTENER LOS PABELLONES TENIIENDO COMO PARAMETRO EL CÓDIGO DEL PABELLÓN
                    $query_pav = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT CODIGO_PABELLON, NOMBRE_PABELLON, DESCRIPCION_CENTRO_COSTO FROM HITO_PABELLONES('$tower_pav->pavCode')");

                    if (count($query_pav) < 0) return response()->json([
                        'msg' => 'Empty Pavilions Query Result',
                        'status' => 204
                    ]);

                    //$records = [];

                    foreach ($query_pav as $item) {

                        // CONSULTA PARA TRAER PACIENTES DE LAS HABITACIONES ENVIANDO COMO PARAMETRO EL CÓDIGO DEL PABELLÓN
                        $query_censo = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT    COD_PAB, PABELLON, CAMA, ESTADO, NUM_HISTORIA, TI_DOC, NOMBRE_1, NOMBRE_2, APELLIDO_1, APELLIDO_2,
                                                            CONTRATO, TIPO_CONTRATO, FECHA_INGRESO, INGRESO,
                                                            EstanciaReal, DX_CODE, DX
                                                    FROM HITO_AUDITORIA_CENSO_REAL('$item->CODIGO_PABELLON')");

                        if (count($query_censo) < 0) return response()->json([
                            'msg' => 'Empty Censo Query Result',
                            'status' => 204
                        ]);

                        $habs = [];

                        foreach ($query_censo as $cat) {

                            // ARRAY QUE ALMACENA LA INFORMACIÓN DE LOS PACIENTES POR CAMA
                            $habs[] = [
                                'pavCode' => $cat->COD_PAB,
                                'pavName' => $cat->PABELLON,
                                'habitation' => $cat->CAMA,
                                'hab_status' => $cat->ESTADO,
                                'patient_doc' => trim($cat->NUM_HISTORIA),
                                'patient_doctype' => trim($cat->TI_DOC),
                                'patient_firstName' => trim($cat->NOMBRE_1),
                                'patient_secondName' => trim($cat->NOMBRE_2),
                                'patient_surname' => trim($cat->APELLIDO_1),
                                'patient_secondSurname' => trim($cat->APELLIDO_2),
                                //'patient_eps_nit' => $cat->EPS_NIT,
                                //'patient_eps' => $cat->EPS,
                                'contract' => $cat->CONTRATO,
                                'attention_type' => $cat->TIPO_CONTRATO,
                                'admission_date' => $cat->FECHA_INGRESO,
                                'admission_num' => (int) $cat->INGRESO,
                                //'service_admission_date' => $cat->FECHA_INGRESO_SERVICIO,
                                //'patient_birthday' => $cat->FECHA_NACIMIENTO,
                                //'patient_age' => (int) $cat->EDAD,
                                //'patient_gender' => $cat->SEXO,
                                'real_stay' => (int) $cat->EstanciaReal,
                                'diagnosis_code' => $cat->DX_CODE,
                                'diagnosis' => $cat->DX,
                                //'analysisDx' => $cat->DX_MEDICO_ANALISIS,
                            ];
                        }

                        // ARRAY QUE ALMACENA LA INFORMACIÓN DE CADA CAMA POR PABELLÓN
                        $torres_pav[] = [
                            //'towerCode' => $tower_pav->towerCode,
                            'pavCode' => $item->CODIGO_PABELLON,
                            'pavName' => $item->NOMBRE_PABELLON,
                            'pavFloor' => $tower_pav->pavFloor,
                            'habs' => $habs
                        ];
                    }

                    //$torres_pav[] = $records;
                }

                $torres[] = [
                    'towerCode' => $tower->towerCode,
                    'towerDescription' => $tower->towerDescription,
                    'pavilions' => $torres_pav
                ];
            }

            return response()
                ->json([
                    'msg' => 'Ok',
                    'data' => $torres,
                    'status' => 200
                ]);

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hito-auditoria/get/patient/{patientdoc?}/type/{patientdoctype?}/adm/{admNum?}",
     *     operationId="getPatientInfoDetail",
     *     tags={"HitoAuditoria"},
     *     summary="getPatientInfoDetail",
     *     description="getPatientInfoDetail",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="patientDoc?",
     *          description="Número de Documento - Obligatory",
     *          in="path",
     *          required=true,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientDoctype?",
     *          description="Tipo de Documento - Obligatory - RC - TI - CC - CE - NIT - MS - PA - PE - AS - SC",
     *          in="path",
     *          required=true,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="admNum?",
     *          description="Consecutivo de Ingreso - Obligatory",
     *          in="path",
     *          required=true,
     *          @OA\Schema (
     *              type="integer"
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
    public function getPatientInfoDetail(Request $request, $patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            if (!$request->hasHeader('X-Authorization')) return response()->json([
                'msg' => 'Api token not found in Header, please Check it!',
                'status' => 500
            ], 500);


            // VALIDACIÓN SI ENCUENTRA USUARIO CON TOKEN EN BD
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized!',
                'status' => 401
            ]);

            // ---------------
            // VALIDACIÓN DE PARAMETROS DE RUTA

            if (!$patientDoc) return response()
                ->json([
                    'msg' => 'Parametro patientDoc No puede estar vacío',
                    'data' => [],
                    'status' => 200
                ]);

            if (!$patientDoctype) return response()
                ->json([
                    'msg' => 'Parametro patientDoctype No puede estar vacío',
                    'data' => [],
                    'status' => 200
                ]);

            if (!$admNum) return response()
                ->json([
                    'msg' => 'Parametro admNum No puede estar vacío',
                    'data' => [],
                    'status' => 200
                ]);


            // ---------------
            // QUERY DETALLE DEL PACIENTE

            $queryPatientInfoDetail = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HITO_AUDITORIA_INFORMACION_DETALLE_PACIENTE('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryPatientInfoDetail) < 0) return response()->json([
                'msg' => 'No se ha encontrado ningun paciente con información suminstrada',
                'data' => [],
                'status' => 204
            ], 204);

            $patient = null;

            foreach ($queryPatientInfoDetail as $item) {

                if ($item->FECHA_EGRESO === '1753-01-01 00:00:00.000') $item->FECHA_EGRESO = null;

                $patient = [
                    'patient_doc' => trim($item->NUM_HISTORIA),
                    'patient_doctype' => trim($item->TI_DOC),
                    'patient_firstName' => trim($item->NOMBRE_1),
                    'patient_secondName' => trim($item->NOMBRE_2),
                    'patient_surname' => trim($item->APELLIDO_1),
                    'patient_secondSurname' => trim($item->APELLIDO_2),
                    'patient_birthday' => $item->FECHA_NACIMIENTO,
                    'patient_age' => (int) $item->EDAD,
                    'patient_gender' => $item->SEXO,
                    'admission_date' => $item->FECHA_INGRESO,
                    'out_date' => $item->FECHA_EGRESO,
                    'admission_num' => (int) $item->INGRESO,
                    'service_admission_date' => $item->FECHA_INGRESO_SERVICIO,
                    'contract' => trim($item->CONTRATO),
                    'attention_type' => trim($item->TIPO_CONTRATO),
                    'pavCode' => (int) $item->COD_PAB,
                    'pavName' => trim($item->PABELLON),
                    'habitation' => trim($item->CAMA),
                    'main_diagnosis_code' => trim($item->DX_PRINCIPAL_CODE),
                    'main_diagnosis_description' => trim($item->DX_PRINCIPAL),
                    'medical_diagnosis' => trim($item->DX_MEDICO_ANALISIS),
                    'consultation_reason' => trim($item->MOTIVO_CONSULTA),
                    'real_stay' => (int) $item->ESTANCIA_REAL
                ];
            }

            if (count($patient) < 0) return response()->json([
                'msg' => 'Array Pacientes vacio',
                'data' => [],
                'status' => 204
            ], 204);

            return response()->json([
                'msg' => 'Paciente Retornado Correctamente',
                'data' => $patient,
                'status' => 200
            ], 200);


            //

        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
