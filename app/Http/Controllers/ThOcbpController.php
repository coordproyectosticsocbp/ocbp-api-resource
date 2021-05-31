<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThOcbpController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    public function getPendingForBilling(Request $request, $initalDate, $finalDate)
    {

        if ($request->hasHeader('X-Authorization')) {

            $query = DB::connection('sqlsrv_hosv')
                ->select(
                    DB::raw("SELECT * FROM Pend_x_Facturar_flujo('$initalDate','$finalDate',1)")
                );

            if (count($query) > 0) {
                $records = [];

                foreach ($query as $item) {
                    $temp = array(
                        'docPatient' => $item->NRO_IDENTIFICACION,
                        'patientDocType' => $item->TIPO_DOCUMENTO,
                        'patientName' => $item->NOMBRE_COMPLETO_PACIENTE,
                        'ingCsc' => $item->INGCSC,
                        'anio' => $item->ANIO,
                        'admDate' => $item->FECHA_INGRESO,
                        'egrDate' => $item->FECHA_EGRESO,
                        'patientCompany' => $item->DESCRIPCION_EMPRESA,
                        'contractDes' => $item->DESCRIPCION_CONTRATO,
                        'patientPavilion' => $item->DESCRIPCION_PABELLON,
                        'invoiceValue' => $item->VLR_TOTAL,
                        'admUser' => $item->USUARIO_ADMISION,
                        'invoicer' => $item->FACTURADOR,
                        'closingStatus' => $item->ESTADO_CIERRE,
                        'currentStatus' => $item->ESTADO_ACTUAL,
                    );

                    $records[] = $temp;
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
                        'msg' => 'No Hay Datos Para Esta Solicitud',
                        'status' => 422,
                    ]);
            }

        }
    }

}
