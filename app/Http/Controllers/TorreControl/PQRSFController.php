<?php

namespace App\Http\Controllers\TorreControl;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;
use function Ramsey\Uuid\v1;

class PQRSFController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/felicitacionesvsquejas",
     *     operationId="getFelicitacionesVsQuejas",
     *     tags={"Indicadores"},
     *     summary="Get getFelicitacionesVsQuejas",
     *     description="Returns getFelicitacionesVsQuejas",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },@OA\Parameter (
     *          name="fechaInicial?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="fechaFinal?",
     *          description="Required",
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
    public function getFelicitacionesVsQuejas(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            // $fechaFinal = '2022-12-12';
            //$fechaInicial = '2022-01-01';
            $fechaInicial=date("Y").'-01-01';
            $fechaActual = date('Y-m-d');
            try {


                $query = DB::connection('pgsql')
                    ->table('issues')
                    ->whereBetween('createdAt', [$fechaInicial, $fechaActual])
                    ->select('type', DB::raw('count(type) as total'), DB::raw("
                (CASE type WHEN 0 THEN 'Peticion'
                when 1 then 'Queja'
                when 2 then 'Reclamo'
                when 3 then 'Sugerencia'
                when 4 then 'Felicitacion'
                END) AS type
                "))
                    ->groupBy('type')
                    ->get();
                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                /*
                foreach ($query as $result) $resultado[] = [
                    'estado' => trim($result->estado),
                    'cantidad' => trim($result->cantidad),
                ];


                if (count($resultado) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);

                */
                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => count($query),
                    'data' => $query
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/porcentaje/{idType?}",
     *     operationId="getPorcentajePQR",
     *     tags={"Indicadores"},
     *     summary="Get getPorcentajePQR",
     *     description="Returns getPorcentajePQR",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },@OA\Parameter (
     *          name="fechaInicial?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="fechaFinal?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="type?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="int"
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
    public function getPorcentajePQR(Request $request, $idType = null)
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            //$fechaFinal= '2022-01-01';
            //$fechaInicial='2022-12-12';
            //$idType='5';
            if ($idType > 4 || $idType == null) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            try {

                $descripcion = "SUGERENCIA";

                if ($idType == 0) {
                    $descripcion = "PETICION";
                } else {
                    if ($idType == 1) {
                        $descripcion = "QUEJA";
                    } else {
                        if ($idType == '2') {
                            $descripcion = "RECLAMO";
                        } else {
                            if ($idType == '3') {
                                $descripcion == "SUGERENCIA";
                            } else {
                                if ($idType == '4') {
                                    $descripcion = "FELICITACIÓN";
                                }
                            }
                        }
                    }
                }
                $fechaInicial=date("Y").'-01-01';
                $fechaActual = date('Y-m-d');
                $fecha="'".$fechaInicial."' AND '".$fechaActual."'";
                //$subquery='(SELECT COUNT(id) FROM issues WHERE "type" =1)';
                $query = DB::connection('pgsql')
                    ->table('issues')
                    ->whereBetween('createdAt', [$fechaInicial, $fechaActual])
                    ->select(
                        DB::raw('count(type) as totales'),
                        DB::raw('(SELECT COUNT(id) as quejas FROM issues WHERE "type" =' . $idType . ' AND "createdAt" between '.$fecha.' )')
                    )->get();

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

               

                foreach ($query as $result){
                    $cantidadPQR=0;
                    $cantidadPQR=$result->quejas;
                    if($result->totales >0){
                        $resultado[] = [
                            'Porcentaje ' => trim(($cantidadPQR / $result->totales) * 100),
                            'Quejas' => $cantidadPQR,
                            'Totales de PQR' => $result->totales,
                            'Type' => $descripcion,
                            'FechaInicial'=>$fechaInicial,
                            'FechaActual'=>$fechaActual,
                        ];
                    }else{
                        $resultado[] = [
                            'Porcentaje ' => '0',
                            'Quejas' => '0',
                            'Totales de PQR' => '0',
                            'Type' => $descripcion,
                            'FechaInicial'=>$fechaInicial,
                            'FechaActual'=>$fechaActual,
                        ];
                    }
                    
                } 

                if (count($resultado) < 0) return response()->json([
                  'msg' => 'Empty Diagnoses Array',
                'status' => 204,
                'data' => []
                ], 204);


                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => count($query),
                    'data' => $resultado
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/tiempopromedio/{idType?}",
     *     operationId="getTiempoPromedio",
     *     tags={"Indicadores"},
     *     summary="Get getTiempoPromedio",
     *     description="Returns getTiempoPromedio",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },@OA\Parameter (
     *          name="fechaInicial?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="fechaFinal?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="type?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="int"
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
    public function getTiempoPromedio(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            //$fechaFinal= '2022-01-01';
            //$fechaInicial='2022-12-12';
            //$idType='5';
            
            $fechaInicial=date("Y").'-01-01';
            $fechaActual = date('Y-m-d');

            try {

                $query = DB::connection('pgsql')
                    ->select('
                    select il.status
                    ,i.id
                    ,(select il2."createdAt" from issues_logs il2 where il2.status=0 and il2."issueId" =i.id) fecha_creacion
                    ,(select il2."createdAt" from issues_logs il2 where il2.status=9 and il2."issueId" =i.id) fecha_cierre
                    from issues i
                    LEFT JOIN issues_logs il ON i.id = il."issueId"
                    where il.status=9 and
                    I."type" =1
                    and i."createdAt" BETWEEN ' . "'" . $fechaInicial . "'" . ' and ' . "'" . $fechaActual . "'" . '
                    order by i.id asc
                    ');

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                $result = [];
                $acu = 0;
                $contador = 0;

                foreach ($query as $result) {
                    $creacion = new DateTime($result->fecha_creacion);
                    $cierre = new DateTime($result->fecha_cierre);
                    $diff = $creacion->diff($cierre);
                    // will output 2 days
                    $acu = $acu + $diff->days;
                    $contador++;
                }
                $resultado = ($acu / $contador);
                $resultadoData = [
                    'Tiempo promedio representado en dias de quejas' => $resultado,
                    'Diferencia de dias por reportes acumulados' => $acu
                ];


                if ($acu < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);


                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => $contador,
                    'data' => $resultadoData
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/oportunidadpqr/{idType?}",
     *     operationId="getOportunidadPQR",
     *     tags={"Indicadores"},
     *     summary="Get getOportunidadPQR",
     *     description="Returns getOportunidadPQR",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="type?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="int"
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
    public function getOportunidadPQR(Request $request, $idType = NULL)
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            //$fechaFinal= '2022-01-01';
            //$fechaInicial='2022-12-12';
            //$idType='5';

            if ($idType > 4 || !is_numeric($idType)) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            try {
                $descripcion = "SUGERENCIA";

                if ($idType == '0') {
                    $descripcion = "PETICION";
                } else {
                    if ($idType == '1') {
                        $descripcion = "QUEJA";
                    } else {
                        if ($idType == '2') {
                            $descripcion = "RECLAMO";
                        } else {
                            if ($idType == '3') {
                                $descripcion == "SUGERENCIA";
                            } else {
                                if ($idType == '4') {
                                    $descripcion = "FELICITACIÓN";
                                }
                            }
                        }
                    }
                }
                $query = DB::connection('pgsql')
                    ->select('
                    select count(il.id ) respondido

                    ,(select count(i.id) reclamos from issues i where i."type" = ' . $idType . ') reportes

                    from issues i

                    LEFT JOIN issues_logs il ON i.id = il."issueId"

                    where il.status=9 and i."type" =' . $idType . '
                    ');

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) $resultado[] = [
                    'Oportunidad' => trim(($result->respondido / $result->reportes) * 100),
                    'Type' => $descripcion,
                    'TotalReportes' => $result->reportes,
                    'Respondidos' => $result->respondido
                ];


                if (count($resultado) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);


                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => count($resultado),
                    'data' => $resultado
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/felicitacionesvsquejasporarea",
     *     operationId="getFelicitacionesVsQuejasPorArea",
     *     tags={"Indicadores"},
     *     summary="Get getFelicitacionesVsQuejasPorArea",
     *     description="Returns getFelicitacionesVsQuejasPorArea",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },@OA\Parameter (
     *          name="fechaInicial?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="fechaFinal?",
     *          description="Required",
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
    public function getFelicitacionesVsQuejasPorArea(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            // $fechaFinal = '2022-12-12';
            //$fechaInicial = '2022-01-01';
            
            $fechaInicial=date("Y").'-01-01';
            $fechaActual = date('Y-m-d');

            try {

                $nameCasosQuery = "
                when 0 then 'Peticion'
                when 1 then 'Queja'
                when 2 then 'Reclamo'
                when 3 then 'Sugerencia'
                when 4 then 'Felicitacion'
                ";

                $query = DB::connection('pgsql')

                    ->select('
                    select a."name" as area,case i."type"
                    ' . $nameCasosQuery . '
                     end tipo,COUNT(i."type") cantidad from issues i
                    LEFT JOIN issue_areas ia
                    ON   i.id = ia."issueId"
                    LEFT JOIN "area" a
                    ON ia."areaId"  = a.id
                    where i."createdAt" between ' . "'" . $fechaInicial . "'" . ' and ' . "'" . $fechaActual . "'" . '
                    GROUP BY i."type",a."name"
                    order by a."name",tipo
                ');
                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                $temp = '';
                $tempinicial = 0;
                $arraytemp = [];

                foreach ($query as $result) {

                    if ($tempinicial == 0) {
                        $temp = $result->area;
                        $tempinicial = 1;
                    }
                    if ($temp == $result->area) {
                        $arraytemp[] = ['tipo' => $result->tipo, 'cantidad' => $result->cantidad];
                    } else {
                        $felicitacion = 0;
                        $peticion = 0;
                        $queja = 0;
                        $peticion = 0;
                        $sugerencia = 0;
                        $reclamo = 0;

                        foreach ($arraytemp as $atm) {


                            if ($atm['tipo'] == 'Felicitacion') {
                                $felicitacion = $atm['cantidad'];
                            }
                            if ($atm['tipo'] == 'Peticion') {
                                $peticion = $atm['cantidad'];
                            }
                            if ($atm['tipo'] == 'Queja') {
                                $queja = $atm['cantidad'];
                            }
                            if ($atm['tipo'] == 'Peticion') {
                                $peticion = $atm['cantidad'];
                            }
                            if ($atm['tipo'] == 'Sugerencia') {
                                $sugerencia = $atm['cantidad'];
                            }
                            if ($atm['tipo'] == 'Reclamo') {
                                $reclamo = $atm['cantidad'];
                            }
                        }
                        $re[] = ['Name' => $temp, 'PQRS' => [
                            ['Tipo' => 'Felicitación', 'Cantidad' => $felicitacion], ['Tipo' => 'Petición', 'Cantidad' => $peticion], ['Tipo' => 'Queja', 'Cantidad' => $queja], ['Tipo' => 'Sugerencia', 'Cantidad' => $sugerencia], ['Tipo' => 'Reclamo', 'Cantidad' => $reclamo]

                        ]];
                        $arraytemp = [];
                        $arraytemp[] = ['tipo' => $result->tipo, 'cantidad' => $result->cantidad];
                    }


                    $temp = $result->area;
                }

                /*

                if (count($resultado) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);

                */
                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => count($query),
                    'data' => $re
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/prioridadcasos",
     *     operationId="getPrioridadCasos",
     *     tags={"Indicadores"},
     *     summary="Get getPrioridadCasos",
     *     description="Returns getPrioridadCasos",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },@OA\Parameter (
     *          name="fechaInicial?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="fechaFinal?",
     *          description="Required",
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

    public function getPrioridadCasos(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            $fechaInicial=date("Y").'-01-01';
            $fechaActual = date('Y-m-d');
            //$idType='5';
            //if (!$fechaInicial || !$fechaFinal) {

             //   return response()
               //     ->json([
            //            'msg' => 'Parameters Cannot Be Empty!',
                   //     'status' => 400
             //       ]);
           // }
            try {
                $concatName = "concat( ip.name, ' ' ,ip.lastname  ) nombres";

                $tipo = "when 0 then 'Petición'
                when 1 then 'Queja'
                when 2 then 'Reclamo'
                when 3 then 'Sugerencia'
                when 4 then 'Felicitación'
                end tipo";

                $case2 = "case id.legal when true then 'SI' else 'NO' end REQUERIMIENTO_DE_JURIDICA_LEGAL
                , case id.risk when true then 'SI' else 'NO' end RIESGO_DE_VIDA
                , case id.relevant when true then 'SI' else 'NO' end PROCEDENTE_NO_PROCEDENTE";

                $ultimocase = "case i.management_type
                when 0 then 'Administrativo'
                when 1 then 'Asistencial'
                when 2 then 'Asistencial y Administrativo'
                end TIPO_DE_GESTION";

                $query = DB::connection('pgsql')
                    ->select('
                    select i.serial,ip."document",i."createdAt" ,case i."type"
                ' . $tipo . '
                ,' . $concatName . '
                ,ip.birthday  fecha_nacimiento
                ,m.name minoria
                ,id.description
                ,e.name Entidad
                ,a.name "area"
                ,c.name categoria
                ,ip.country
                ,ip.city
                ,' . $case2 . '
                , p."name" prioridad
                , i."createdAt" fecha_creacion
                ,' . $ultimocase . '
                ,rights."name" DERECHO_VULNERADO
                from issues i
                LEFT JOIN issue_patient ip
                ON ip.id = i."patientId"
                LEFT JOIN minorities m
                ON m.id = ip."minorityId"
                LEFT JOIN issues_details id
                ON id."issueId" =i.id
                LEFT JOIN entity e
                ON e.id = ip."entityId"
                LEFT JOIN issue_areas ia
                ON   i.id = ia."issueId" and ia.main =true 
                LEFT JOIN "area" a
                ON ia."areaId"  = a.id 
                LEFT JOIN priority p 
                ON i."priorityId"  = p.id
                LEFT JOIN categories c
                ON i."categoryId"  = c.id
                left join categories_rights cr
                on c.id = cr."categoryId"
                left join rights
                on rights.id = cr."rightId"
                where i."type" != 3 and i."type" != 4 and i."createdAt" BETWEEN ' . "'" . $fechaInicial . "'" . ' and ' . "'" . $fechaActual . "'" . '

                    ');

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                $ahora = new DateTime(date("Y-m-d"));


                foreach ($query as $result) {

                    $nacimiento = new DateTime($result->fecha_nacimiento);
                    $dif = $ahora->diff($nacimiento);
                    $edad = $dif->format("%y");
                    $prioridad = '';

                    
                        if ($edad < 18 || $edad > 60) {

                            if($edad > 120){
                                $prioridad = 'PRIORIDAD BAJA';
                            }else{
                                $prioridad = 'PRIORIDAD ALTA';
                            }
                        }else{

                            if ($edad >= 18 ||$edad <= 60) {
    
                                $prioridad = 'PRIORIDAD MEDIA';
                            } else {
    
                                $prioridad = 'PRIORIDAD BAJA';
                            }

                        }
                   

                    $resultado[] = [
                        'N°Caso: ' => $result->serial, 'N°Documento' => $result->document
                        ,'Nombre'=>$result->nombres, 'Edad' => $edad,'Pais' => $result->country,'FechaCasos'=>$result->createdAt
                        , 'Ciudad' => $result->city, 'Tipo' => $result->tipo, 'Entidad' => $result->entidad
                        , 'Minoria' => $result->minoria,  'Area' => $result->area, 'Categoria' => $result->categoria
                        ,  'RequerimientoDeJuridicaLegal' => $result->requerimiento_de_juridica_legal
                        , 'RiesgoDeVida' => $result->riesgo_de_vida, 'Prioridad' => $prioridad, 'Descripcion' =>$result->description
                    ];
                }



                if (count($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);


                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => count($resultado),
                    'data' => $resultado
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
    public function replaceCharacter($text)
    {
        if (!$text) {
            return false;
        }
        return str_replace(array("\r", "\n", "*", "**"), '', $text);
    }
}
