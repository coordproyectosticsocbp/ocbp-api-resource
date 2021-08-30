<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     *     operationId="occupation",
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
                    ->select("SELECT * FROM TORRES WHERE towerState = 1");

                if (count($query_torres) > 0) {
                    $torres = [];

                    foreach ($query_torres as $tower) {

                        // CONSULTA PARA OBTENER LA RELACIÓN DE TORRE PABELLÓN TENIENDO COMO PARAMETRO EL CÓDIGO DE LA TORRE
                        $query_torres_pavs = DB::connection('sqlsrv')
                            ->select("SELECT * FROM HITO_TOWER_PAVILIONS('$tower->towerCode')");

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
                                            ->select("SELECT * FROM HITO_CENSOREAL('$item->CODIGO_PABELLON')");

                                        if (count($query2) > 0) {

                                            $habs = [];

                                            foreach ($query2 as $cat) {

                                                $temp1 = array(
                                                    'pavCode' => $cat->COD_PAB,
                                                    'pavName' => $cat->PABELLON,
                                                    'habitation' => $cat->CAMA,
                                                    'hab_status' => $cat->ESTADO,
                                                    'patient_doc' => $cat->NUM_HISTORIA,
                                                    'patient_doctype' => $cat->TI_DOC,
                                                    'patient_name' => $cat->NOMBRE_PACIENTE,
                                                    'contract' => $cat->CONTRATO,
                                                    'attention_type' => $cat->TIPO,
                                                    'admission_date' => $cat->FECHA_INGRESO,
                                                    'admission_num' => $cat->INGRESO,
                                                    'age' => $cat->EDAD,
                                                    'gender' => $cat->SEXO,
                                                    'real_stay' => $cat->EstanciaReal,
                                                    'diagnosis' => $cat->DX
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

            } catch
            (\Throwable $e) {
                throw $e;
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/hito/get/patient-info-by-hab-code/{hab?}",
     *     operationId="getPatientInfoByHabCode",
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
     *          description="Fecha Inicio para Búsqueda - Opcional",
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
    public function getPatientInfoByHabCode(Request $request, $habCode = 'ab0100')
    {

        if ($request->hasHeader('X-Authorization'))
        {

            try {

                if ($habCode) {

                    $query = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HITO_PACIENTE_X_HABITACION('$habCode')");

                    if (count($query) > 0)
                    {

                        $records = [];

                        foreach ($query as $item)
                        {

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

                        if(count($records) > 0)
                        {

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

        if ($request->hasHeader('X-Authorization'))
        {
            if ($patientDoc && $patientDoctype)
            {

                $query = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT   MPCedu CEDULA, MPTDoc TIP_DOC, IngCsc INGRESO, IngFecAdm FECHA_INGRESO, IngFecEgr FECHA_EGRESO
                                        FROM INGRESOS
                                        WHERE MPCedu = '$patientDoc' AND MPTDoc = '$patientDoctype'
                                        ORDER BY IngCsc
                                     "
                    );

                if (count($query) > 0) {

                    $records = [];

                    foreach ($query as $item) {

                        $temp = array(
                            'patientDoc' => $item->CEDULA,
                            'patientDoctype' => $item->TIP_DOC,
                            'patAdmConsecutive' => $item->INGRESO,
                            'patAdmDate' => $item->FECHA_INGRESO,
                            'patOutputDate' => $item->FECHA_EGRESO,
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
     *          description="Tipo de Documento - Obligatory - RC - TI - CC - CE - NIT - MS - PA - PE - AS",
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
    public function initialPatientInfo(Request $request, $patientDoc, $patientTipoDoc) {

        if ($request->hasHeader('X-Authorization'))
        {
            if ($patientDoc != "" && $patientTipoDoc != "") {

                $query_patient_info = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM HITO_INFORMACION_HISTORIAL_CLINICO('$patientDoc', '$patientTipoDoc')");

                if (count($query_patient_info) > 0)
                {
                    $patient_info = [];

                    foreach ($query_patient_info as $item) {


                        $query_consul_reason = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM MOTIVOS_CONSULTA('$item->DOCUMENTO', '$item->TIP_DOC')");

                        /*$query_consul_soap = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FORM ");*/



                        if (count($query_consul_reason) > 0) {

                            $consul_reason = [];

                            foreach ($query_consul_reason as $cr) {

                                $temp2 = array(
                                    'folio' => $item->FOLIO,
                                    'diagnostics_cod' => $item->DX_COD,
                                    'diagnostics' => $item->DX,
                                    'currentDisease' => $item->ENFEREMDAD_ACTUAL,
                                    'consultationReason' => $cr->MOTIVO
                                );

                                $consul_reason[] = $temp2;

                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Consul_reason Info Array',
                                    'data' => [],
                                    'status' => 200
                                ], 200);

                        }

                        $temp = array(
                            'tipDoc' => $item->TIP_DOC,
                            'document' => $item->DOCUMENTO,
                            'admConsecutive' => $item->CTVO_INGRESO,
                            'patientCompany' => $item->EMPRESA,
                            'fName' => $item->PRIMER_NOMBRE,
                            'sName' => $item->SEGUNDO_NOMBRE,
                            'fLastname' => $item->PRIMER_APELLIDO,
                            'sLastname' => $item->SEGUNDO_APELLIDO,
                            /*'birthDate' => $item->FECHA_NAC,
                            'age' => $item->EDAD,
                            'gender' => $item->SEXO,
                            'civilStatus' => $item->ESTADOCIVIL,
                            'bloodType' => $item->GRUPO_SANGUINEO,
                            'mobilePhone' => $item->TELEFONO1,
                            'address' => $item->DIRECCION,
                            'state' => $item->DEPARTAMENTO,
                            'city' => $item->MUNICIPIO,
                            'neighborhood' => $item->BARRIO,
                            'occupation' => $item->OCUPACION,
                            'ethnicity' => $item->BARRIO,
                            'educationLevel' => $item->NIVEL_EDUCATIVO,
                            'specialAttention' => $item->ATEN_ESPECIAL,
                            'disability' => $item->DISCAPACIDAD,
                            'populationGroup' => $item->GRUPO_POBLA,*/
                            'clinicHistorial' => $consul_reason
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

}




