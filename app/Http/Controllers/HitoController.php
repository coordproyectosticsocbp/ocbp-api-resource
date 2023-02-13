<?php

namespace App\Http\Controllers;

use COM;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HitoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/occupation-with-real-stay",
     *     operationId="occupationHito",
     *     tags={"Hito"},
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

            try {
                // CONSULTA PATA OBTENER LAS TORRES QUE ESTEN ACTIVAS EN LA ORGANIZACIÓN
                $query_torres = DB::connection('sqlsrv')
                    ->select("SELECT towerCode, towerDescription FROM TORRES WHERE towerState = 1");

                if (count($query_torres) > 0) {

                    $torres = [];

                    foreach ($query_torres as $tower) {

                        // CONSULTA PARA OBTENER LA RELACIÓN DE TORRE PABELLÓN TENIENDO COMO PARAMETRO EL CÓDIGO DE LA TORRE
                        $query_torres_pavs = DB::connection('sqlsrv')
                            ->select("SELECT pavCode, pavFloor FROM HITO_TOWER_PAVILIONS('$tower->towerCode') ORDER BY pavFloor DESC");

                        if (count($query_torres_pavs) > 0) {

                            $torres_pav = [];

                            foreach ($query_torres_pavs as $tower_pav) {

                                // CONSULTA PARA OBTENER LOS PABELLONES TENIIENDO COMO PARAMETRO EL CÓDIGO DEL PABELLÓN
                                $query = DB::connection('sqlsrv_hosvital')
                                    ->select("SELECT CODIGO_PABELLON, NOMBRE_PABELLON, DESCRIPCION_CENTRO_COSTO FROM HITO_PABELLONES('$tower_pav->pavCode')");


                                if (count($query) > 0) {

                                    $records = [];

                                    foreach ($query as $item) {

                                        // CONSULTA PARA TRAER PACIENTES DE LAS HABITACIONES ENVIANDO COMO PARAMETRO EL CÓDIGO DEL PABELLÓN
                                        $query2 = DB::connection('sqlsrv_hosvital')
                                            ->select("SELECT    COD_PAB, PABELLON, CAMA, ESTADO, NUM_HISTORIA, TI_DOC, NOMBRE_PACIENTE,
                                                                EPS_NIT, EPS, EPS_EMAIL, CONTRATO, TIPO, FECHA_INGRESO, INGRESO, EDAD,
                                                                SEXO, PREALTA, EstanciaReal, DX
                                                        FROM HITO_CENSOREAL('$item->CODIGO_PABELLON')");



                                        if (count($query2) > 0) {



                                            $habs = [];

                                            foreach ($query2 as $cat) {

                                                if ($cat->PREALTA != null) {
                                                    $cat->PREALTA = 1;
                                                } else {
                                                    $cat->PREALTA = 0;
                                                }

                                                $habs[] = array(
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
                                                );
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
                                                    'status' => 204
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

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/occupation-with-real-stay-two",
     *     operationId="getCensoNew",
     *     tags={"Hito"},
     *     summary="Get getCensoNew",
     *     description="Returns getCensoNew",
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
    public function getCensoNew(Request $request)
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
                            ->select("SELECT    COD_PAB, PABELLON, CAMA, ESTADO, NUM_HISTORIA, TI_DOC, NOMBRE_PACIENTE,
                                                            EPS_NIT, EPS, EPS_EMAIL, CONTRATO, TIPO, FECHA_INGRESO, INGRESO, EDAD,
                                                            SEXO, PREALTA, EstanciaReal, DX
                                                    FROM HITO_CENSOREAL('$item->CODIGO_PABELLON')");

                        if (count($query_censo) < 0) return response()->json([
                            'msg' => 'Empty Censo Query Result',
                            'status' => 204
                        ]);

                        $habs = [];

                        foreach ($query_censo as $cat) {

                            if ($cat->PREALTA != null) {
                                $cat->PREALTA = 1;
                            } else {
                                $cat->PREALTA = 0;
                            }

                            // ARRAY QUE ALMACENA LA INFORMACIÓN DE LOS PACIENTES POR CAMA
                            $habs[] = [
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
     *     path="/api/v1/hito/get/patient-info-by-hab-code/{hab?}",
     *     operationId="getPatientInfoByHabCodeHito",
     *     tags={"Hito"},
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

            try {

                if ($habCode) {

                    $query = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HITO_PACIENTE_X_HABITACION('$habCode')");

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
                                'PatCompany' => $item->EMPRESA,
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
                                    'data' => $records,
                                    'status' => 200
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

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/patient-adm-output-info/{patientdoc?}/{patientdoctype?}",
     *     operationId="get Adm Output Info By NumDoc",
     *     tags={"Hito"},
     *     summary="Get Adm Output Info By NumDoc",
     *     description="Returns Adm Output Info By NumDoc",
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
    public function getAdmOutDateByDocument(Request $request, $patientDoc = '111', $patientDoctype = 'TR')
    {

        if ($request->hasHeader('X-Authorization')) {
            if ($patientDoc && $patientDoctype) {

                $query = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM HITO_INGRESOS_EGRESOS('$patientDoc', '$patientDoctype')");

                if (count($query) > 0) {

                    $records = [];

                    foreach ($query as $item) {

                        $temp = array(
                            'patientDoc' => trim($item->CEDULA),
                            'patientDoctype' => $item->TIP_DOC,
                            'patAdmConsecutive' => $item->INGRESO,
                            'patAdmDate' => $item->FECHA_INGRESO,
                            'patOutputDate' => $item->FECHA_EGRESO,
                            'attentionTypeCode' => $item->TIPO_ATENCION_ACTUAL,
                            'attentionTypeDes' => $item->TIPO_ATENCION_ACTUAL_DESC,
                            'outputDxCode' => trim($item->DX_EGRESO1),
                            'outputDxDes' => trim($item->DESCRIPCION_PRIMER_DX_EGRESO),
                        );

                        $records[] = $temp;
                    }

                    if (sizeof($records) > 0) {

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'data' => $records,
                                'status' => 200
                            ], 200);
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Records Array Empty',
                                'data' => [],
                                'status' => 200
                            ], 200);
                    }
                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty Query Request, Because No Parameter Has Been Sent',
                            'data' => [],
                            'status' => 200
                        ], 200);
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Empty Parameters, Please Check them',
                        'status' => 400
                    ], 400);
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/patient/{patientdoc?}/type/{patientdoctype?}/information",
     *     operationId="get clinic Historial By NumDoc",
     *     tags={"Hito"},
     *     summary="Get clinic Historial By NumDoc",
     *     description="Returns clinic Historial By NumDoc",
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
     *          description="Número de Documento - Obligatory",
     *          in="path",
     *          required=true,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype?",
     *          description="Tipo de Documento - Obligatory - RC - TI - CC - CE - NIT - MS - PA - PE - AS - SC",
     *          in="path",
     *          required=true,
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
            if ($patientDoc != "" && $patientTipoDoc != "") {

                $dt = Carbon::now()->format('Y-m-d');

                $query_patient_info = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM HITO_INFORMACION_HISTORIAL_CLINICO_V2('$patientDoc', '$patientTipoDoc')");

                if (count($query_patient_info) > 0) {
                    $patient_info = [];

                    foreach ($query_patient_info as $item) {


                        $query_consul_reason = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM HITO_MOTIVO_CONSULTA('$item->NUM_HISTORIA', '$item->TI_DOC')");

                        $query_antecedentes = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT TOP 5 * FROM HITO_ANTECEDENTES('$item->NUM_HISTORIA', '$item->TI_DOC')");


                        /* $query_riesgos = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM HITO_RIESGOS_PACIENTE('$item->NUM_HISTORIA', '$item->TI_DOC', '$item->FOLIO_FORMATO')"); */


                        // VALIDACIÓN PARA LOS RIESGOS DEL PACIENTE
                        /* if (count($query_riesgos) > 0) {

                            $riesgos = [];

                            foreach ($query_riesgos as $riesgo) {
                                $temp4 = array(
                                    'Hipotensión_por_uso_de_vasodilatador' => $riesgo->UCI_HIPOTENSION_VASODILATADOR === 1 ? 1  : 0,
                                    'Arritmias_asociadas_a_uso_de_vasopresores' => $riesgo->UCI_ARRITMIA_VASOPRESORES === 1 ? 1  : 0,
                                    'Evento_Cerebrovascular_asociado_a_uso_de_vasopresor' => $riesgo->UCI_CEREBROVASCULAR_VASOPRESORES === 1 ? 1  : 0,
                                    'Desarrollo_de_delirium_en_paciente_critico' => $riesgo->UCI_DELIRIUM_PACIENTE === 1 ? 1  : 0,
                                    'Síndrome_de_desacondicionamiento_físico_en_paciente_critico' => $riesgo->UCI_DESACONDICIONAMIENTO_FISICO === 1 ? 1  : 0,
                                    'Fistula_traqueoesofagica_por_entubación_orotraqueal_prolongada' => $riesgo->UCI_FISTULA_ORATRAQUEAL === 1 ? 1  : 0,
                                    'Estenosis_subglotica_por_entubación_orotraqueal_prolongada' => $riesgo->UCI_ESTENOSIS_X_ENTUBACION === 1 ? 1  : 0,
                                    'Inestabilidad_hemodinámica_durante_hemodiálisis' => $riesgo->UCI_INESTABILIDAD_HEMODINAMICA === 1 ? 1  : 0,
                                    'Neumotórax_por_inserción_de_catéter_venoso_central' => $riesgo->UCI_NEUMOTORAX_CATETER_CENTRAL === 1 ? 1  : 0,
                                    'Lesión_pulmonar_asociada_a_ventilación_mecánica' => $riesgo->UCI_LESION_PULMONAR === 1 ? 1  : 0,
                                    'Lesion_isquemica_distal_por_vasopresores_en_pacientes_con_sepsis_en_la_UCI_Adultos' => $riesgo->UCI_LESION_ISQUEMICA === 1 ? 1  : 0,
                                    'Neutropenia_febril_en_pacientes_con_quimioterapia' => $riesgo->UCI_NEUTROPENIA_FEBRIL_QUIMIOTERAPIAS === 1 ? 1  : 0,
                                    'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->UCI_MUCOSITIS_QUIMIOTERAPIA === 1 ? 1  : 0,
                                    'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->UCI_HEMORRAGIA_ANTICOAGULADO === 1 ? 1  : 0,
                                    'Sangrado_segundario_a_trombocitopenia_severa' => $riesgo->UCIPED_SANGRADO_TROMBOCITOPENIA === 1 ? 1  : 0,
                                    'Hipotensión_por_desequilibrio_electrolítico_en_dengue_grave' => $riesgo->UCIPED_HIPOTENSION_ELECTROLITICO === 1 ? 1  : 0,
                                    'Shock_hipovolémico_por_insuficiencia__renal_aguda_en_pediatría' => $riesgo->UCIPED_SHOCK_HIPOVOLEMICO === 1 ? 1  : 0,
                                    'Falla_respiratoria_por_neumonía' => $riesgo->UCIPED_FALLA_RESPIRATORIA === 1 ? 1  : 0,
                                    'Sepsis_severa_en_aplasia_medular' => $riesgo->UCIPED_SEPSIS_APLASIA_MEDULAR === 1 ? 1  : 0,
                                    'Síndrome_de_Lisis_tumoral_en_Linfoma_No_Hodgkin' => $riesgo->UCIPED_SINDROME_LINFOMA_NOHODKIN === 1 ? 1  : 0,
                                    'Hipervolemia_por_no_restricciones_de_líquido_en_síndrome_nefrítico' => $riesgo->UCIPED_HIPERVOLEMIA === 1 ? 1  : 0,
                                    'Edema_pulmonar_por_extravasación_de_liquido_en_síndrome_nefrótico' => $riesgo->UCIPED_EDEMA_PULMONAR === 1 ? 1  : 0,
                                    'Crisis_hipertensiva_en_síndrome_nefrítico' => $riesgo->UCIPED_CRISIS_HIPERTENSIVA === 1 ? 1  : 0,
                                    'Hematuria_asociada_a_síndrome_nefrítico' => $riesgo->UCIPED_HEMATURIA_NEFRITICO === 1 ? 1  : 0,
                                    'Síndrome_Compresivos_en_Linfoma_No_Hodgkin' => $riesgo->UCIPED_SINDROME_COMPRENSI_UCI_PED === 1 ? 1  : 0,
                                    'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->UCIPED_MUCOSITIS_QUIMIOTERAPIA_UCI_PED === 1 ? 1  : 0,
                                    'Dolor_en_paciente_oncologico' => $riesgo->UCIPED_DOLOR_PACIENTE_ONCOLOGICO === 1 ? 1  : 0,
                                    'Neumonia_asociada_a_ventilacion_mecanica_en_UCIP' => $riesgo->UCIPED_NEUMONIA_VENTILACION_MECANICA === 1 ? 1  : 0,
                                    'Shock_hipovolémico_secundario_a_sangrado_agudo_en_procedimiento_quirúrgico_programado' => $riesgo->CIR_SHOCK_HIPOVOLEMICO === 1 ? 1  : 0,
                                    'Parada_cardiaca_durante_procedimiento_quirúrgico' => $riesgo->CIR_PARADA_CARDIACA === 1 ? 1  : 0,
                                    'Dehiscencia_de_herida_quirúrgica' => $riesgo->CIR_DEHISCENCIA_QX === 1 ? 1  : 0,
                                    'Seroma_postquirúrgico' => $riesgo->CIR_SEROMA_POSTQX === 1 ? 1  : 0,
                                    'Incremento_del_dolor_neuropatico_post_cirugía_oncológica_de_mama' => $riesgo->CIR_INCREMENTO_DOLOR_POSTQX === 1 ? 1  : 0,
                                    'Hernias_en_pared_abdominal_postquirúrgicas' => $riesgo->CIR_HERNIAS_ABDOMINAL_POSTQX === 1 ? 1  : 0,
                                    'Fistula_postcirugia_abdominal' => $riesgo->CIR_FISTULAQX_ABDOMINAL === 1 ? 1  : 0,
                                    'Lesión_de_uréteres_durante_cirugía_de_tumor_retroperitoneal' => $riesgo->CIR_LESION_URETERES === 1 ? 1  : 0,
                                    'Lesión_isquémica_por_perfusión_inadecuada_prolongada__durante_cirugía_de_tumor_retroperitoneal' => $riesgo->CIR_LESION_ISQUEMICA_QX_TUMOR === 1 ? 1  : 0,
                                    'Neumonía_por_realización_de_cirugía_abdominales_mayores' => $riesgo->CIR_NEUMONIA_ABDOMINALES_MAYORES === 1 ? 1  : 0,
                                    'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->CIR_HEMORRAGIA_TROMBOSIS === 1 ? 1  : 0,
                                    'Hematoma_por_obstrucción_de_hemovac_en_cirugía_de_mama_con_vaciamiento' => $riesgo->CIR_HEMATOMA_OBSTRUCCION_HEMOVAC === 1 ? 1  : 0,
                                    'Íleo_paralítico_en_pos_quirúrgico_de_colon_por_movilización_tardía' => $riesgo->CIR_ILEO_PARALITICO_POSTQX === 1 ? 1  : 0,
                                    'Linfaedema_post_cirugía_de_mama_con_vaciamiento_axilar' => $riesgo->CIR_LINFAEDEMA_POSTQX === 1 ? 1  : 0,
                                    'Obstrucción_de_sonda_vesical_en_post_quirúrgico_de_prostatectomía_transvesical' => $riesgo->CIR_OBSTRUCCION_SONDA_VESICAL === 1 ? 1  : 0,
                                    'Hematórax_o_colección_residual_por_drenaje_inadecuado_de_pleurovac_en_cirugía_de_tórax' => $riesgo->CIR_HEMOTORAX_POR_DRENAJE === 1 ? 1  : 0,
                                    'Peritonitis_por_dolor_abdominal' => $riesgo->CIR_PERITONITIS_DOLOR_ABDOMINAL === 1 ? 1  : 0,
                                    'Infeccion_de_sitio_quirurgico' => $riesgo->CIR_INFECCION_SITIO_QX === 1 ? 1  : 0,
                                    'Neutropenia_febril_en_pacientes_con_quimioterapia_hospitalaria' => $riesgo->HOSP_NEUTROPENIA_FEBRIL === "1" ? 1  : 0,
                                    'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->HOSP_MUCOSITIS === "1" ? 1  : 0,
                                    'Progresion_de_las_complicaciones_por_radioterapia' => $riesgo->HOSP_PROGRESION_COMPLICACIONES_RADIOTERAPIA === "1" ? 1  : 0,
                                    'Malnutricion_en_paciente_oncologico' => $riesgo->HOSP_MALNUTRICION === "1" ? 1  : 0,
                                    'Dolor_en_paciente_oncologico' => $riesgo->HOSP_DOLOR === "1" ? 1 : 0,
                                    'Riesgo_de_Suicidio_' => $riesgo->HOSP_SUICIDIO === "1" ? 1  : 0,
                                    'Infeccion_de_sitio_quirurgico' => $riesgo->HOSP_INFECCION_SITIO_QX === "1" ? 1  : 0,
                                    'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->HOSP_HEMORRAGIA_TROMBOSIS === "1" ? 1  : 0,
                                    'Retraso_en_atencion_en_pacientes_con_patologia_oncologica_(Ca_de_mama,_Leucemia_en_pediatria)' => $riesgo->HOSP_RETRASO_ATENCION === "1" ? 1  : 0,
                                    'Retraso_en_atencion_en_pacientes_con_patologia_oncologica' => $riesgo->HOSP_RETRASO_ATENCION === "1" ? 1  : 0,
                                    'Hematoma_por_obstrucción_de_hemovac_en_cirugía_de_mama_con_vaciamiento' => $riesgo->HOSP_HEMATOMA_OBSTRUCCION_HEMOVAC === "1" ? 1  : 0,
                                    'Íleo_paralítico_en_pos_quirúrgico_de_colon_por_movilización_tardía' => $riesgo->HOSP_ILEO_PARALITICO_POSTQX === "1" ? 1  : 0,
                                    'Linfaedema_post_cirugía_de_mama_con_vaciamiento_axilar' => $riesgo->HOSP_LINFAEDEMA_POSTQX === "1" ? 1  : 0,
                                    'Obstrucción_de_sonda_vesical_en_post_quirúrgico_de_prostatectomía_transvesical' => $riesgo->HOSP_OBSTRUCCION_SONDA_VESICAL === "1" ? 1  : 0,
                                    'Hematórax_o_colección_residual_por_drenaje_inadecuado_de_pleurovac_en_cirugía_de_tórax' => $riesgo->HOSP_HEMOTORAX_POR_DRENAJE === "1" ? 1  : 0,
                                    'Peritonitis_por_dolor_abdominal' => $riesgo->URG_PERITONITIS_DOLOR_ABDOMINAL === 1 ? 1  : 0,
                                    'Neutropenia_febril_en_pacientes_con_quimioterapia_hospitalaria' => $riesgo->URG_NEUTROPENIA_FEBRIL === 1 ? 1  : 0,
                                    'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->URG_MUCOSITIS === 1 ? 1  : 0,
                                    'Progresion_de_las_complicaciones_por_radioterapia_' => $riesgo->URG_PROGRESION_COMPLICACIONES_RADIOTERAPIA === 1 ? 1  : 0,
                                    'Malnutricion_en_paciente_oncologico' => $riesgo->URG_MALNUTRICION === 1 ? 1  : 0,
                                    'Dolor_en_paciente_oncologico' => $riesgo->URG_DOLOR === 1 ? 1  : 0,
                                    'Riesgo_de_Suicidio' => $riesgo->URG_SUICIDIO === 1 ? 1  : 0,
                                    'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->URG_HEMORRAGIA_TROMBOSIS === 1 ? 1  : 0,
                                    'Falla_ventilatoria_secundaria_en_paciente_con_derrame_pleural' => $riesgo->URG_FALLA_VENTILATORIA_DERRAME_PLEURAL === 1 ? 1  : 0,
                                    'Perforación_intestinal_por_obstruccion_mecanica' => $riesgo->URG_PERFORACION_POR_OBSTRUCCION === 1 ? 1  : 0,
                                    'Sangrado_cerebral_secundario_a_trombocitopenia_severa' => $riesgo->URG_SANGRADO_CEREBRAL === 1 ? 1  : 0,
                                    'Choque_hipovolemico_por_hemorragia_vaginal_en_cancer_de_cervix' => $riesgo->URG_CHOQUE_HIPOVOLEMICO === 1 ? 1  : 0,
                                    'Tromboembolismo_Pulmonar_en_paciente_con_fractura_de_cadera' => $riesgo->URG_TROMBOEMBOLISMO_PULMONAR === 1 ? 1  : 0,
                                    'Lesion_uretral_en_paciente_con_trombocitopenia_post_colocacion_de_sonda_vesical' => $riesgo->URG_LESION_URETRAL === 1 ? 1  : 0,
                                    'Aplasia_prolongada_secundaria_a_acondicionamiento_en_TAMO' => $riesgo->TAMO_APLASIA_PROLONGADA === 1 ? 1  : 0,
                                    'Síndrome_de_la_pega' => $riesgo->TAMO_SINDROME_PEGA === 1 ? 1  : 0,
                                    'Mucositis_severa_secundaria_a_condicionamiento_en_TAMO' => $riesgo->TAMO_MUCOSITIS === 1 ? 1  : 0,
                                    'Falla_en_la_colecta_de_CD34_por_movilización_deficiente' => $riesgo->TAMO_FALLA_COLECTA_CD34 === 1 ? 1  : 0,
                                    'Desarrollo_de_delirium_en_paciente_critico' => $riesgo->TAMO_DELIRIUM_PACIENTE_CRITICO === 1 ? 1  : 0,
                                    'Síndrome_de_desacondicionamiento_físico_en_paciente_critico' => $riesgo->TAMO_SINDROME_DESACONDICIONAMIENTO === 1 ? 1  : 0,
                                    'Sangrado_cerebral_secundario_a_trombocitopenia_severa' => $riesgo->TAMO_SANGRADO_CEREBRAL === 1 ? 1  : 0,
                                    'Falla_respiratoria_por_neumonía' => $riesgo->TAMO_FALLA_RESPIRATORIA_NEUMONIA === 1 ? 1  : 0,
                                    'Sepsis_severa_en_aplasia_medular' => $riesgo->TAMO_SEPSIS_APLASIA_MEDULAR === 1 ? 1  : 0,
                                    'Edema_agudo_de_pulmon_por_sobrecarga_de_volumen' => $riesgo->TAMO_EDEMA_AGUDO_PULMON === 1 ? 1  : 0,
                                    'Reaccion_alergica_a_acondicionamiento_con_melfalan' => $riesgo->TAMO_REACCION_ALERGICA === 1 ? 1  : 0,
                                    'Deshidratación_por_diarrea_y/o_vomito_postquimioterapia_con_alquilantes' => $riesgo->TAMO_DESHIDRATACION_DIARREA === 1 ? 1  : 0,

                                );

                                $riesgos[] = $temp4;
                            }
                        } else {

                            $riesgos = [];
                        } */


                        // VALIDACIÓN PARA LOS ANTECEDENTES DEL PACIENTE
                        if (count($query_antecedentes) > 0) {

                            $antecedentes = [];

                            foreach ($query_antecedentes as $antecedente) {

                                $temp3 = array(
                                    'folio' => $antecedente->FOLIO ?: '',
                                    'backDate' => $antecedente->FECHA ?: '',
                                    'backGroup' => $antecedente->GRUPO_ANTECEDENTE ?: '',
                                    'backSubGroup' => $antecedente->SUBGRUPO_ANTECEDENTE ?: '',
                                    'backDesc' => $antecedente->ANTECEDENTES ?: '',
                                );

                                $antecedentes[] = $temp3;
                            }
                        } else {

                            $antecedentes = [];
                        }


                        // VALIDACIÓN PARA LOS MOTIVOS DE CONSULTA DEL PACIENTE
                        if (count($query_consul_reason) > 0) {

                            $consul_reason = [];

                            foreach ($query_consul_reason as $cr) {

                                if ($item->NEUTROPENIA != null) {
                                    $item->NEUTROPENIA = 1;
                                } else {
                                    $item->NEUTROPENIA = 0;
                                }

                                if ($item->PREALTA != null) {
                                    $item->PREALTA = 1;
                                } else {
                                    $item->PREALTA = 0;
                                }

                                $temp2 = array(
                                    'lastInterConsulDoctorDoc' => $item->DOCUMENTO_ESPECIALISTA_INTERCONSULTA ?: '',
                                    'lastInterConsulDoctor' => $item->ULTIMO_ESPECIALISTA_INTERCONSULTA ?: '',
                                    'lastInterConsulSpeciality' => $item->ESPECIALIDAD_ULTIMA_INTERCONSULTA ?: '',
                                    'neutropenia' => $item->NEUTROPENIA ?: '',
                                    'preMedicalDischarge' => $item->PREALTA ?: '',
                                    'dxSecondaryCode' => $item->COD_DX_SECUNDARIO ?: '',
                                    'dxSecondaryName' => $item->NOMBRE_DX_SECUNDARIO ?: '',
                                    'consultationReason' => $cr->DESCRIPCION_EVOLUCION ?: '',
                                    'medDiagnostics' => $item->DX_MEDICO ?: '',
                                    'treatment' => $item->TRATAMIENTOS ?: '',
                                    'previousStudies' => $item->ESTUDIOS_PREVIOS ?: '',
                                    'pendingAndRecommendations' => $item->ANALISIS ?: '',
                                    'lastEvoDoctorCode' => $item->COD_MED_ULT_EVO ?: '',
                                    'lastEvoDoctorName' => trim($item->NOM_MED_ULT_EVO),
                                    'tVariable' => $item->VARIABLE_T ?: '',
                                    'nVariable' => $item->VARIABLE_N ?: '',
                                    'mVariable' => $item->VARIABLE_M ?: '',
                                    'background' => $antecedentes,
                                    'risks' => []
                                );

                                $consul_reason[] = $temp2;
                            }
                        } else {

                            $consul_reason = [];

                            if ($item->NEUTROPENIA != null) {
                                $item->NEUTROPENIA = 1;
                            } else {
                                $item->NEUTROPENIA = 0;
                            }

                            if ($item->PREALTA != null) {
                                $item->PREALTA = 1;
                            } else {
                                $item->PREALTA = 0;
                            }

                            $tempConsulReason = array(
                                'lastInterConsulDoctorDoc' => $item->DOCUMENTO_ESPECIALISTA_INTERCONSULTA ?: '',
                                'lastInterConsulDoctor' => $item->ULTIMO_ESPECIALISTA_INTERCONSULTA ?: '',
                                'lastInterConsulSpeciality' => $item->ESPECIALIDAD_ULTIMA_INTERCONSULTA ?: '',
                                'neutropenia' => $item->NEUTROPENIA ?: '',
                                'preMedicalDischarge' => $item->PREALTA ?: '',
                                'dxSecondaryCode' => $item->COD_DX_SECUNDARIO ?: '',
                                'dxSecondaryName' => $item->NOMBRE_DX_SECUNDARIO ?: '',
                                'consultationReason' => "",
                                'medDiagnostics' => $item->DX_MEDICO ?: '',
                                'treatment' => $item->TRATAMIENTOS ?: '',
                                'previousStudies' => $item->ESTUDIOS_PREVIOS ?: '',
                                'pendingAndRecommendations' => $item->ANALISIS ?: '',
                                'lastEvoDoctorCode' => $item->COD_MED_ULT_EVO ?: '',
                                'lastEvoDoctorName' => trim($item->NOM_MED_ULT_EVO),
                                'tVariable' => $item->VARIABLE_T ?: '',
                                'nVariable' => $item->VARIABLE_N ?: '',
                                'mVariable' => $item->VARIABLE_M ?: '',
                                'background' => $antecedentes,
                                'risks' => $riesgos
                            );

                            $consul_reason[] = $tempConsulReason;
                        }


                        $temp = array(
                            'document' => $item->NUM_HISTORIA,
                            'tipDoc' => $item->TI_DOC,
                            'admConsecutive' => $item->INGRESO,
                            'admDate' => $item->FECHA_INGRESO,
                            //'folio' => $item->FOLIO,
                            'fName' => $item->PRIMER_NOMBRE,
                            'sName' => $item->SEGUNDO_NOMBRE,
                            'fLastname' => $item->PRIMER_APELLIDO,
                            'sLastname' => $item->SEGUNDO_APELLIDO,
                            'birthDate' => $item->FECHA_NAC,
                            'age' => $item->EDAD,
                            'gender' => $item->SEXO,
                            'civilStatus' => $item->ESTADOCIVIL,
                            'patientCompany' => $item->EMPRESA,
                            'patientContract' => $item->CONTRATO,
                            'primaryDxCode' => $item->DX_COD,
                            'primaryDxDescription' => $item->DX,
                            'primaryDxDate' => $item->FECHA_1_DX,
                            'date' => $dt,
                            'consumption' => 0,
                            'realStay' => $item->EstanciaReal,
                            'clinicHistorial' => $consul_reason,
                        );

                        $patient_info[] = $temp;
                    }

                    if (count($patient_info) > 0) {

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'data' => $patient_info,
                                'status' => 200
                            ], 200);
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty Patient Info Array',
                                'data' => [],
                                'status' => 200
                            ], 200);
                    }
                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty Patient Info Query Request',
                            'data' => [],
                            'status' => 200
                        ], 200);
                }
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/active-nurses",
     *     operationId="ActNurses",
     *     tags={"Hito"},
     *     summary="Get Nurses Database",
     *     description="Returns Nurses Database",
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
    public function getNursesList(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $query_nurses = DB::connection('sqlsrv_kactusprod')
                ->select('SELECT * FROM EMPLEADOS_ENF()');

            if (count($query_nurses) > 0) {

                $nurses = [];

                foreach ($query_nurses as $item) {

                    $temp = array(
                        'docType' => $item->TIP_DOC,
                        'document' => $item->DOC,
                        'name' => $item->Nombre,
                        'lastName' => $item->Apellidos,
                        'gender' => $item->sexo,
                        'email' => $item->Email,
                        'phone' => $item->Telefono,
                        'address' => $item->Direccion,
                        'birthDate' => $item->Fecha_Nacimiento,
                        'immediateBoss' => $item->JEFE_INMEDIATO,
                        'positionCode' => $item->CARGO_COD,
                        'position' => $item->Cargo,
                        'costCenterCod' => $item->CENTRO_COSTO_COD,
                        'costCenter' => $item->CENTRO_COSTO
                    );

                    $nurses[] = $temp;
                }

                if (count($nurses) < 0) {
                    $nurses = [];
                }

                return response()
                    ->json([
                        'msg' => 'Ok',
                        'status' => 200,
                        'data' => $nurses
                    ]);
            } else {

                return response()
                    ->json([
                        'msg' => 'Ok',
                        'status' => 204,
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/active-doctors",
     *     operationId="ActDoctors",
     *     tags={"Hito"},
     *     summary="Get Doctors Database",
     *     description="Returns Doctors Database",
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
    public function getDoctorsList(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $query_doctors = DB::connection('sqlsrv_kactusprod')
                ->select('SELECT * FROM EMPLEADOS_MED()');

            if (count($query_doctors) > 0) {

                $doctors = [];

                foreach ($query_doctors as $item) {

                    $temp = array(
                        'docType' => $item->TIP_DOC,
                        'document' => $item->DOC,
                        'name' => $item->Nombre,
                        'lastName' => $item->Apellidos,
                        'gender' => $item->sexo,
                        'email' => $item->Email,
                        'phone' => $item->Telefono,
                        'address' => $item->Direccion,
                        'birthDate' => $item->Fecha_Nacimiento,
                        'immediateBoss' => $item->JEFE_INMEDIATO,
                        'positionCode' => $item->CARGO_COD,
                        'position' => $item->Cargo,
                        'costCenterCod' => $item->CENTRO_COSTO_COD,
                        'costCenter' => $item->CENTRO_COSTO
                    );

                    $doctors[] = $temp;
                }

                if (count($doctors) < 0) {
                    $doctors = [];
                }

                return response()
                    ->json([
                        'msg' => 'Ok',
                        'status' => 200,
                        'data' => $doctors
                    ]);
            } else {

                return response()
                    ->json([
                        'msg' => 'Ok',
                        'status' => 204,
                    ]);
            }
        }
    }


    // ============================================================

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/audit-get-patient-procedures/{patientdoc?}/{patientdoctype?}",
     *     operationId="getPatientInfoForAudit",
     *     tags={"Hito"},
     *     summary="Get Patient Info For Audit",
     *     description="Returns Patient Info for Audit",
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
    public function getOrderedProceduresByPatientDoc(Request $request, $patientDoc = '', $patientDocType = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if (!$patientDoc || !$patientDocType) {
                    return response()
                        ->json([
                            'msg' => 'Parameters Cannot be Empty',
                            'status' => 400,
                        ], 400);
                } else {

                    $query_procedures = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HITO_AUDITORIA_PROCEDIMIENTOS_ANTERIORES('$patientDoc', '$patientDocType')");

                    if (count($query_procedures) > 0) {

                        $procedures = [];

                        foreach (json_decode(json_encode($query_procedures), true) as $item) {

                            if (!isset($procedures[$item['NUM_DOC']])) {
                                $procedures[$item['NUM_DOC']] = array(
                                    'patientFirstName' => $item['NOMBRE1'],
                                    'patientSecondName' => $item['NOMBRE2'],
                                    'patientLastName' => $item['APELLIDO1'],
                                    'patientSecondLastName' => $item['APELLIDO2'],
                                    'patientDoc' => $item['NUM_DOC'],
                                    'patientDocType' => $item['TIP_DOC'],
                                    'patientBirthDate' => $item['FECHA_NAC'],
                                    'patientGender' => $item['SEXO'],
                                    'patientBloodType' => $item['GRUPO_SANGUINEO'],
                                    'patientCity' => $item['MUNICIPIO'],
                                    'patientState' => $item['DEPARTAMENTO'],
                                    'patientAdmConsecutive' => $item['INGRESO'],
                                    'patientAdmDate' => Carbon::parse($item['FECHA_INGRESO'])->format('Y-m-d H:i:s'),
                                    'patientEpsCode' => $item['EPS'],
                                    'patientEpsName' => $item['EPS_NOM'],
                                    'patientContract' => $item['CONTRATO'],

                                );
                                unset(
                                    $procedures[$item['NUM_DOC']]['PROCEDIMIENTO_COD'],
                                    $procedures[$item['NUM_DOC']]['NOMB_PROCED'],
                                    $procedures[$item['NUM_DOC']]['FECHA_PROGRAMACION'],
                                    $procedures[$item['NUM_DOC']]['FECHA_PROCEDIMIENTO'],
                                    $procedures[$item['NUM_DOC']]['DIFERENCIA_FP_FPROC'],
                                    $procedures[$item['NUM_DOC']]['QX_ESTADO'],
                                    $procedures[$item['NUM_DOC']]['MOTIVO_CANCELACION'],
                                    $procedures[$item['NUM_DOC']]['OBS_CANCELACION'],
                                );
                                $procedures[$item['NUM_DOC']]['procedures'] = [];
                            }

                            $procedures[$item['NUM_DOC']]['procedures'][] = array(
                                'procedureCode' => $item['PROCEDIMIENTO_COD'],
                                'procedureName' => $item['NOMB_PROCED'],
                                'procedureScheduleDate' => $item['FECHA_PROGRAMACION'],
                                'procedureExecutionDate' => $item['FECHA_PROCEDIMIENTO'],
                                'procedureDifDate' => $item['DIFERENCIA_FP_FPROC'],
                                'procedureState' => $item['QX_ESTADO'],
                                'procedureCancelReason' => $item['MOTIVO_CANCELACION'],
                                'procedureCancelObs' => $item['OBS_CANCELACION'],
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
                    } else {
                        return response()
                            ->json([
                                'msg' => 'Procedures Query is Empty',
                                'status' => 204,
                                'data' => []
                            ]);
                    }
                }
            }
        }
    }


    // ============================================================
    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/audit-patient-admision-history/{patientdoc?}/{patientdoctype?}",
     *     operationId="getAuditPatientAdmisionHistory",
     *     tags={"Hito"},
     *     summary="Get Patient Admision History",
     *     description="Returns Patient Admision History",
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
    public function getPatientAdmisionHistory(Request $request, $patientDoc = '', $patientDocType = '')
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if (!$patientDoc || !$patientDocType) {
                    return response()
                        ->json([
                            'msg' => 'Parameters Cannot be Empty',
                            'status' => 400,
                        ], 400);
                } else {

                    $query_admisions = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HITO_AUDITORIA_UBICACION_PACIENTES('$patientDoc', '$patientDocType') ORDER BY FECHA_INGRESO DESC");

                    if (count($query_admisions) > 0) {

                        $admisions = [];

                        foreach (json_decode(json_encode($query_admisions), true) as $item) {

                            if (!isset($admisions[$item['DOCUMENTO']])) {
                                $admisions[$item['DOCUMENTO']] = array(
                                    'patientFirstName' => $item['NOMBRE_1'],
                                    'patientSecondName' => $item['NOMBRE_2'],
                                    'patientLastName' => $item['APELLIDO_1'],
                                    'patientSecondLastName' => $item['APELLIDO_2'],
                                    'patientDoc' => $item['DOCUMENTO'],
                                    'patientBirthDate' => $item['FECHA_NACIMIENTO'],
                                    'patientGender' => $item['SEXO'],
                                    'patientAge' => $item['EDAD'],
                                    'patientBloodType' => $item['GRUPO_SANGUINEO'],
                                );
                                unset(
                                    $admisions[$item['DOCUMENTO']]['PABELLON'],
                                    $admisions[$item['DOCUMENTO']]['CAMA'],
                                    $admisions[$item['DOCUMENTO']]['INGRESO'],
                                    $admisions[$item['DOCUMENTO']]['FECHA_INGRESO'],
                                    $admisions[$item['DOCUMENTO']]['FECHA_EGRESO'],
                                    $admisions[$item['DOCUMENTO']]['DX_CODE'],
                                    $admisions[$item['DOCUMENTO']]['DX_NOMBRE'],
                                    $admisions[$item['DOCUMENTO']]['TIPO_ATENCION_ACTUAL_DESC'],
                                    $admisions[$item['DOCUMENTO']]['ESTANCIA'],

                                );
                                $admisions[$item['DOCUMENTO']]['admisions'] = [];
                            }

                            $admisions[$item['DOCUMENTO']]['admisions'][] = array(
                                'patientEpsCode' => $item['EPS_NIT'],
                                'patientEpsName' => $item['EPS'],
                                'patientContract' => $item['CONTRATO'],
                                'patientPavilion' => $item['PABELLON'],
                                'patientHabitation' => $item['CAMA'],
                                'patientAdmConsecutive' => $item['INGRESO'],
                                'patientDxCode' => $item['DX_CODE'] != null ? $item['DX_CODE'] : '',
                                'patientDxDescription' => $item['DX_NOMBRE'] != null ? $item['DX_NOMBRE'] : '',
                                'patientAdmDate' => Carbon::parse($item['FECHA_INGRESO'])->format('Y-m-d H:i:s'),
                                'patientOutDate' => Carbon::parse($item['FECHA_EGRESO'])->format('Y-m-d H:i:s'),
                                'patientStay' => $item['ESTANCIA']
                            );
                        }
                        if (count($admisions) > 0) {

                            $admisions = array_values($admisions);

                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'data' => $admisions
                                ], 200);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Admisions Array is Empty',
                                    'status' => 204,
                                    'data' => []
                                ], 204);
                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Admisions Query is Empty',
                                'status' => 204,
                                'data' => []
                            ]);
                    }
                }
            }
        }
    }


    // ============================================================
    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/active-doctors-to-audit",
     *     operationId="ActDoctorsToAudit",
     *     tags={"Hito"},
     *     summary="Get Doctors Database for Audit",
     *     description="Returns Doctors Database for Audit",
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
    public function getDoctorsListForAudit(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $query_doctors = DB::connection('sqlsrv_kactusprod')
                        ->select('SELECT * FROM EMPLEADOS_MED()');

                    if (count($query_doctors) > 0) {

                        $doctors = [];

                        foreach ($query_doctors as $item) {

                            $temp = array(
                                'docType' => $item->TIP_DOC,
                                'document' => $item->DOC,
                                'name' => $item->Nombre,
                                'lastName' => $item->Apellidos,
                                'gender' => $item->sexo,
                                'email' => $item->Email,
                                'phone' => $item->Telefono,
                                'address' => $item->Direccion,
                                'birthDate' => $item->Fecha_Nacimiento,
                                'immediateBoss' => $item->JEFE_INMEDIATO,
                                'positionCode' => $item->CARGO_COD,
                                'position' => $item->Cargo,
                                'costCenterCod' => $item->CENTRO_COSTO_COD,
                                'costCenter' => $item->CENTRO_COSTO
                            );

                            $doctors[] = $temp;
                        }

                        if (count($doctors) < 0) {
                            $doctors = [];
                        }

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'status' => 200,
                                'data' => $doctors
                            ]);
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'status' => 204,
                            ]);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {
                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 403
                    ]);
            }
        }
    }


    // ============================================================
    // FUNCTION TO TURN DELIVERY
    //public function getPatientInfoForTurnDelivery(Request $request, $patientDoc = '', $patientDocType = '')
    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/patient-info/turn-delivery/{bedCode?}",
     *     operationId="getPatientInfoForTurnDelivery",
     *     tags={"Hito"},
     *     summary="getPatientInfoForTurnDelivery",
     *     description="Returns getPatientInfoForTurnDelivery",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="bedCode?",
     *          description="Código de la cama - Obligatory",
     *          in="path",
     *          required=true,
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
    public function getPatientInfoForTurnDelivery(Request $request, $bedCode = '')
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

            if (!$bedCode) return response()->json([
                'msg' => 'Parameter BedCode Cannot Be Empty',
                'status' => 400
            ]);

            // QUERY TO GET ALL PATIENT BY PAVILION CODE
            $query = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HITO_BUSQUEDA_PACIENTES_POR_PABELLON('$bedCode')");

            if (sizeof($query) < 0 || !$query[0]->NOMBRE_COMPLETO) return response()->json([
                'msg' => 'Empty Main Query',
                'status' => 204
            ]);

            $records = [];

            foreach ($query as $record) {

                $query_antecedentes = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT TOP 5 * FROM HITO_ANTECEDENTES('$record->NUM_HISTORIA', '$record->TI_DOC')");

                if (sizeof($query_antecedentes) < 0) return response()->json([
                    'msg' => 'Empty Background Query',
                    'status' => 204
                ]);

                $antecedentes = [];

                foreach ($query_antecedentes as $antecedente) {

                    $antecedentes[] = array(
                        'folio' => $antecedente->FOLIO ?: '',
                        'backDate' => $antecedente->FECHA ?: '',
                        'backGroup' => $antecedente->GRUPO_ANTECEDENTE ?: '',
                        'backSubGroup' => $antecedente->SUBGRUPO_ANTECEDENTE ?: '',
                        'backDesc' => $antecedente->ANTECEDENTES ?: '',
                    );
                }

                if (sizeof($antecedentes) < 0) $antecedentes = [];


                $query_riesgos = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM HITO_RIESGOS_PACIENTE('$record->NUM_HISTORIA', '$record->TI_DOC', '$record->FOLIO_FORMATO')");

                // VALIDACIÓN PARA LOS RIESGOS DEL PACIENTE
                if (sizeof($query_riesgos) < 0) return response()->json([
                    'msg' => 'Empty Risks Query',
                    'status' => 204
                ]);

                $riesgos = [];

                foreach ($query_riesgos as $riesgo) {

                    $riesgos[] = array(
                        /* 'Hipotensión_por_uso_de_vasodilatador' => $riesgo->UCI_HIPOTENSION_VASODILATADOR === 1 ? 1  : 0,
                        'Arritmias_asociadas_a_uso_de_vasopresores' => $riesgo->UCI_ARRITMIA_VASOPRESORES === 1 ? 1  : 0,
                        'Evento_Cerebrovascular_asociado_a_uso_de_vasopresor' => $riesgo->UCI_CEREBROVASCULAR_VASOPRESORES === 1 ? 1  : 0,
                        'Desarrollo_de_delirium_en_paciente_critico' => $riesgo->UCI_DELIRIUM_PACIENTE === 1 ? 1  : 0,
                        'Síndrome_de_desacondicionamiento_físico_en_paciente_critico' => $riesgo->UCI_DESACONDICIONAMIENTO_FISICO === 1 ? 1  : 0,
                        'Fistula_traqueoesofagica_por_entubación_orotraqueal_prolongada' => $riesgo->UCI_FISTULA_ORATRAQUEAL === 1 ? 1  : 0,
                        'Estenosis_subglotica_por_entubación_orotraqueal_prolongada' => $riesgo->UCI_ESTENOSIS_X_ENTUBACION === 1 ? 1  : 0,
                        'Inestabilidad_hemodinámica_durante_hemodiálisis' => $riesgo->UCI_INESTABILIDAD_HEMODINAMICA === 1 ? 1  : 0,
                        'Neumotórax_por_inserción_de_catéter_venoso_central' => $riesgo->UCI_NEUMOTORAX_CATETER_CENTRAL === 1 ? 1  : 0,
                        'Lesión_pulmonar_asociada_a_ventilación_mecánica' => $riesgo->UCI_LESION_PULMONAR === 1 ? 1  : 0,
                        'Lesion_isquemica_distal_por_vasopresores_en_pacientes_con_sepsis_en_la_UCI_Adultos' => $riesgo->UCI_LESION_ISQUEMICA === 1 ? 1  : 0,
                        'Neutropenia_febril_en_pacientes_con_quimioterapia' => $riesgo->UCI_NEUTROPENIA_FEBRIL_QUIMIOTERAPIAS === 1 ? 1  : 0,
                        'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->UCI_MUCOSITIS_QUIMIOTERAPIA === 1 ? 1  : 0,
                        'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->UCI_HEMORRAGIA_ANTICOAGULADO === 1 ? 1  : 0,
                        'Sangrado_segundario_a_trombocitopenia_severa' => $riesgo->UCIPED_SANGRADO_TROMBOCITOPENIA === 1 ? 1  : 0,
                        'Hipotensión_por_desequilibrio_electrolítico_en_dengue_grave' => $riesgo->UCIPED_HIPOTENSION_ELECTROLITICO === 1 ? 1  : 0,
                        'Shock_hipovolémico_por_insuficiencia__renal_aguda_en_pediatría' => $riesgo->UCIPED_SHOCK_HIPOVOLEMICO === 1 ? 1  : 0,
                        'Falla_respiratoria_por_neumonía' => $riesgo->UCIPED_FALLA_RESPIRATORIA === 1 ? 1  : 0,
                        'Sepsis_severa_en_aplasia_medular' => $riesgo->UCIPED_SEPSIS_APLASIA_MEDULAR === 1 ? 1  : 0,
                        'Síndrome_de_Lisis_tumoral_en_Linfoma_No_Hodgkin' => $riesgo->UCIPED_SINDROME_LINFOMA_NOHODKIN === 1 ? 1  : 0,
                        'Hipervolemia_por_no_restricciones_de_líquido_en_síndrome_nefrítico' => $riesgo->UCIPED_HIPERVOLEMIA === 1 ? 1  : 0,
                        'Edema_pulmonar_por_extravasación_de_liquido_en_síndrome_nefrótico' => $riesgo->UCIPED_EDEMA_PULMONAR === 1 ? 1  : 0,
                        'Crisis_hipertensiva_en_síndrome_nefrítico' => $riesgo->UCIPED_CRISIS_HIPERTENSIVA === 1 ? 1  : 0,
                        'Hematuria_asociada_a_síndrome_nefrítico' => $riesgo->UCIPED_HEMATURIA_NEFRITICO === 1 ? 1  : 0,
                        'Síndrome_Compresivos_en_Linfoma_No_Hodgkin' => $riesgo->UCIPED_SINDROME_COMPRENSI_UCI_PED === 1 ? 1  : 0,
                        'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->UCIPED_MUCOSITIS_QUIMIOTERAPIA_UCI_PED === 1 ? 1  : 0,
                        'Dolor_en_paciente_oncologico' => $riesgo->UCIPED_DOLOR_PACIENTE_ONCOLOGICO === 1 ? 1  : 0,
                        'Neumonia_asociada_a_ventilacion_mecanica_en_UCIP' => $riesgo->UCIPED_NEUMONIA_VENTILACION_MECANICA === 1 ? 1  : 0,
                        'Shock_hipovolémico_secundario_a_sangrado_agudo_en_procedimiento_quirúrgico_programado' => $riesgo->CIR_SHOCK_HIPOVOLEMICO === 1 ? 1  : 0,
                        'Parada_cardiaca_durante_procedimiento_quirúrgico' => $riesgo->CIR_PARADA_CARDIACA === 1 ? 1  : 0,
                        'Dehiscencia_de_herida_quirúrgica' => $riesgo->CIR_DEHISCENCIA_QX === 1 ? 1  : 0,
                        'Seroma_postquirúrgico' => $riesgo->CIR_SEROMA_POSTQX === 1 ? 1  : 0,
                        'Incremento_del_dolor_neuropatico_post_cirugía_oncológica_de_mama' => $riesgo->CIR_INCREMENTO_DOLOR_POSTQX === 1 ? 1  : 0,
                        'Hernias_en_pared_abdominal_postquirúrgicas' => $riesgo->CIR_HERNIAS_ABDOMINAL_POSTQX === 1 ? 1  : 0,
                        'Fistula_postcirugia_abdominal' => $riesgo->CIR_FISTULAQX_ABDOMINAL === 1 ? 1  : 0,
                        'Lesión_de_uréteres_durante_cirugía_de_tumor_retroperitoneal' => $riesgo->CIR_LESION_URETERES === 1 ? 1  : 0,
                        'Lesión_isquémica_por_perfusión_inadecuada_prolongada__durante_cirugía_de_tumor_retroperitoneal' => $riesgo->CIR_LESION_ISQUEMICA_QX_TUMOR === 1 ? 1  : 0,
                        'Neumonía_por_realización_de_cirugía_abdominales_mayores' => $riesgo->CIR_NEUMONIA_ABDOMINALES_MAYORES === 1 ? 1  : 0,
                        'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->CIR_HEMORRAGIA_TROMBOSIS === 1 ? 1  : 0,
                        'Hematoma_por_obstrucción_de_hemovac_en_cirugía_de_mama_con_vaciamiento' => $riesgo->CIR_HEMATOMA_OBSTRUCCION_HEMOVAC === 1 ? 1  : 0,
                        'Íleo_paralítico_en_pos_quirúrgico_de_colon_por_movilización_tardía' => $riesgo->CIR_ILEO_PARALITICO_POSTQX === 1 ? 1  : 0,
                        'Linfaedema_post_cirugía_de_mama_con_vaciamiento_axilar' => $riesgo->CIR_LINFAEDEMA_POSTQX === 1 ? 1  : 0,
                        'Obstrucción_de_sonda_vesical_en_post_quirúrgico_de_prostatectomía_transvesical' => $riesgo->CIR_OBSTRUCCION_SONDA_VESICAL === 1 ? 1  : 0,
                        'Hematórax_o_colección_residual_por_drenaje_inadecuado_de_pleurovac_en_cirugía_de_tórax' => $riesgo->CIR_HEMOTORAX_POR_DRENAJE === 1 ? 1  : 0,
                        'Peritonitis_por_dolor_abdominal' => $riesgo->CIR_PERITONITIS_DOLOR_ABDOMINAL === 1 ? 1  : 0,
                        'Infeccion_de_sitio_quirurgico' => $riesgo->CIR_INFECCION_SITIO_QX === 1 ? 1  : 0, */
                        'Neutropenia_febril_en_pacientes_con_quimioterapia_hospitalaria' => $riesgo->HOSP_NEUTROPENIA_FEBRIL === "1" ? 1  : 0,
                        'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->HOSP_MUCOSITIS === "1" ? 1  : 0,
                        'Progresion_de_las_complicaciones_por_radioterapia' => $riesgo->HOSP_PROGRESION_COMPLICACIONES_RADIOTERAPIA === "1" ? 1  : 0,
                        'Malnutricion_en_paciente_oncologico' => $riesgo->HOSP_MALNUTRICION === "1" ? 1  : 0,
                        'Dolor_en_paciente_oncologico' => $riesgo->HOSP_DOLOR === "1" ? 1 : 0,
                        'Riesgo_de_Suicidio_' => $riesgo->HOSP_SUICIDIO === "1" ? 1  : 0,
                        'Infeccion_de_sitio_quirurgico' => $riesgo->HOSP_INFECCION_SITIO_QX === "1" ? 1  : 0,
                        'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->HOSP_HEMORRAGIA_TROMBOSIS === "1" ? 1  : 0,
                        /* 'Retraso_en_atencion_en_pacientes_con_patologia_oncologica_(Ca_de_mama,_Leucemia_en_pediatria)' => $riesgo->HOSP_RETRASO_ATENCION === "1" ? 1  : 0, */
                        'Retraso_en_atencion_en_pacientes_con_patologia_oncologica' => $riesgo->HOSP_RETRASO_ATENCION === "1" ? 1  : 0,
                        'Hematoma_por_obstrucción_de_hemovac_en_cirugía_de_mama_con_vaciamiento' => $riesgo->HOSP_HEMATOMA_OBSTRUCCION_HEMOVAC === "1" ? 1  : 0,
                        'Íleo_paralítico_en_pos_quirúrgico_de_colon_por_movilización_tardía' => $riesgo->HOSP_ILEO_PARALITICO_POSTQX === "1" ? 1  : 0,
                        'Linfaedema_post_cirugía_de_mama_con_vaciamiento_axilar' => $riesgo->HOSP_LINFAEDEMA_POSTQX === "1" ? 1  : 0,
                        'Obstrucción_de_sonda_vesical_en_post_quirúrgico_de_prostatectomía_transvesical' => $riesgo->HOSP_OBSTRUCCION_SONDA_VESICAL === "1" ? 1  : 0,
                        'Hematórax_o_colección_residual_por_drenaje_inadecuado_de_pleurovac_en_cirugía_de_tórax' => $riesgo->HOSP_HEMOTORAX_POR_DRENAJE === "1" ? 1  : 0,
                        /* 'Peritonitis_por_dolor_abdominal' => $riesgo->URG_PERITONITIS_DOLOR_ABDOMINAL === 1 ? 1  : 0,
                        'Neutropenia_febril_en_pacientes_con_quimioterapia_hospitalaria' => $riesgo->URG_NEUTROPENIA_FEBRIL === 1 ? 1  : 0,
                        'Mucositis_oral_en_pacientes_con_quimioterapia' => $riesgo->URG_MUCOSITIS === 1 ? 1  : 0,
                        'Progresion_de_las_complicaciones_por_radioterapia_' => $riesgo->URG_PROGRESION_COMPLICACIONES_RADIOTERAPIA === 1 ? 1  : 0,
                        'Malnutricion_en_paciente_oncologico' => $riesgo->URG_MALNUTRICION === 1 ? 1  : 0,
                        'Dolor_en_paciente_oncologico' => $riesgo->URG_DOLOR === 1 ? 1  : 0,
                        'Riesgo_de_Suicidio' => $riesgo->URG_SUICIDIO === 1 ? 1  : 0,
                        'Hemorragia_o_trombosis_en_paciente_anticoagulado' => $riesgo->URG_HEMORRAGIA_TROMBOSIS === 1 ? 1  : 0,
                        'Falla_ventilatoria_secundaria_en_paciente_con_derrame_pleural' => $riesgo->URG_FALLA_VENTILATORIA_DERRAME_PLEURAL === 1 ? 1  : 0,
                        'Perforación_intestinal_por_obstruccion_mecanica' => $riesgo->URG_PERFORACION_POR_OBSTRUCCION === 1 ? 1  : 0,
                        'Sangrado_cerebral_secundario_a_trombocitopenia_severa' => $riesgo->URG_SANGRADO_CEREBRAL === 1 ? 1  : 0,
                        'Choque_hipovolemico_por_hemorragia_vaginal_en_cancer_de_cervix' => $riesgo->URG_CHOQUE_HIPOVOLEMICO === 1 ? 1  : 0,
                        'Tromboembolismo_Pulmonar_en_paciente_con_fractura_de_cadera' => $riesgo->URG_TROMBOEMBOLISMO_PULMONAR === 1 ? 1  : 0,
                        'Lesion_uretral_en_paciente_con_trombocitopenia_post_colocacion_de_sonda_vesical' => $riesgo->URG_LESION_URETRAL === 1 ? 1  : 0,
                        'Aplasia_prolongada_secundaria_a_acondicionamiento_en_TAMO' => $riesgo->TAMO_APLASIA_PROLONGADA === 1 ? 1  : 0,
                        'Síndrome_de_la_pega' => $riesgo->TAMO_SINDROME_PEGA === 1 ? 1  : 0,
                        'Mucositis_severa_secundaria_a_condicionamiento_en_TAMO' => $riesgo->TAMO_MUCOSITIS === 1 ? 1  : 0,
                        'Falla_en_la_colecta_de_CD34_por_movilización_deficiente' => $riesgo->TAMO_FALLA_COLECTA_CD34 === 1 ? 1  : 0,
                        'Desarrollo_de_delirium_en_paciente_critico' => $riesgo->TAMO_DELIRIUM_PACIENTE_CRITICO === 1 ? 1  : 0,
                        'Síndrome_de_desacondicionamiento_físico_en_paciente_critico' => $riesgo->TAMO_SINDROME_DESACONDICIONAMIENTO === 1 ? 1  : 0,
                        'Sangrado_cerebral_secundario_a_trombocitopenia_severa' => $riesgo->TAMO_SANGRADO_CEREBRAL === 1 ? 1  : 0,
                        'Falla_respiratoria_por_neumonía' => $riesgo->TAMO_FALLA_RESPIRATORIA_NEUMONIA === 1 ? 1  : 0,
                        'Sepsis_severa_en_aplasia_medular' => $riesgo->TAMO_SEPSIS_APLASIA_MEDULAR === 1 ? 1  : 0,
                        'Edema_agudo_de_pulmon_por_sobrecarga_de_volumen' => $riesgo->TAMO_EDEMA_AGUDO_PULMON === 1 ? 1  : 0,
                        'Reaccion_alergica_a_acondicionamiento_con_melfalan' => $riesgo->TAMO_REACCION_ALERGICA === 1 ? 1  : 0,
                        'Deshidratación_por_diarrea_y/o_vomito_postquimioterapia_con_alquilantes' => $riesgo->TAMO_DESHIDRATACION_DIARREA === 1 ? 1  : 0, */

                    );
                }

                if (sizeof($riesgos) < 0) $riesgos = [];


                $records[] = [

                    'PatTDoc' => trim($record->TI_DOC),
                    'PatDoc' => trim($record->NUM_HISTORIA),
                    'PatBDate' => $record->FECHA_NAC,
                    'PatAdmDate' => $record->FECHA_INGRESO,
                    'PatAge' => $record->EDAD,
                    'PatCompany' => trim($record->CONTRATO),
                    'PatPavilion' => trim($record->PABELLON),
                    'PatHabitation' => trim($record->CAMA),
                    'nameComplete' => trim($record->NOMBRE_COMPLETO),
                    'medDiagnostics' => $record->ANALISIS,
                    'treatment' => $record->TRATAMIENTOS,
                    'pendingAndRecommendations' => $record->DX_MEDICO,
                    'risks' => $riesgos,
                    'background' => $antecedentes

                ];
            }

            if (sizeof($records) < 0) return response()->json([
                'msg' => 'Empty Bed Array',
                'status' => 204,
                'data' => []
            ]);

            return response()->json([
                'msg' => 'Bed',
                'status' => 200,
                'data' => $records
            ]);

            //
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // ============================================================
    // FUNCTION TO RETURN BEDS BY PAV CODE
    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/pavilion-beds/turn-delivery/{pavCode?}",
     *     operationId="getBedsOfPavilionByPavName",
     *     tags={"Hito"},
     *     summary="getBedsOfPavilionByPavName",
     *     description="Returns getBedsOfPavilionByPavName",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="pavCode?",
     *          description="Código del Pabellón - Obligatory",
     *          in="path",
     *          required=true,
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
    public function getBedsOfPavilionByPavName(Request $request, $pavCode = '')
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

            if (!$pavCode) return response()->json([
                'msg' => 'Parameter pavCode Cannot Be Empty',
                'status' => 400
            ]);

            //

            $query = DB::connection('sqlsrv_hosvital')
                ->select("  SELECT  RTRIM(MAEPAB.MPNomP) AS PABELLON,
                                RTRIM(MAEPAB.MPCodP)  COD_PAB,
                                RTRIM(MAEPAB1.MPNumC) CAMA,
                                CASE MPDisp WHEN 1 THEN 'OCUPADA'
                                            WHEN 0 THEN 'LIBRE'
                                            WHEN 8 THEN 'MANTENIMIENTO'
                                            WHEN 9 THEN 'DESINFECCION'
                                END  ESTADO,
                                MPUced NUM_HISTORIA,
                                CAPBAS.MPTDoc TI_DOC,
                                RTRIM(CAPBAS.MPNomC) NOMBRE_COMPLETO
                        FROM    MAEPAB1	INNER JOIN	MAEPAB ON	MAEPAB.MPCodP = MAEPAB1.MPCodP
											    AND MAEPAB.MPActPab <> 'S'
					                    LEFT JOIN CAPBAS ON		CAPBAS.MPCedu = MPUced
											    AND CAPBAS.MPTDoc = MAEPAB1.MPUDoc
                        WHERE   MAEPAB1.MPCodP NOT IN (1, 2, 24, 29, 38)    AND MAEPAB1.MPNumC not in( '', 'OBSRE','ORTOP','PQQX','REA2A','REA2B','REANI','SILL1','SILL2','SILL3',
																			 'SILL4','SILL5','SILL6','SILLA','SILLB','SILLC','SILLD',
																			  'SILLE','SILLF','SILLG','VIP-A','VIP-B','VIP-C')
                                                                            AND MPActCam = 'N'
                                                                            AND MAEPAB.MPNomP = '$pavCode'
                        ORDER BY MAEPAB1.MPNumC ASC
            ");

            if (sizeof($query) < 0) return response()->json([
                'msg' => 'Empty Main Query',
                'status' => 204
            ]);

            $records = [];

            foreach ($query as $record) {

                $records[] = [
                    'pavName' => $record->PABELLON,
                    'pavCode' => $record->COD_PAB,
                    'bedStatus' => $record->ESTADO,
                    'PatTDoc' => trim($record->TI_DOC),
                    'PatDoc' => trim($record->NUM_HISTORIA),
                    'PatHabitation' => trim($record->CAMA),
                    'nameComplete' => trim($record->NOMBRE_COMPLETO)
                ];
            }

            if (sizeof($records) < 0) return response()->json([
                'msg' => 'Empty Beds Array',
                'status' => 204,
                'data' => []
            ]);

            return response()->json([
                'msg' => 'Beds',
                'status' => 200,
                'data' => $records
            ]);


            //
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
