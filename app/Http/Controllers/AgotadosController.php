<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgotadosController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1/agotados/get/drugs-by-code/{sumcod}",
     *     operationId="drugsByCode",
     *     tags={"Agotados"},
     *     summary="Get Drugs By Code",
     *     description="Returns Drugs By Code",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="sumcod",
     *          description="Código del Medicamento",
     *          required=false,
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
     *
     * )
     */
    public function drugsByCode(Request $request, $drugCode)
    {

        if ($request->hasHeader('X-Authorization'))
        {
            try {

                if ($drugCode != '' && $drugCode != null)
                {
                    $query = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM AGOTADOS_MEDICAMENTO_X_CODIGO('$drugCode')");

                    if (sizeof($query) > 0) {

                        $records = [];

                        foreach ($query as $item)
                        {
                            $temp = array(
                                'sumCod' => $item->CODIGO_MEDICAMENTO,
                                'sumGName' => $item->NOMBRE_GENERICO,
                                'sumGroupCod' => $item->COD_GRUPO,
                                'sumGroupName' => $item->NOM_GRUPO,
                                'balance' => (int) $item->SALDO
                            );

                            $records = $temp;
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
                                    'msg' => 'No hay datos en la respuesta a esta solicitud',
                                    'data' => [],
                                    'status' => 500
                                ], 500);

                        }
                    } else {

                        return response()
                            ->json([
                                'msg' => 'La consulta a la BD no ha devuelto ningun dato',
                                'data' => [],
                                'status' => 500
                            ], 500);

                    }
                } else {

                    return response()
                        ->json([
                            'msg' => 'Parametro Código de Medicamento no puede estar vacio',
                            'data' => [],
                            'status' => 500
                        ], 500);

                }

            } catch (\Throwable $e) {
                throw $e;
            }
        }

    }


    /**
     * @OA\Get (
     *     path="/api/v1/agotados/get/purchase-order/not-greater-than-21",
     *     operationId="purchaseOrderNoGreaterThan21",
     *     tags={"Agotados"},
     *     summary="purchaseOrderNoGreaterThan21",
     *     description="purchaseOrderNoGreaterThan21",
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
     *
     * )
     */
    public function getPurchaseOrders(Request $request)
    {

        if ($request->hasHeader('X-Authorization'))
        {
            try {

                $query = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM AGOTADOS_ORDENES_COMPRAS_NO_MAYOR_21()");

                if (sizeof($query) > 0)
                {
                    $records = [];

                    foreach($query as $item)
                    {

                        $temp = array(
                            'orderNumber' => $item->NRO,
                            'orderDate' => $item->FECHA_ORDEN,
                            'sumCod' => $item->CODIGO,
                            'sumGName' => $item->SUMINISTRO,
                            'quantity' => $item->CANTIDAD,
                            'unitCost' => $item->VALOR_UNITARIO,
                            'totalCost' => $item->VALOR_TOTAL,
                            'reqNumber' => $item->NRO_REQUI,
                            'providerCod'=> $item->COD_PROVEED,
                            'provider'=> $item->PROVEED,
                            'wareHouse' => $item->BODEGA,
                            'orderObs' => $item->OBSERVACION,
                            'deliveryTime' => $item->TIEMPO_ENTREGA
                        );

                        $records[] = $temp;

                    }

                    if (sizeof($records) < 0)
                    {

                        return response()
                            ->json([
                                'msg' => 'No hay datos en respuesta a la solicitud',
                                'data' => [],
                                'status' => 400
                            ], 400);

                    }

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $records
                        ], 200);

                }

            } catch (\Throwable $e) {
                throw $e;
            }
        } else {

            return response()
                ->json([
                    'msg' => 'Acceso no Autorizado',
                    'status' => 403
                ], 403);

        }

    }

}
