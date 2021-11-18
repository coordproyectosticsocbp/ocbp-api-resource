<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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


            if (count($query_providers) > 0) {

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

            $date = \request('init', \Carbon\Carbon::now()->format('Y-m-d'));


            $query_purchase_order = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HYGEA_PURCHASE_ORDERS('$date')");


            if (count($query_purchase_order) > 0) {

                $purchaseOrders = [];

                foreach ($query_purchase_order as $item) {

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


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/total-providers",
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
    public function getAllProviders(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            $query_providers = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM HYGEA_ALL_PROVEEDORES()");

            if (count($query_providers) > 0) {

                $providers = [];

                foreach ($query_providers as $provider) {

                    $temp = array(
                        'providerCode' => $provider->CODIGO,
                        'providerVerificationCode' => $provider->CODIGO_VERIFICACION,
                        'providerCodeType' => $provider->CODIGO_TIPO_DOC,
                        'providerCodeTypeDesc' => $provider->TIPO_DOC,
                        'providerName' => $provider->RAZON_SOCIAL,
                        'providerAddress' => $provider->DIRECCION,
                        'providerPhone' => $provider->TELEFONO,
                        'providerEmail' => $provider->EMAIL,
                        'providerEntityType' => $provider->CODIGO_TIPO_ENTIDAD,
                        'providerEntityTypeDes' => $provider->TIPO_ENTIDAD,
                        'providerType' => $provider->CODIGO_TIPO_TERCERO,
                        'providerTypeDesc' => $provider->TIPO_TERCERO,
                        'providerIsInactive' => $provider->ACTIVO,
                    );

                    $providers[] = $temp;
                }

                if (count($providers) > 0) {

                    return response()
                        ->json([
                            'data' => $providers,
                            'status' => 200,
                            'msg' => 'Ok'
                        ]);
                } else {

                    return response()
                        ->json([
                            'data' => [],
                            'status' => 204,
                            'msg' => 'Empty array Providers'
                        ]);
                }
            } else {

                return response()
                    ->json([
                        'data' => [],
                        'status' => 204,
                        'msg' => 'Empty Query Providers Response'
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/drugs-inventory",
     *     operationId="get Drugs Inventory Info",
     *     tags={"Hygea"},
     *     summary="Get Drugs Inventory Info",
     *     description="Returns Drugs Inventory Info",
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
    public function drugsInventory(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            $query_inventory = DB::connection('sqlsrv_hosvital')
                ->select('SELECT * FROM HYGEA_INVENTARIO_MEDICAMENTOS()');

            if (count($query_inventory) > 0) {
                $drugs = [];

                foreach ($query_inventory as $item) {

                    $temp = array(
                        'sumCod' => trim($item->MEDICAMENTO),
                        'balance' => (int)$item->SALDO,
                        'warehouse' => $item->BODEGA,
                    );

                    $drugs[] = $temp;
                }

                if (count($drugs) > 0) {

                    return response()
                        ->json([
                            'data' => $drugs,
                            'status' => 200,
                            'msg' => 'Ok'
                        ]);
                } else {

                    return response()
                        ->json([
                            'data' => [],
                            'status' => 204,
                            'msg' => 'Empty drugs Array'
                        ]);
                }
            } else {

                return response()
                    ->json([
                        'data' => [],
                        'status' => 204,
                        'msg' => 'Empty Inventory Query Array'
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/all-drugs",
     *     operationId="get All Drugs Info",
     *     tags={"Hygea"},
     *     summary="Get All Drugs Info",
     *     description="Returns All Drugs Info",
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
    public function allDrugs(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            $query_drugs = DB::connection('sqlsrv_hosvital')
                ->select('SELECT * FROM PRODUCTO_ACTIVOS()');

            if (count($query_drugs) > 0) {
                $drugs = [];
                $controlledMedicine = '';

                foreach ($query_drugs as $item) {

                    if ($item->MEDICAMENTO_CONTROLADO === 'S') {
                        $controlledMedicine = 1;
                    } else if ($item->MEDICAMENTO_CONTROLADO === 'N') {
                        $controlledMedicine = 0;
                    }

                    $temp = array(
                        'sumCod' => trim($item->CODIGO),
                        'balance' => $item->DESCRIPCION,
                        'activePrinciple' => $item->PRINCIPIO_ACTIVO,
                        'invimaCode' => trim($item->REGISTRO_INVIMA),
                        'cumCod' => trim($item->CODIGO_CUM),
                        'concentration' => trim($item->CONCENTRACION),
                        'pharmForm' => $item->FORMA_FARMACEUTICA,
                        'pharmFormDesc' => trim($item->FORMA_FARMACEUTICA_DESC),
                        'posNoPos' => $item->POS,
                        'atcCode' => trim($item->ATC),
                        'price' => $item->VALOR,
                        'groupCode' => trim($item->GRUPO_COD),
                        'group' => trim($item->GRUPO),
                        'subGroupCode' => trim($item->SUBGRUPO_COD),
                        'subGroup' => trim($item->SUBGRUPO),
                        'storageCondition' => trim($item->CONDICION_ALMACENAJE),
                        'dispatchAdditional' => $item->DESPACHAR_COMO_ADICIONAL,
                        'medicationControl' => $item->MEDICAMENTO_CONTROL,
                        'remission' => $item->REMISION,
                        'warehouse' => $item->ADMITE_ADICIONALES,
                        'highPrice' => $item->ALTO_COSTO,
                        'applyForNursing' => $item->SOLICITA_ENFERMERIA,
                        'refusedType' => $item->TIPO_REHUSO,
                        'riskClass' => $item->CLASE_RIESGO,
                        'averageCost' => $item->COSTO_PROMEDIO,
                        'creationDate' => Carbon::parse($item->FECHA_CREACION)->format('Y-m-d H:m:s'),
                        'riskClasification' => $item->CLASIFICACION_RIESGO,
                        'lastEntryDate' => Carbon::parse($item->FECHA_ULTIMA_ENTRADA)->format('Y-m-d H:m:s'),
                        'controlledMedication' => $controlledMedicine,
                        'productType' => $item->TIPO_MED_O_SUM
                    );

                    $drugs[] = $temp;
                }

                if (count($drugs) > 0) {

                    return response()
                        ->json([
                            'status' => 200,
                            'msg' => 'Ok',
                            'data' => $drugs,
                        ]);
                } else {

                    return response()
                        ->json([
                            'data' => [],
                            'status' => 204,
                            'msg' => 'Empty drugs Array'
                        ]);
                }
            } else {

                return response()
                    ->json([
                        'data' => [],
                        'status' => 204,
                        'msg' => 'Empty Inventory Query Array'
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/purchase-orders/{init?}{end?}",
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
     *          name="init?",
     *          description="Initial Date For Search - Optional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="end?",
     *          description="End Date For Search - Optional",
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
    public function getPurchaseOrdersByDateRange(Request $request, $initDate = '', $endDate = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            try {

                $init = '';
                $end = '';

                if (!$initDate) {

                    $init = Carbon::now()->format('Y-m-d');
                } else {
                    $init = $initDate;
                }

                if (!$endDate) {

                    $end = Carbon::now()->format('Y-m-d');
                } else {
                    $end = $endDate;
                }

                $query_purchase_order = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM HYGEA_PURCHASE_ORDERS_BY_DATE_RANGE('$init', '$end') ORDER BY FECHA_ORDEN");


                if (count($query_purchase_order) > 0) {

                    $purchaseOrders = [];

                    foreach ($query_purchase_order as $item) {

                        $temp = array(
                            'orderNro' => $item->NRO,
                            'transacNro' => $item->TRANSAC,
                            'providerCode' => $item->PROVEED_COD,
                            'providerName' => trim($item->PROVEED),
                            'paymentDeadline' => trim($item->PLAZO),
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
                            'authorizedBy' => trim($item->USUARIO_AUTORIZA),
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
            } catch (\Throwable $e) {
                return $e->getMessage();
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/medical-orders/{orderdate?}",
     *     operationId="get Medical Orders Information",
     *     tags={"Hygea"},
     *     summary="Get Medical Orders Info",
     *     description="Returns Medical Order Information",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="orderdate?",
     *          description="Order Date Y-m-d",
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
    public function getMedicalOrders(Request $request, $orderDate = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            if (!$orderDate) {

                return response()
                    ->json([
                        'msg' => 'Parameter orderDate cannot be Empty',
                        'status' => 400
                    ]);
            } else {

                $orderDateU = carbon::parse($orderDate)->format('Ymd H:i:s');
                $query_medical_orders = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM HYGEA_ORDENAMIENTO_PARA_PRODUCCION('$orderDateU')");

                if (count($query_medical_orders) > 0) {

                    $records = [];

                    foreach ($query_medical_orders as $item) {

                        $temp = array(
                            'docPac' => $item->DOC,
                            'docType' => $item->TIP_DOC,
                            'patientName' => $item->NOMBRE_PACIENTE,
                            'age' => $item->EDAD,
                            'admConsecutive' => $item->INGRESO,
                            'patientPavilion' => $item->PABELLON,
                            'patientHabitation' => $item->CAMA,
                            'sumCod' => $item->CODIGO,
                            'sumGName' => $item->NOMBRE_SUMINISTRO,
                            'sumDose' => (int)$item->DOSIS,
                            'sumUnity' => $item->UNIDAD,
                            'ordObservation' => $item->OBSERVACION,
                            'ordFrecuency' => $item->FRECUENCIA,
                            'quantity' => (int)$item->CANTIDAD,
                            'applicationForm' => trim($item->VIA),
                            'folio' => $item->FOLIO,
                            'status' => $item->ESTADO,
                            'doctorWhoOrdered' => trim($item->MEDICO),
                            'orderDate' => $item->FECHA
                        );

                        $records[] = $temp;
                    }

                    if (count($records) < 0) {

                        return response()
                            ->json([
                                'msg' => 'Empty Orders Array',
                                'status' => 200,
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
                            'msg' => 'Empty Query Medical Orders Array',
                            'status' => 204
                        ]);
                }
            }
        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/hygea/get/drug-rotation/{sumcod?}",
     *     operationId="get Rotation, Purchase, Output Information",
     *     tags={"Hygea"},
     *     summary="Get Rotation, Purchase, Output Information",
     *     description="Returns Rotation, Purchase, Output Information",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="sumcod?",
     *          description="Product Code",
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
    public function getProductRotationPurchasesOutputBySumCod(Request $request, $sumCod = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            try {

                if (!$sumCod) {

                    return response()
                        ->json([
                            'msg' => 'Parameter sumCod cannot be Empty',
                            'status' => 400
                        ]);
                } else {

                    // CONSULTAS A LA BASE DE DATOS PARA CÓDIGO DE SUMINISTRO, ROTACIÓN DE PRODUCTOS, COMPRAS Y SALIDAS DE PRODUCTOS
                    $querySum = DB::connection('sqlsrv_hosvital')
                        ->select(
                            "SELECT    RTRIM(MAESUM1.MSRESO) COD_PRO,
                                            CASE
                                                    WHEN MAESUMN.MSDesc = '.' THEN RTRIM(MAESUM1.MSNomG)
                                                    WHEN MAESUMN.MSDesc IS NOT NULL THEN RTRIM(MAESUMN.MSDesc) ELSE  RTRIM(MAESUM1.MSNomG) END DESCRIPCION,
                                            RTRIM(MAESUM1.MSNomG)  DESCRIPCION_COMERCIAL
                                FROM  MAESUM1 LEFT JOIN MAESUMN ON  MAESUM1.MSCodi = MAESUMN.MSCodi
                                                                    AND MAESUM1.MSPrAc = MAESUMN.MSPRAC
                                                                    AND MAESUM1.CncCd = MAESUMN.CncCd
                                                                    AND MAESUM1.MSForm =  MAESUMN.MSForm
                                WHERE MAESUM1.MSRESO = '$sumCod'"
                        );

                    $queryRotation = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HYGEA_ROTACION_PRODUCTO('$sumCod')");

                    $queryPurchase = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HYGEA_COMPRAS_PRODUCTO('$sumCod')");

                    $queryOutput = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM HYGEA_DESPACHOS_PRODUCTO('$sumCod')");


                    // VALIDACIÓN SI EL MEDICAMENTO TIENE ROTACIÓN
                    if (count($queryRotation) > 0) {

                        $rotation = [];

                        foreach ($queryRotation as $item) {

                            $temp = array(
                                'balance' => (int) $item->SALDO,
                                'rotation' => (int) $item->ROTACION,
                                'averageCost' => $item->COSTO_PROMEDIO,
                                'month' => trim($item->MES),
                                'year' => trim($item->ANIO),
                            );

                            $rotation[] = $temp;
                        }

                        if (count($rotation) < 0) {

                            return response()
                                ->json([
                                    'msg' => 'Empty Rotation Array',
                                    'status' => 200,
                                    'data' => []
                                ]);
                        }
                    }


                    // VALIDACIÓN SI EL MEDICAMENTO TIENE ROTACIÓN
                    if (count($queryPurchase) > 0) {

                        $purchases = [];

                        foreach ($queryPurchase as $purchase) {

                            $temp = array(
                                'sumCod' => trim($purchase->COD_SUM),
                                'quantity' => (int) $purchase->COMPRAS,
                                'month' => trim($purchase->MES),
                                'year' => trim($purchase->ANIO),
                            );

                            $purchases[] = $temp;
                        }

                        if (count($purchases) < 0) {

                            return response()
                                ->json([
                                    'msg' => 'Empty Purchases Array',
                                    'status' => 200,
                                    'data' => []
                                ]);
                        }
                    }

                    // VALIDACIÓN SI EL MEDICAMENTO TIENE ROTACIÓN
                    if (count($queryOutput) > 0) {

                        $outputs = [];

                        foreach ($queryOutput as $output) {

                            $tempOutput = array(
                                'sumCod' => trim($output->COD_SUM),
                                'quantity' => (int) $output->SALIDAS,
                                'month' => trim($output->MES),
                                'year' => trim($output->ANIO),
                            );

                            $outputs[] = $tempOutput;
                        }

                        if (count($outputs) < 0) {

                            return response()
                                ->json([
                                    'msg' => 'Empty Outputs Array',
                                    'status' => 200,
                                    'data' => []
                                ]);
                        }
                    }

                    // VALIDACIÓN PRINCIPAL DEL MEDICAMENTO -- CÓDIGO, DESCRIPCIÓN, DESCRIPCIÓN COMERCIAL, ARRAY DE ROTACIÓN, ARRAY DE COMPRAS, ARRAY DE DESPACHOS
                    if (count($querySum) > 0) {

                        $suministros = [];

                        foreach ($querySum as $item) {

                            $tempSum = array(
                                'sumCod' => trim($item->COD_PRO),
                                'sumDesc' => trim($item->DESCRIPCION),
                                'sumDescComercial' => trim($item->DESCRIPCION_COMERCIAL),
                                'rotations' => $rotation,
                                'purchases' => $purchases,
                                'outputs' => $outputs,
                            );

                            $suministros[] = $tempSum;
                        }

                        if (count($suministros) < 0) {

                            // PETICIÓN CON RESPUESTA VACIA
                            return response()
                                ->json([
                                    'msg' => 'Empty Suministro Array',
                                    'status' => 200,
                                    'data' => []
                                ]);
                        } else {

                            // PETICIÓN CON RESPUESTA EXITOSA
                            return response()
                                ->json([
                                    'msg' => 'Ok',
                                    'status' => 200,
                                    'data' => $suministros
                                ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                return $e->getMessage();
            }
        }
    }
}
