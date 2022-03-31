<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntelOptionController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1/hs/populations/age/{age?}/date/{init?}/{end?}",
     *     operationId="getDataUsersPopulation",
     *     tags={"IntelOptions"},
     *     summary="Get Patient With CP Format",
     *     description="Returns CP Format Information",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="age",
     *          description="Edad Tope",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="init?",
     *          description="Fecha Inicio para Búsqueda - Opcional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="end?",
     *          description="Fecha Final para Búsqueda - Opcional",
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
    public function getDataUsersPopulation(Request $request, $age = '40')
    {
        if ($request->hasHeader('X-Authorization')) {

            if ($age) {
                $initdate = \request('init', \Carbon\Carbon::create('1900-01-01 00:00:00')->format('Ymd H:i:s'));
                $enddate = \request('end', \Carbon\Carbon::now()->format('Ymd H:i:s'));

                if ($age >= 40) {
                    $query = DB::connection('sqlsrv_hosvital')
                        ->select(
                            DB::raw(
                                "SELECT * FROM DATOS_DEMOGRAFICOS('$age')
                                        WHERE	(   FECHA_FORMATO >= '$initdate' AND
                                                    FECHA_FORMATO <= '$enddate')"
                            )
                        );

                    if (count($query) > 0) {

                        $records = [];

                        foreach ($query as $item) {

                            if ($item->FECHA_TACTO_RECTAL === '1753-01-01' || $item->FECHA_ECO_PROSTATA === '1753-01-01') {
                                $item->FECHA_TACTO_RECTAL = "";
                                $item->FECHA_ECO_PROSTATA = "";
                            }

                            $temp = array(
                                'tipDoc' => $item->TIP_DOC,
                                'documento' => $item->DOCUMENTO,
                                'pNombre' => $item->PRIMER_NOMBRE,
                                'sNombre' => $item->SEGUNDO_NOMBRE,
                                'pApellido' => $item->PRIMER_APELLIDO,
                                'sApellido' => $item->SEGUNDO_APELLIDO,
                                'sexo' => $item->SEXO,
                                'fNacimiento' => $item->FECHA_NACIMIFECHA_NACIMI,
                                'empresa' => trim($item->EMPRESA),
                                'regimen' => $item->REGIMEN,
                                'categoria' => $item->CATEGORIA,
                                'telefono1' => $item->TELEFONO1,
                                'telefono2' => $item->TELEFONO2,
                                'telefono3' => $item->TELEFONO3,
                                'fechaCitaMedExp' => Carbon::parse($item->FECHA_CITA_MEDICO_EXPERTO)->format('Y-m-d'),
                                'fechaPsa' => Carbon::parse($item->FECHA_PSA)->format('Y-m-d'),
                                'valPsa' => $item->VALOR_PSA,
                                'fechaTacRec' => Carbon::parse($item->FECHA_TACTO_RECTAL)->format('Y-m-d'),
                                'ValTacRec' => $item->VALOR_TACTO_RECTAL,
                                'fechaEcoPro' => Carbon::parse($item->FECHA_ECO_PROSTATA)->format('Y-m-d'),
                                'valEcoPro' => $item->ECOGRAFIA_PROSTA,
                                'fechaBioPro' => Carbon::parse($item->FECHA_BIOPSIA_PROSTATA)->format('Y-m-d'),
                                'resBioPro' => $item->RESULTADO_BIOPSIA,
                                'fechaInforPac' => Carbon::parse($item->FECHA_INFOR_PACIENTE)->format('Y-m-d'),
                                'clasiRies' => $item->CLASIFICACION_RIESGO,
                                'resGleason' => $item->RESULTADO_GLEASON,
                                'fechaEstTnm' => Carbon::parse($item->FECHA_ESTADIFICACION_TNM)->format('Y-m-d'),
                                'estTnm' => $item->ESTADIFICACION_TNM,
                                'fechaEstCaso' => $item->FECHA_ESTADIFICACION_CASO,
                                'estCaso' => $item->ESTADIFICACION_CASO,
                                'fechaIniTrat' => Carbon::parse($item->FECHA_INICIO_TRATAMIENTO)->format('Y-m-d'),
                                'tipoTrat' => $item->TIPO_TRATAMIENTO,
                                'defConducta' => $item->DEFINICION_CONDUCTA,
                                'fechaCitaUro' => Carbon::parse($item->FECHA_CITA_URO_ONCO)->format('Y-m-d'),
                                'fechaPresTrat' => Carbon::parse($item->FECHA_PRES_TRATAMIENTO)->format('Y-m-d'),
                                'fechaAutTrat' => Carbon::parse($item->FECHA_AUTORIZACION_TRATAMIENTO)->format('Y-m-d'),
                                'fechaRealizaPsaPosTrat' => Carbon::parse($item->FECHA_REALIZA_PSA_POS_TRATAMIENTO)->format('Y-m-d'),
                                'valPsaPosTrat' => $item->VALOR_PSA_POS_TRATAMIENTO,
                                'fechaFosAlc' => Carbon::parse($item->FECHA_FOSFATASA_ALCALINA)->format('Y-m-d'),
                                'resFosAlc' => $item->RESULTADO_FOSFATASA,
                                'fechaGama' => Carbon::parse($item->FECHA_GAMAGRAFIA)->format('Y-m-d'),
                                'resGama' => $item->RESULTADO_GAMAGRAFIA,
                                'fechaTest' => Carbon::parse($item->FECHA_TESTOSTERONA)->format('Y-m-d'),
                                'resTest' => $item->RESULTADO_TESTOSTERONA,
                                'fechaPato' => Carbon::parse($item->FECHA_PATOLOGIA)->format('Y-m-d'),
                                'resPato' => $item->RESULTADO_PATOLOGIA,
                                'fechaBili' => Carbon::parse($item->FECHA_BILIRRUBINAS)->format('Y-m-d'),
                                'resBili' => $item->RESULTADO_BILIRRUBINAS,
                                'fechaTgo' => Carbon::parse($item->FECHA_TGO)->format('Y-m-d'),
                                'resTgo' => $item->RESULTADO_TGO,
                                'fechaTgp' => Carbon::parse($item->FECHA_TGP)->format('Y-m-d'),
                                'resTgp' => $item->RESULTADO_TGP,
                                'fechaLdh' => Carbon::parse($item->FECHA_LDH)->format('Y-m-d'),
                                'resLdh' => $item->RESULTADO_LDH,
                                'fechaCh' => Carbon::parse($item->FECHA_CH)->format('Y-m-d'),
                                'resCh' => $item->RESULTADO_CH,
                                'fechaTomoTorax' => Carbon::parse($item->FECHA_TOMOGRAFIA_TORAX)->format('Y-m-d'),
                                'resTomoTorax' => $item->RESULTADO_TOMO_TORAX,
                                'fechaAst' => Carbon::parse($item->FECHA_AST)->format('Y-m-d'),
                                'resAst' => $item->RESULTADO_AST,
                                'fechaAlt' => Carbon::parse($item->FECHA_ALT)->format('Y-m-d'),
                                'restAlt' => $item->RESULTADO_ALT,
                                'fechaCreatinina' => Carbon::parse($item->FECHA_CREATININA)->format('Y-m-d'),
                                'resCreatinina' => $item->RESULTADO_CREATININA,
                                'desClinRel' => $item->DESLENACES_CLINICOS_RELEVANTES,
                            );

                            $records[] = $temp;
                        }

                        return response()->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'count' => count($records),
                            'data' => $records,
                        ], 200);
                    } else {

                        return response()->json([
                            'msg' => 'No Hay Datos en la Respuesta a la Solicitud',
                            'status' => 204,
                        ]);
                    }
                } else {

                    return response()->json([
                        'msg' => 'La Edad Debe Ser Mayor o Igual a 40 Años',
                        'status' => 200
                    ], 200);
                }
            } else {

                return response()->json([
                    'msg' => 'El Parametro Edad No Puede Estar Vacío',
                    'status' => 200
                ], 200);
            }
        } else {

            return response()->json([
                'msg' => 'Acceso No Autorizado',
                'status' => 401
            ], 401);
        }
    }
}
