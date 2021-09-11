<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HygeaController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/warehouses",
     *     operationId="get WareHouses Info",
     *     tags={"Hygea"},
     *     summary="Get WareHouses Info",
     *     description="Returns WareHouses Info",
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
    public function getWarehouses(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            $query_warehouse = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HYGEA_BODEGAS()");

            if (count($query_warehouse) > 0) {

                $warehouses = [];

                foreach ($query_warehouse as $item) {
                    $temp = array(
                        'entCode' => $item->EMPRESA,
                        'whDescription' => $item->BODEGA_DESCRIPCION,
                        'whCode' => $item->BODEGA_COD,
                        'headquarters' => $item->SEDE,
                        'costCenter' => $item->CENTRO_COSTO,
                        'prefix' => $item->PREFIJO,
                        'whTypeCode' => $item->TIPO_BODEGA_COD,
                        'whTypeDesc' => $item->TIPO_BODEGA_DESC,
                        'type' => $item->CLASE_COD,
                        'typeDesc' => $item->CLASE_DESC,
                        'whDispatchToFloor' => $item->DESPACHO_A_PISO,
                        'consignmentWh' => $item->BODEGA_CONSIGNACION,
                        'purchaseWh' => $item->BODEGA_COMPRA,
                        'lockStatus' => $item->ESTADO_BLOQUEO,
                        'returnWh' => $item->BODEGA_DEVOLUCION,
                        'tempLocation' => $item->UBICACION_TEMPORAL,
                        'utilityCenter' => $item->CENTRO_UTILIDAD,
                        'subUtilityCenter' => $item->SUBCENTRO_UTILIDAD,
                        'transferWh' => $item->BODEGA_TRASLADO,
                    );

                    $warehouses[] = $temp;

                }

                if (count($warehouses) > 0) {

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $warehouses
                        ]);

                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty Warehouses Array',
                            'status' => 200,
                            'data' => []
                        ]);

                }


            } else {

                return response()
                    ->json([
                        'msg' => 'Empty Query Warehouses Array',
                        'status' => 200,
                        'data' => []
                    ]);

            }

        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/providers",
     *     operationId="get Providers Info",
     *     tags={"Hygea"},
     *     summary="Get Providers Info",
     *     description="Returns Providers Info",
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
    public function getProviders(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $query_providers = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM PROVEEDORES()");


            if (count($query_providers) > 0)
            {

                $providers = [];

                foreach ($query_providers as $item) {

                    $temp = array(
                        'providerCode' => rtrim($item->NIT),
                        'providerName' => rtrim($item->RAZON_SOCIAL),
                        'providerAddress' => $item->DIRECCION,
                        'providerCity' => $item->CIUDAD,
                        'providerPhone' => $item->TELEFONO,
                        'providerEmail' => $item->EMAIL
                    );

                    $providers[] = $temp;

                }

                if (count($providers) > 0) {

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $providers
                        ]);

                } else {
                    return response()
                        ->json([
                            'msg' => 'Empty Providers Array',
                            'status' => 200,
                            'data' => []
                        ]);
                }


            } else {

                return response()
                    ->json([
                        'msg' => 'Empty Query Providers Array',
                        'status' => 200,
                        'data' => []
                    ]);

            }


        }

    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/purchase-orders/{date?}",
     *     operationId="get Purchases Orders Info",
     *     tags={"Hygea"},
     *     summary="Get Purchases Orders Info",
     *     description="Returns Purchases Orders Info",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="date",
     *          description="date",
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
     * )
     */
    public function getPurchaseOrders(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $date = \request('init', \Carbon\Carbon::now()->format('Ymd'));


            $query_purchase_order = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HYGEA_PURCHASE_ORDERS('$date')");


            if (count($query_purchase_order) > 0)
            {

                $purchaseOrders = [];

                foreach ($query_purchase_order as $item)
                {

                    $temp = array(
                        'orderNro' => $item->NRO,
                        'transacNro' => $item->TRANSAC,
                        'providerCode' => $item->PROVEED_COD,
                        'providerName' => $item->PROVEED,
                        'paymentDeadline' => $item->PLAZO,
                        'OrderSatatus' => $item->ESTADO_ORDEN,
                        'sumCod' => $item->CODIGO,
                        'sumDesc' => $item->SUMINISTRO,
                        'sumStatus' => $item->ESTADO_ITEM,
                        'quantity' => $item->CANTIDAD,
                        'receivedQuantity' => $item->RECIBIDO,
                        'unitValue' => $item->VALOR_UNITARIO,
                        'orderDate' => $item->FECHA_ORDEN,
                        'warehouse' => $item->BODEGA,
                        'totalValue' => $item->VALOR_TOTAL,
                        'discountValue' => $item->VALOR_DESCUENTO,
                        'requisitionNro' => $item->NRO_REQUI,
                        'createdBy' => $item->USUARIO_CREA,
                        'authorizedBy' => $item->USUARIO_AUTORIZA,
                        'ordObservation' => $item->OBSERVACION,
                        'deliveryTime' => $item->TIEMPO_ENTREGA,
                    );

                    $purchaseOrders[] = $temp;

                }

                if (count($purchaseOrders) > 0) {

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $purchaseOrders
                        ]);

                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty Purchase Orders Array',
                            'status' => 200,
                            'data' => []
                        ]);

                }

            } else {

                return response()
                    ->json([
                        'msg' => 'Empty Purchase Orders Query',
                        'status' => 200,
                        'data' => []
                    ]);

            }

        }

    }



}
