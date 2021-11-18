<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class MacnaController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/macna/patient/{patientdoc}/type/{patientdoctype}/information",
     *     operationId="initialPatientInfo",
     *     tags={"Macna"},
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
     *          name="patientdoc",
     *          description="Documento del Paciente",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype",
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
    public function getPatientInfoByDocument(Request $request, $patientCode, $patientDoctype)
    {
        if ($request->hasHeader('X-Authorization')) {

            $deathDateV = '';
            $deathState = 0;

            $query_patient = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM MACNA_INFORMACION_PACIENTE('$patientCode', '$patientDoctype')");

            if (count($query_patient) > 0) {

                $patient = [];
                $deceased = 0;

                foreach ($query_patient as $item) {

                    $query_ingresos = DB::connection('sqlsrv_hosvital')
                        ->select("select * from MACNA_INGRESOS_PACIENTES('$item->DOCUMENTO', '$item->TIP_DOC') ORDER BY INGRESO + 0 ASC");

                    if (count($query_ingresos) > 0) {

                        $ingresos = [];

                        foreach ($query_ingresos as $row) {

                            $query_folios = DB::connection('sqlsrv_hosvital')
                                ->select("select * from MACNA_FOLIOS_PACIENTE('$item->DOCUMENTO', '$item->TIP_DOC', '$row->INGRESO') ORDER BY FOLIO + 0 ASC");

                            if (count($query_folios) > 0) {

                                $folios = [];

                                foreach ($query_folios as $folio) {

                                    $query_formulacion = DB::connection('sqlsrv_hosvital')
                                        ->select("SELECT * FROM MACNA_FORMULACION_PACIENTE('$item->DOCUMENTO', '$item->TIP_DOC', '$folio->FOLIO') ORDER BY FOLIO + 0 ASC");

                                    $query_procedimientos = DB::connection('sqlsrv_hosvital')
                                        ->select("SELECT * FROM MACNA_PROCEDIMIENTOS_QX('$item->DOCUMENTO', '$item->TIP_DOC', $row->INGRESO, '$folio->FOLIO')");

                                    $query_evolution = DB::connection('sqlsrv_hosvital')
                                        ->select("SELECT * FROM MACNA_INFORMACION_EVOLUCION('$item->DOCUMENTO', '$item->TIP_DOC', '$folio->FOLIO')");


                                    if (count($query_formulacion) > 0) {

                                        $formulacion = [];

                                        foreach ($query_formulacion as $med) {

                                            $temp3 = array(
                                                'folio' => $med->FOLIO,
                                                'sumACTCod' => trim($med->COD_ACT),
                                                'sumDesc' => trim($med->MED_DESC),
                                                'dose' => $med->DOSIS,
                                                'measurementUnit' => $med->UNIDAD_MEDIDA,
                                                'quantity' => (int)$med->CANTIDAD,
                                                'admRoute' => $med->VIA_ADMINISTRACION,
                                                'frequency' => $med->FRECUENCIA,
                                                'orderDate' => $med->FECHA_ORDENAMIENTO,
                                            );

                                            $formulacion[] = $temp3;
                                        }
                                    } else {

                                        $formulacion = [];
                                    }

                                    if (count($query_procedimientos) > 0) {

                                        $procedimientos = [];

                                        foreach ($query_procedimientos as $proc) {

                                            $query_cirugias = DB::connection('sqlsrv_hosvital')
                                                ->select("SELECT dbo.MACNA_LISTA_CIRUGIAS_REALIZADAS('$proc->EMPRESA_RESERVA','$proc->SEDE_RESERVA','$proc->CODIGO_PROC','$proc->CODIGO_CIRUGIA') AS CIRUGIA ");

                                            if (count($query_cirugias) > 0) {

                                                $cirugias = [];

                                                foreach ($query_cirugias as $cirugia) {

                                                    $cirugia_explode = explode(',', trim($cirugia->CIRUGIA));
                                                    unset($cirugia_explode[0]);


                                                    foreach ($cirugia_explode as $cir) {

                                                        $cirugia_explode_two = explode('-', trim($cir));

                                                        $temp5 = array(
                                                            'surgeryCode' => $cirugia_explode_two[0],
                                                            'surgeryDesc' => $cirugia_explode_two[1]
                                                        );

                                                        $cirugias[] = $temp5;
                                                    }

                                                    if (count($cirugias) < 0) {

                                                        return response()
                                                            ->json([
                                                                'msg' => 'Empty Surgeries Array',
                                                                'status' => 200,
                                                                'data' => []
                                                            ]);
                                                    }
                                                }
                                            } else {

                                                $cirugias = [];
                                            }

                                            $temp4 = array(
                                                'folio' => $proc->FOLIO,
                                                'proc' => $proc->CODIGO_PROC,
                                                'procDate' => $proc->FECHA_PROCEDIMIENTO,
                                                'procState' => $proc->QX_ESTADO,
                                                'srCode' => $proc->CODIGO_CIRUGIA,
                                                'empRes' => $proc->EMPRESA_RESERVA,
                                                'sedRes' => $proc->SEDE_RESERVA,
                                                'surgeries' => $cirugias,
                                                //'dddd' => $cirugias
                                            );

                                            $procedimientos[] = $temp4;
                                        }

                                        if (count($procedimientos) < 0) {

                                            return response()
                                                ->json([
                                                    'msg' => 'Empty Procedures Array',
                                                    'status' => 200,
                                                    'data' => []
                                                ]);
                                        }
                                    } else {

                                        $procedimientos = [];
                                    }

                                    if (count($query_evolution) > 0) {
                                        $evolutions = [];

                                        foreach ($query_evolution as $evo) {

                                            $tempEvo = array(
                                                'analysis' => $evo->TRATAMIENTOS,
                                                'planAndManagement' => $evo->PENDIENTES,
                                            );

                                            $evolutions[] = $tempEvo;
                                        }

                                        if (count($evolutions) < 0) {

                                            $evolutions = [];
                                        }
                                    } else {

                                        $evolutions = [];
                                    }

                                    $temp = array(
                                        'admConsecutive' => $folio->INGRESO,
                                        'folio' => $folio->FOLIO,
                                        'specialty' => $folio->ESPECIALIDAD_FOLIO,
                                        'specialtyDesc' => $folio->ESPECIALIDAD_FOLIO_DESC,
                                        'ordering' => $formulacion,
                                        'procedures' => $procedimientos,
                                        'evolutions' => $evolutions
                                    );

                                    $folios[] = $temp;
                                }
                            } else {

                                $folios = [];
                            }

                            $temp1 = array(
                                'document' => $row->DOCUMENTO,
                                'docType' => $row->TIPO,
                                'admConsecutive' => $row->INGRESO,
                                'admDate' => $row->FECHA_ING_OCBP,
                                'attentionType' => $row->ATENCION_ACTUAL,
                                //'admPavilion' => $row->PAB_INGRESO_OCBP,
                                'outputStatus' => $row->ESTSAL,
                                'outputDate' => $row->FECHA_SALIDA_MED,
                                'folios' => $folios
                            );

                            $ingresos[] = $temp1;
                        }

                        if (count($ingresos) < 0) {

                            return response()
                                ->json([
                                    'msg' => 'Empty Admisions Array',
                                    'status' => 200,
                                    'data' => []
                                ]);
                        }
                    }

                    if ($item->FECHA_DEFUNCION === '1753-01-01 00:00:00.000') {
                        $deathDateV = "";
                        $deathState = 0;
                    } else {
                        $deathDateV = $item->FECHA_DEFUNCION;
                        $deathState = 1;
                    }

                    $temp2 = array(
                        'docType' => $item->TIP_DOC,
                        'document' => $item->DOCUMENTO,
                        'fName' => $item->PRIMER_NOMBRE,
                        'sName' => $item->SEGUNDO_NOMBRE,
                        'fLastname' => $item->PRIMER_APELLIDO,
                        'sLastname' => $item->SEGUNDO_APELLIDO,
                        'birthDate' => trim($item->FECHA_NAC),
                        'age' => $item->EDAD,
                        'gender' => $item->SEXO,
                        //'patientCompany' => utf8_encode(trim($item->EMPRESA)),
                        'patientCompany' => $item->EMPRESA,
                        'civilStatus' => $item->ESTADOCIVIL,
                        'bloodType' => $item->GRUPO_SANGUINEO,
                        'mobilePhone' => $item->TELEFONO1,
                        'address' => $item->DIRECCION,
                        'state' => $item->DEPARTAMENTO,
                        'city' => $item->MUNICIPIO,
                        'neighborhood' => $item->BARRIO,
                        'occupation' => $item->OCUPACION,
                        'ethnicity' => $item->ETNIA,
                        'educationLevel' => $item->NIVEL_EDUCATIVO,
                        'specialAttention' => $item->ATEN_ESPECIAL,
                        'disability' => $item->DISCAPACIDAD,
                        'populationGroup' => $item->GRUPO_POBLA,
                        //'diagnostics_cod' => $item->DX_COD,
                        //'diagnostics' => $item->DX,
                        //'deceased' => $deceased,
                        'deathDate' => $deathDateV,
                        'deathState' => $deathState,
                        'formatFolio' => $item->FOLIO_FORMATO,
                        'admissions' => $ingresos
                    );

                    $patient[] = $temp2;
                }

                if (count($patient) > 0) {
                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $patient
                        ]);
                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty Patient Array',
                            'status' => 200,
                            'data' => []
                        ]);
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Empty Patient Query Request for year ' . Carbon::now()->year,
                        'status' => 200,
                        'data' => []
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/macna/get/patient/alto-cost-format/{patientdocument?}/type/{patientdoctype?}/folio/{patientfolio?}",
     *     operationId="altCostoFormat",
     *     tags={"Macna"},
     *     summary="Get Patient Alto Costo Format Information",
     *     description="Returns Alto Costo Format Patient Information",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="patientdocument?",
     *          description="Documento del Paciente",
     *          required=false,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype?",
     *          description="Tipo de Documento del Paciente - CC, TI, RC, PE",
     *          required=false,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientfolio?",
     *          description="Folio del Paciente: Num",
     *          required=false,
     *          in="path",
     *          @OA\Schema (
     *              type="number"
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
    public function getAltCostoFormat(Request $request, $docPac = '', $docType = '', $folio = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            if (!$docPac) {
                return response()
                    ->json([
                        'msg' => 'Parameter docPac Cannot be Empty',
                        'status' => 400
                    ], 400);
            } else if (!$docType) {
                return response()
                    ->json([
                        'msg' => 'Parameter docType Cannot be Empty',
                        'status' => 400
                    ], 400);
            } else if (!$folio) {
                return response()
                    ->json([
                        'msg' => 'Parameter folio Cannot be Empty',
                        'status' => 400
                    ], 400);
            }

            $query_formato = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM MACNA_FORMATO_CUENTA_ALTO_COSTO('$docPac', '$docType', '$folio')");


            if (count($query_formato) > 0) {

                $records = [];

                $fechaTempPrueba = '';
                $fechaTempClasiRies = '';

                foreach ($query_formato as $form) {

                    if ($form->FECHA_PRUEBA === '1753-01-01 00:00:00.000') {
                        $fechaTempPrueba = null;
                    } else {
                        $fechaTempPrueba = Carbon::parse($form->FECHA_PRUEBA)->format('Y-m-d');
                    }

                    if ($form->FECHA_CLASIFICACION_RIESGO === '1753-01-01 00:00:00.000') {
                        $fechaTempClasiRies = null;
                    } else {
                        $fechaTempClasiRies = Carbon::parse($form->FECHA_CLASIFICACION_RIESGO)->format('Y-m-d');
                    }

                    $temp = array(
                        'FECHA_DX_CANCER' => $form->FECHA_DX_CANCER ? Carbon::parse($form->FECHA_DX_CANCER)->format('Y-m-d') : "",
                        'FECHA_REMISION' => $form->FECHA_REMISION ? Carbon::parse($form->FECHA_REMISION)->format('Y-m-d') : "",
                        'FECHA_INGRESO' => $form->FECHA_INGRESO ? Carbon::parse($form->FECHA_INGRESO)->format('Y-m-d') : "",
                        'FECHA_RECOLECCION_MUESTRA' => $form->FECHA_RECOLECCION_MUESTRA ? Carbon::parse($form->FECHA_RECOLECCION_MUESTRA)->format('Y-m-d') : "",
                        'FECHA_INFORME' => $form->FECHA_INFORME ? Carbon::parse($form->FECHA_INFORME)->format('Y-m-d') : "",
                        'ESTADIFICACION' => $form->ESTADIFICACION,
                        'FECHA_PRUEBA' => $fechaTempPrueba,
                        'RESULTADO_HER_2' => $form->RESULTADO_HER_2,
                        'ESTADIO_DUKES' => $form->ESTADIO_DUKES,
                        'ESTADIO_LINFOMA' => $form->ESTADIO_LINFOMA,
                        'ESCALA_GLEASON' => $form->ESCALA_GLEASON,
                        'RIESGO_LEUCEMIA' => $form->RIESGO_LEUCEMIA,
                        'CLASIFICACION_RIESGO_ADULTOS' => $form->CLASIFICACION_RIESGO_ADULTOS,
                        'CLASIFICACION_RIESGO_PEDIATRICO' => $form->CLASIFICACION_RIESGO_PEDIATRICO,
                        'FECHA_CLASIFICACION_RIESGO' => $fechaTempClasiRies,
                        'OBSERVACION' => $form->OBSERVACION,
                        'UBICACION_ESQUEMA_QUIMIOTERAPIA' => $form->UBICACION_ESQUEMA_QUIMIOTERAPIA,
                        'UBICACION_ESQUEMA_RADIO_BRAQUITERAPIA' => $form->UBICACION_ESQUEMA_RADIO_BRAQUITERAPIA,
                        'TIPO_RADIO_APLICADA' => $form->TIPO_RADIO_APLICADA,
                        'GRADO_DIFERENCIACION' => $form->GRADO_DIFERENCIACION,
                    );

                    $records[] = $temp;
                }

                if (count($records) < 0) {

                    return response()
                        ->json([
                            'msg' => 'Empty Format Array',
                            'status' => 204,
                            'data' => []
                        ]);
                }

                return response()
                    ->json([
                        'msg' => 'Ok',
                        'status' => 200,
                        'data' => $records
                    ]);
            } else {

                return response()
                    ->json([
                        'msg' => 'Empty Query Format Array',
                        'status' => 204,
                        'data' => []
                    ]);
            }
        }
    }
}
