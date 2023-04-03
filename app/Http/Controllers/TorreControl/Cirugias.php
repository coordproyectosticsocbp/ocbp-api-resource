<?php

namespace App\Http\Controllers\TorreControl;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Cirugias extends Controller
{
    public function getCirugias(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {
            /*
            if ($fechaInicial == '' || $fechaActual == '') {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            
            $datetime1 = date_create($fechaInicial);
            $datetime2 = date_create($fechaActual);

            $contador = date_diff($datetime1, $datetime2);
            $differenceFormat = '%a';

            */
            $fechaInicial = date("Y-m") . '-01';
            $fechaActual = date('Y-m-d');

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorizedd',
                'status' => 401
            ]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */

            try {


                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    SELECT		PROCIR.MPCedu DOCUMENTO
					, PROCIR.MPTDoc TIPO_DOC
                    , PROCIR.ProFec FECHA
					, CAPBAS.MPNOMC PACIENTE
					--, INGRESOS.IngCsc INGRESO
					, RTRIM(PROCIR1.CrgCod) PROCEDIMIENTO_COD
					, RTRIM(MAEPRO.PrNomb) NOMB_PROCED
					, PROCIR.ProEsta ESTADO
		            FROM	PROCIR
					LEFT JOIN INGRESOS ON	INGRESOS.MPCedu = PROCIR.MPCedu
											AND INGRESOS.MPTDoc = PROCIR.MPTDoc
											AND INGRESOS.IngCsc = PROCIR.ProCtvIn
					INNER JOIN PROCIR1  ON	PROCIR1.ProEmpCod = PROCIR.ProEmpCod
											AND PROCIR1.ProMCDpto = PROCIR.ProMCDpto
											AND PROCIR1.ProCirCod = PROCIR.ProCirCod	
					INNER JOIN MAEPRO ON PROCIR1.CrgCod = MAEPRO.PrCodi
					INNER JOIN CAPBAS ON	CAPBAS.MPCedu = PROCIR.MPCedu
											AND CAPBAS.MPTDoc = PROCIR.MPTDoc
		            WHERE		CAST(PROCIR.ProFec AS DATE) BETWEEN '" . $fechaInicial . "'  AND '" . $fechaActual . "'
					AND PROCIR.ProEsta IN (3, 4)
					AND PROCIR1.CrgEst = 'S'
		            ORDER BY	PROCIR1.CrgCod DESC
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) $resultado[] = [
                    'Documento' => trim($result->DOCUMENTO),
                    'TipoDoc' => trim($result->TIPO_DOC),
                    'Paciente' => trim($result->PACIENTE),
                    'Fecha' => trim($result->FECHA),
                    'Procedimiento' => trim($result->PROCEDIMIENTO_COD),
                    'NombreProcedimiento' => ($result->NOMB_PROCED),
                    'Estado' => trim($result->ESTADO)
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
    public function patientMortality(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {
            /*
            if ($fechaInicial == '' || $fechaActual == '') {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            
            $datetime1 = date_create($fechaInicial);
            $datetime2 = date_create($fechaActual);

            $contador = date_diff($datetime1, $datetime2);
            $differenceFormat = '%a';

            */
            $fechaInicial = date("Y-m") . '-01';
            $fechaActual = date('Y-m-d');

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorizedd',
                'status' => 401
            ]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */
            $resultado = [];
            try {


                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    SELECT		PROCIR.MPCedu DOCUMENTO
					, PROCIR.MPTDoc TIPO_DOC
					, PROCIR.ProFec FECHA
					, CAPBAS.MPNOMC PACIENTE
					, RTRIM(PROCIR1.CrgCod) PROCEDIMIENTO_COD
					, RTRIM(MAEPRO.PrNomb) NOMB_PROCED
					, PROCIR.ProEsta ESTADO
		            FROM	PROCIR
					LEFT JOIN INGRESOS ON	INGRESOS.MPCedu = PROCIR.MPCedu
											AND INGRESOS.MPTDoc = PROCIR.MPTDoc
											AND INGRESOS.IngCsc = PROCIR.ProCtvIn
					INNER JOIN PROCIR1  ON	PROCIR1.ProEmpCod = PROCIR.ProEmpCod
											AND PROCIR1.ProMCDpto = PROCIR.ProMCDpto
											AND PROCIR1.ProCirCod = PROCIR.ProCirCod	
					INNER JOIN MAEPRO ON PROCIR1.CrgCod = MAEPRO.PrCodi
					INNER JOIN CAPBAS ON	CAPBAS.MPCedu = PROCIR.MPCedu
											AND CAPBAS.MPTDoc = PROCIR.MPTDoc
                    WHERE		CAST(PROCIR.ProFec AS DATE) BETWEEN '" . $fechaInicial . "'  AND '" . $fechaActual . "'
					AND PROCIR.ProEsta IN (4,5)
					AND PROCIR1.CrgEst = 'S'
					AND PROCIR.ProVivo = 2
	            	ORDER BY	PROCIR1.CrgCod DESC
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) $resultado[] = [
                    'Documento' => trim($result->DOCUMENTO),
                    'TipoDoc' => trim($result->TIPO_DOC),
                    'Paciente' => trim($result->PACIENTE),
                    'Procedimiento' => trim($result->PROCEDIMIENTO_COD),
                    'NombreProcedimiento' => ($result->NOMB_PROCED),
                    'Estado' => trim($result->ESTADO)
                ];


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
    public function getsurgeryEfficiency(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {
            /*
            if ($fechaInicial == '' || $fechaActual == '') {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            
            $datetime1 = date_create($fechaInicial);
            $datetime2 = date_create($fechaActual);

            $contador = date_diff($datetime1, $datetime2);
            $differenceFormat = '%a';

            */
            $fechaInicial = date("Y-m") . '-01';
            $fechaActual = date('Y-m-d');

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorizedd',
                'status' => 401
            ]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */

            try {


                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    SELECT MAEPRO.PrNomb,p.MPCedu,CAPBAS.MPNOMC PACIENTE,p.ProEsta,p.ProHorI,p.ProHorF,p.ProDurHr ,p.ProDurMn ,d.HorIniCir,d.HorFinCir from PROCIR p
		
		            right join DESCIRMED d on d.CodCir= p.ProCirCod
		
		            INNER JOIN CAPBAS ON	CAPBAS.MPCedu = p.MPCedu
											AND CAPBAS.MPTDoc = p.MPTDoc
					INNER JOIN PROCIR1  ON	PROCIR1.ProEmpCod = p.ProEmpCod
											AND PROCIR1.ProMCDpto = p.ProMCDpto
											AND PROCIR1.ProCirCod = p.ProCirCod							
					INNER JOIN MAEPRO ON PROCIR1.CrgCod = MAEPRO.PrCodi
		            WHERE CAST(p.ProFec AS DATE) ='2023-03-28'
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                $resultado = [];

                foreach ($query as $result) {

                    $horaInicio = new DateTime($result->ProHorI);
                    $horaTermino = new DateTime($result->ProHorF);

                    $horaInicio2 = new DateTime($result->HorIniCir);
                    $horaTermino2 = new DateTime($result->HorFinCir);

                    $intervalP = date_diff($horaInicio, $horaTermino);
                    $intervalE = date_diff($horaInicio2, $horaTermino2);

                    $minutesP = $intervalP->days * 24 * 60;
                    $minutesP += $intervalP->h * 60;
                    $minutesP += $intervalP->i;

                    $minutesE = $intervalE->days * 24 * 60;
                    $minutesE += $intervalE->h * 60;
                    $minutesE += $intervalE->i;


                    $resultado[] = [
                        'paciente' => trim($result->PACIENTE),
                        'cedula' => trim($result->MPCedu),
                        'procedimiento' => trim($result->PrNomb),
                        'minutosProgramados' => $minutesP,
                        'minutosEjecutados' => $minutesE,
                        'eficiencia' => ($minutesP*100)/$minutesE
                    ];
                    
                }
                /*
                $resultado[] = [
                    'Documento' => trim($result->DOCUMENTO),
                    'TipoDoc' => trim($result->TIPO_DOC),
                    'Paciente' => trim($result->PACIENTE),
                    'Procedimiento' => trim($result->PROCEDIMIENTO_COD),
                    'NombreProcedimiento' => ($result->NOMB_PROCED),
                    'Estado' => trim($result->ESTADO)
                ];
                 */
                if (count($resultado) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);
               


                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => 1,
                    'data' => $resultado
                ], 200);


                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
}
