<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExitusController extends Controller
{
    //
    public function __construct()
    {
        return $this->middleware('auth.apikey');
    }



    /**
     * @OA\Get (
     *     path="/api/v1/financial-exitus/get/bills-by-date/{startdate?}/end/{enddate?}",
     *     operationId="get Bills Info",
     *     tags={"Exitus"},
     *     summary="Get Bills Info",
     *     description="Returns Bills Info",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="startdate?",
     *          description="Initial Date For Search - Optional",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="enddate?",
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
    public function getBillByDateRange(Request $request, $startDate, $endDate)
    {
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if (!$startDate) {

                    $init = Carbon::now()->format('Y-m-d');
                } else {
                    $init = $startDate;
                }

                if (!$endDate) {

                    $end = Carbon::now()->format('Y-m-d');
                } else {
                    $end = $endDate;
                }


                $query_bills = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM DBO.EXITUS_FACTURAS_ESTADOS() WHERE FECHA_FACTURA BETWEEN '$init' AND '$end'");

                if (count($query_bills) > 0) {

                    $bills = [];

                    foreach ($query_bills as $bill) {
                        $tempBills = array(
                            'checkNumber' => trim($bill->FACTURA . '-' . $bill->DOCUMENTO . '-' . $bill->TIPO_DOC . '-' . $bill->ING),
                            'invoice' => trim($bill->FACTURA),
                            'invoiceType' => trim($bill->TIPO_FACTURA),
                            'invoiceClass' => trim($bill->CLASE_FACTURA),
                            'invoicePatientDocument' => trim($bill->DOCUMENTO),
                            'invoicePatientDocType' => trim($bill->TIPO_DOC),
                            'invoiceAdmConsecutive' => trim($bill->ING),
                            'invoicePatientName' => trim($bill->PACIENTE),
                            'invoiceAdmDate' => Carbon::parse($bill->FECHA_INGRESO)->format('Y-m-d'),
                            'invoiceOutDate' => Carbon::parse($bill->FECHA_EGRESO)->format('Y-m-d'),
                            'invoiceProviderNit' => trim($bill->NIT_EMPRESA),
                            'invoiceProviderName' => trim($bill->NOM_EMPRESA),
                            'invoiceContract' => trim($bill->CONTRATO),
                            'invoiceDate' => carbon::parse($bill->FECHA_FACTURA)->format('Y-m-d'),
                            'invoiceStatus' => trim($bill->ESTADO_FACTURA),
                            'invoiceValue' => $bill->VALOR_FACTURA,
                        );

                        $bills[] = $tempBills;
                    }

                    if (count($bills) > 0) {
                        return response()
                            ->json([
                                'status' => 200,
                                'message' => 'Success',
                                'counter' => count($bills),
                                'data' => $bills,
                            ]);
                    } else {
                        return response()
                            ->json([
                                'status' => 204,
                                'message' => 'No bills found',
                            ]);
                    }
                } else {
                    return response()
                        ->json([
                            'status' => 204,
                            'message' => 'Empty Bills Query Repsonse',
                        ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized',
                ]);
            }
        }
    }
}
