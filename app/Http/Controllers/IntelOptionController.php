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
     *     path="/api/v1/hs/populations/age/{age}/date/{init?}/{end?}",
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
    public function getDataUsersPopulation(Request $request, $age)
    {
        if ($request->hasHeader('X-Authorization')) {

            if ($age)
            {
                $initdate = \request('init', \Carbon\Carbon::create('1900-01-01 00:00:00')->format('Ymd H:i:s'));
                $enddate = \request('end', \Carbon\Carbon::now()->format('Ymd H:i:s'));

                if ($age >= 50)
                {
                    $query = DB::connection('sqlsrv_hosvital')
                        ->select(
                            DB::raw(
                                "SELECT * FROM DATOS_DEMOGRAFICOS('$age')
                                        WHERE	(   FECHA_FORMATO >= '$initdate' AND
                                                    FECHA_FORMATO <= '$enddate')"
                            )
                        );

                    if (count($query ) > 0)
                    {

                        $records = [];

                        foreach ($query as $item)
                        {

                            $temp = array(
                                'tipDoc' => $item->TIP_DOC,
                                'documento' => $item->DOCUMENTO,
                                'pNombre' => $item->PRIMER_NOMBRE,
                                'sNombre' => $item->SEGUNDO_NOMBRE,
                                'pApellido' => $item->PRIMER_APELLIDO,
                                'sApellido' => $item->SEGUNDO_APELLIDO,
                                'sexo' => $item->SEXO,
                                'fNacimiento' => $item->FECHA_NACIMIFECHA_NACIMI,
                                'empresa' => $item->EMPRESA,
                                'regimen' => $item->REGIMEN,
                                'categoria' => $item->CATEGORIA,
                                'telefono1' => $item->TELEFONO1,
                                'telefono2' => $item->TELEFONO2,
                                'telefono3' => $item->TELEFONO3,
                                'fechaCitaMedExp' => $item->FECHA_CITA_MEDICO_EXPERTO,
                                'fechaPsa' => $item->FECHA_PSA,
                                'valPsa' => $item->VALOR_PSA,
                                'fechaTacRec' => $item->FECHA_TACTO_RECTAL,
                                'ValTacRec' => $item->VALOR_TACTO_RECTAL,
                                'fechaEcoPro' => $item->FECHA_ECO_PROSTATA,
                                'valEcoPro' => $item->ECOGRAFIA_PROSTA,
                                'fechaBioPro' => $item->FECHA_BIOPSIA_PROSTATA,
                                'resBioPro' => $item->RESULTADO_BIOPSIA,
                                'fechaInforPac' => $item->FECHA_INFOR_PACIENTE,
                                'clasiRies' => $item->CLASIFICACION_RIESGO,
                                'resGleason' => $item->RESULTADO_GLEASON,
                                'fechaEstTnm' => $item->FECHA_ESTADIFICACION_TNM,
                                'estTnm' => $item->ESTADIFICACION_TNM,
                                'fechaEstCaso' => $item->FECHA_ESTADIFICACION_CASO,
                                'estCaso' => $item->ESTADIFICACION_CASO,
                                'fechaIniTrat' => $item->FECHA_INICIO_TRATAMIENTO,
                                'tipoTrat' => $item->TIPO_TRATAMIENTO,
                                'defConducta' => $item->DEFINICION_CONDUCTA,
                                'fechaCitaUro' => $item->FECHA_CITA_URO_ONCO,
                                'fechaPresTrat' => $item->FECHA_PRES_TRATAMIENTO,
                                'fechaAutTrat' => $item->FECHA_AUTORIZACION_TRATAMIENTO,
                                'fechaRealizaPsaPosTrat' => $item->FECHA_REALIZA_PSA_POS_TRATAMIENTO,
                                'valPsaPosTrat' => $item->VALOR_PSA_POS_TRATAMIENTO,
                                'fechaFosAlc' => $item->FECHA_FOSFATASA_ALCALINA,
                                'resFosAlc' => $item->RESULTADO_FOSFATASA,
                                'fechaGama' => $item->FECHA_GAMAGRAFIA,
                                'resGama' => $item->RESULTADO_GAMAGRAFIA,
                                'fechaTest' => $item->FECHA_TESTOSTERONA,
                                'resTest' => $item->RESULTADO_TESTOSTERONA,
                                'fechaPato' => $item->FECHA_PATOLOGIA,
                                'resPato' => $item->RESULTADO_PATOLOGIA,
                                'fechaBili' => $item->FECHA_BILIRRUBINAS,
                                'resBili' => $item->RESULTADO_BILIRRUBINAS,
                                'fechaTgo' => $item->FECHA_TGO,
                                'resTgo' => $item->RESULTADO_TGO,
                                'fechaTgp' => $item->FECHA_TGP,
                                'resTgp' => $item->RESULTADO_TGP,
                                'fechaLdh' => $item->FECHA_LDH,
                                'resLdh' => $item->RESULTADO_LDH,
                                'fechaCh' => $item->FECHA_CH,
                                'resCh' => $item->RESULTADO_CH,
                                'fechaTomoTorax' => $item->FECHA_TOMOGRAFIA_TORAX,
                                'resTomoTorax' => $item->RESULTADO_TOMO_TORAX,
                                'fechaAst' => $item->FECHA_AST,
                                'resAst' => $item->RESULTADO_AST,
                                'fechaAlt' => $item->FECHA_ALT,
                                'restAlt' => $item->RESULTADO_ALT,
                                'fechaCreatinina' => $item->FECHA_CREATININA,
                                'resCreatinina' => $item->RESULTADO_CREATININA,
                                'desClinRel' => $item->DESLENACES_CLINICOS_RELEVANTES,
                            );

                            $records[] = $temp;
                        }

                        return response()->json([
                            'msg' => 'Ok',
                            'status' => 200,
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
                        'msg' => 'La Edad Debe Ser Mayor o Igual a 50 Años',
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
