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

            $patient = [];

            foreach ($queryPatientInfoDetail as $item) {

                if ($item->FECHA_EGRESO === '1753-01-01 00:00:00.000') $item->FECHA_EGRESO = null;

                $patient[] = [
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
                    'real_stay' => (int) $item->ESTANCIA_REAL,
                    'tomografias' => $this->getAllTomografias($patientDoc, $patientDoctype, $admNum),
                    'gamagrafias' => $this->getAllGamagrafias($patientDoc, $patientDoctype, $admNum),
                    'resonancias' => $this->getAllResonancias($patientDoc, $patientDoctype, $admNum),
                    'hemodinamias' => $this->getAllHemodinamia($patientDoc, $patientDoctype, $admNum),
                    'pet_tc' => $this->getAllPetScans($patientDoc, $patientDoctype, $admNum),
                    'interconsultas' => $this->getAllInterconsultas($patientDoc, $patientDoctype, $admNum),
                    'quimioterapias' => $this->getAllQuimioterapias($patientDoc, $patientDoctype, $admNum),
                    'radioterapias' => $this->getAllRadioterapias($patientDoc, $patientDoctype, $admNum),
                    'ecocardiogramas' => $this->getAllEcocardiogramas($patientDoc, $patientDoctype, $admNum),
                    //'pet_Scan' => $this->getAllPetScans($patientDoc, $patientDoctype, $admNum),
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

    private function getAllPetScans($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryPetScan = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_PET_TC('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryPetScan) < 0) return [];

            $petScans = [];

            foreach ($queryPetScan as $pet) {
                $petScans[] = [
                    'admission_num' => (int) $pet->INGRESO,
                    'admission_folio' => (int) $pet->FOLIO,
                    'procedure_code' => trim($pet->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($pet->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $pet->FECHA_ORDEN,
                    'order_application_date' => $pet->FECHA_APLICACION,
                    'service_code' => trim($pet->CODIGO_SERVICIO),
                    'service_description' => trim($pet->CONCEPTO_SERVICIO),
                    'order_status' => trim($pet->ESTADO_PROC),
                ];
            }

            if (count($petScans) < 0) return [];

            return $petScans;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllTomografias($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryTomografia = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_TOMOGRAFIAS('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryTomografia) < 0) return [];

            $tomografias = [];

            foreach ($queryTomografia as $tomografia) {
                $tomografias[] = [
                    'admission_num' => (int) $tomografia->INGRESO,
                    'admission_folio' => (int) $tomografia->FOLIO,
                    'procedure_code' => trim($tomografia->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($tomografia->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $tomografia->FECHA_ORDEN,
                    'order_application_date' => $tomografia->FECHA_APLICACION,
                    'service_code' => trim($tomografia->CODIGO_SERVICIO),
                    'service_description' => trim($tomografia->CONCEPTO_SERVICIO),
                    'order_status' => trim($tomografia->ESTADO_PROC),
                ];
            }

            if (count($tomografias) < 0) return [];

            return $tomografias;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllGamagrafias($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryGamagrafia = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_GAMAGRAFIA('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryGamagrafia) < 0) return [];

            $gamagrafias = [];

            foreach ($queryGamagrafia as $gamagrafia) {
                $gamagrafias[] = [
                    'admission_num' => (int) $gamagrafia->INGRESO,
                    'admission_folio' => (int) $gamagrafia->FOLIO,
                    'procedure_code' => trim($gamagrafia->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($gamagrafia->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $gamagrafia->FECHA_ORDEN,
                    'order_application_date' => $gamagrafia->FECHA_APLICACION,
                    'service_code' => trim($gamagrafia->CODIGO_SERVICIO),
                    'service_description' => trim($gamagrafia->CONCEPTO_SERVICIO),
                    'order_status' => trim($gamagrafia->ESTADO_PROC),
                ];
            }

            if (count($gamagrafias) < 0) return [];

            return $gamagrafias;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllResonancias($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryResonancia = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_RESONANCIAS('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryResonancia) < 0) return [];

            $resonancias = [];

            foreach ($queryResonancia as $resonancia) {
                $resonancias[] = [
                    'admission_num' => (int) $resonancia->INGRESO,
                    'admission_folio' => (int) $resonancia->FOLIO,
                    'procedure_code' => trim($resonancia->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($resonancia->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $resonancia->FECHA_ORDEN,
                    'order_application_date' => $resonancia->FECHA_APLICACION,
                    'service_code' => trim($resonancia->CODIGO_SERVICIO),
                    'service_description' => trim($resonancia->CONCEPTO_SERVICIO),
                    'order_status' => trim($resonancia->ESTADO_PROC),
                ];
            }

            if (count($resonancias) < 0) return [];

            return $resonancias;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllHemodinamia($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryHemodinamia = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_HEMODINAMIA('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryHemodinamia) < 0) return [];

            $hemodinamias = [];

            foreach ($queryHemodinamia as $hemodinamia) {
                $hemodinamias[] = [
                    'admission_num' => (int) $hemodinamia->INGRESO,
                    'admission_folio' => (int) $hemodinamia->FOLIO,
                    'procedure_code' => trim($hemodinamia->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($hemodinamia->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $hemodinamia->FECHA_ORDEN,
                    'order_application_date' => $hemodinamia->FECHA_APLICACION,
                    'service_code' => trim($hemodinamia->CODIGO_SERVICIO),
                    'service_description' => trim($hemodinamia->CONCEPTO_SERVICIO),
                    'order_status' => trim($hemodinamia->ESTADO_PROC),
                ];
            }

            if (count($hemodinamias) < 0) return [];

            return $hemodinamias;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllInterconsultas($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryInterconsultas = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HITO_AUDITORIA_INTERCONSULTAS('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryInterconsultas) < 0) return [];

            $interconsultas = [];

            foreach ($queryInterconsultas as $interconsulta) {
                $interconsultas[] = [
                    'admission_num' => (int) $interconsulta->INGRESO,
                    'admission_folio' => (int) $interconsulta->FOLIO,
                    'speciality_code' => trim($interconsulta->COD_ESPECIALIDAD),
                    'speciality_description' => trim($interconsulta->ESPECIALIDAD),
                    'order_date' => $interconsulta->FECHA_ORDEN,
                    'order_application_date' => $interconsulta->FECHA_RESPUESTA,
                    'order_status' => trim($interconsulta->ESTADO),
                ];
            }

            if (count($interconsultas) < 0) return [];

            return $interconsultas;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllQuimioterapias($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryQuimioterapias = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_QUIMIOTERAPIAS('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryQuimioterapias) < 0) return [];

            $quimioterapias = [];

            foreach ($queryQuimioterapias as $quimioterapia) {
                $quimioterapias[] = [
                    'admission_num' => (int) $quimioterapia->INGRESO,
                    'admission_folio' => (int) $quimioterapia->FOLIO,
                    'procedure_code' => trim($quimioterapia->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($quimioterapia->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $quimioterapia->FECHA_ORDEN,
                    'order_application_date' => $quimioterapia->FECHA_APLICACION,
                    'service_code' => trim($quimioterapia->CODIGO_SERVICIO),
                    'service_description' => trim($quimioterapia->CONCEPTO_SERVICIO),
                    'order_status' => trim($quimioterapia->ESTADO_PROC),
                ];
            }

            if (count($quimioterapias) < 0) return [];

            return $quimioterapias;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    private function getAllRadioterapias($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryRadioterapias = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_RADIOGRAFIAS('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryRadioterapias) < 0) return [];

            $radioterapias = [];

            foreach ($queryRadioterapias as $radioterapia) {
                $radioterapias[] = [
                    'admission_num' => (int) $radioterapia->INGRESO,
                    'admission_folio' => (int) $radioterapia->FOLIO,
                    'procedure_code' => trim($radioterapia->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($radioterapia->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $radioterapia->FECHA_ORDEN,
                    'order_application_date' => $radioterapia->FECHA_APLICACION,
                    'service_code' => trim($radioterapia->CODIGO_SERVICIO),
                    'service_description' => trim($radioterapia->CONCEPTO_SERVICIO),
                    'order_status' => trim($radioterapia->ESTADO_PROC),
                ];
            }

            if (count($radioterapias) < 0) return [];

            return $radioterapias;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    private function getAllEcocardiogramas($patientDoc = null, $patientDoctype = null, $admNum = null)
    {

        try {

            $queryEcocardiogramas = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM BONNACOMMUNITY_PROCEDIMIENTOS_RADIOGRAFIAS('$patientDoc', '$patientDoctype', $admNum)");

            if (count($queryEcocardiogramas) < 0) return [];

            $ecocardiogramas = [];

            foreach ($queryEcocardiogramas as $eco) {

                if ($eco->FECHA_APLICACION === '1753-01-01 00:00:00.000') $eco->FECHA_APLICACION = null;

                $ecocardiogramas[] = [
                    'admission_num' => (int) $eco->INGRESO,
                    'admission_folio' => (int) $eco->FOLIO,
                    'procedure_code' => trim($eco->CODIGO_PROCEDIMIENTO),
                    'procedure_description' => trim($eco->DESCRIPCION_PROCEDIMIENTO),
                    'order_date' => $eco->FECHA_ORDEN,
                    'order_application_date' => $eco->FECHA_APLICACION,
                    'service_code' => trim($eco->CODIGO_SERVICIO),
                    'service_description' => trim($eco->CONCEPTO_SERVICIO),
                    'order_status' => trim($eco->ESTADO_PROC),
                ];
            }

            if (count($ecocardiogramas) < 0) return [];

            return $ecocardiogramas;

            //
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    //
}
