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
    public function getFelicitacionesVsQuejas(Request $request, $fechaInicial = '', $fechaFinal = '')
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
            if (!$fechaInicial || !$fechaFinal) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            try {


                $query = DB::connection('pgsql')
                    ->table('issues')
                    ->whereBetween('createdAt', [$fechaInicial, $fechaFinal])
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
     *     path="/api/v1/indicadores/get/porcentaje/{fechaInicial?}/{fechaFinal?}/{idType?}",
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
    public function getPorcentajePQR(Request $request, $fechaInicial = '', $fechaFinal = '', $idType = null)
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
            if (!$fechaInicial || !$fechaFinal || $idType > 4 || $idType == null) {

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

                //$subquery='(SELECT COUNT(id) FROM issues WHERE "type" =1)';
                $query = DB::connection('pgsql')
                    ->table('issues')
                    ->whereBetween('createdAt', [$fechaInicial, $fechaFinal])
                    ->select(
                        DB::raw('count(type) as totales'),
                        DB::raw('(SELECT COUNT(id) as quejas FROM issues WHERE "type" =' . $idType . ')')
                    )

                    ->get();

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) $resultado[] = [
                    'Porcentaje ' => trim(($result->quejas / $result->totales) * 100),
                    'Quejas' => $result->quejas,
                    'Totales de PQR' => $result->totales,
                    'type' => $descripcion,
                ];


                //if (count($resultado) < 0) return response()->json([
                //  'msg' => 'Empty Diagnoses Array',
                //'status' => 204,
                //'data' => []
                //], 204);


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
     *     path="/api/v1/indicadores/get/tiempopromedio/{fechaInicial?}/{fechaFinal?}/{idType?}",
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
    public function getTiempoPromedio(Request $request, $fechaInicial = '', $fechaFinal = '')
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
            if (!$fechaInicial || !$fechaFinal) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
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
                    and i."createdAt" BETWEEN ' . "'" . $fechaInicial . "'" . ' and ' . "'" . $fechaFinal . "'" . '
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
                    'Tiempo promedio representado en dias' => $resultado,
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
                    'Total reportes' => $result->reportes,
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
    public function getFelicitacionesVsQuejasPorArea(Request $request, $fechaInicial = '', $fechaFinal = '')
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
            if (!$fechaInicial || !$fechaFinal) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
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
                                where i."createdAt" between ' . "'" . $fechaInicial . "'" . ' and ' . "'" . $fechaFinal . "'" . '
                                GROUP BY i."type",a."name"
                                order by a."name"
                            ');

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                $re = [];
                $typeOfCase = json_decode(json_encode($this->typeOfCase()));
                $casesArray = [];

                foreach ($typeOfCase as $tc) {
                    foreach ($re as $case) {

                        if (array_search($case->tipo, $typeOfCase)) {

                            return 'ok';
                        }
                    }
                }




                $casesArray = array_values($re);

                return response()->json([
                    'msg' => 'Casos',
                    'status' => 200,
                    'array' => $casesArray
                ]);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    function typeOfCase()
    {

        return  [
            (object)[
                'id' => 0,
                'description' => 'Peticion'
            ],
            (object)[
                'id' => 1,
                'description' => 'Queja'
            ],
            (object)[
                'id' => 2,
                'description' => 'Reclamo'
            ],
            (object)[
                'id' => 3,
                'description' => 'Sugerencia'
            ],
            (object)[
                'id' => 4,
                'description' => 'Felicitacion'
            ],
        ];
    }
}
