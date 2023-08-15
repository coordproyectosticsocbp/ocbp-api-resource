<?php

namespace App\Http\Controllers\TorreControl;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Cirugias extends Controller
{
    public function getCirugias(Request $request)
    {

        //if ($request->hasHeader('X-Authorization')) {
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
            //$fechaInicial = date("Y-m") . '-01';
            //$fechaActual = date('Y-m-d');

            //$token = $request->header('X-Authorization');
            //$user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            //if (count($user) < 0) return response()->json([
              //  'msg' => 'Unauthorizedd',
                //'status' => 401
            //]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */

            try {
                $dateIni=new Carbon('last monday');

               
                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    SELECT		PROCIR.MPCedu DOCUMENTO
					, PROCIR.MPTDoc TIPO_DOC
                    , PROCIR.ProFec FECHA
                    ,PROCIR.ProMotCan
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
		            WHERE		CAST(PROCIR.ProFec AS DATE) between '" . $dateIni->format('Y-m-d'). "' AND '" . $dateIni->addDay(7)->format('Y-m-d'). "'
					AND PROCIR.ProEsta IN (3, 4, 5)
					AND PROCIR1.CrgEst = 'S'
		            ORDER BY	PROCIR.ProFec ASC
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) $resultado[] = [
                    'documento' => trim($result->DOCUMENTO),
                    'tipoDoc' => trim($result->TIPO_DOC),
                    'paciente' => trim($result->PACIENTE),
                    'fecha' => trim($result->FECHA),
                    'procedimiento' => trim($result->PROCEDIMIENTO_COD),
                    'nombreProcedimiento' => ($result->NOMB_PROCED),
                    'motivoCancelacion' => ($result->ProMotCan),
                    'estado' => trim($result->ESTADO),
                    'fechaInicialConsulta'=>$dateIni->subDay(6)->format('Y-m-d'),
                    'fechaFinalConsulta'=>$dateIni->addDay(6)->format('Y-m-d')
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
        //}
    }
    public function patientMortality(Request $request)
    {

        //if ($request->hasHeader('X-Authorization')) {
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

            //$token = $request->header('X-Authorization');
            //$user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            //if (count($user) < 0) return response()->json([
              //  'msg' => 'Unauthorizedd',
                //'status' => 401
            //]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */

            $dateIni=new Carbon('last monday');

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
                    WHERE		CAST(PROCIR.ProFec AS DATE) between '" . $dateIni->format('Y-m-d'). "' AND '" . $dateIni->addDay(6)->format('Y-m-d'). "'
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
                    'Estado' => trim($result->ESTADO),
                    'ProFec'=>$result->FECHA
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
        //}
    }
    public function getsurgeryEfficiency(Request $request)
    {
        $dateIni=new Carbon('last monday');
        //if ($request->hasHeader('X-Authorization')) {
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

            //$token = $request->header('X-Authorization');
            //$user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            //if (count($user) < 0) return response()->json([
              //  'msg' => 'Unauthorizedd',
                //'status' => 401
            //]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */

            try {


                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    SELECT 
                    CONSUL.ConsDet quirofano,
                    --MAEPRO.PrNomb,
                    p.ProCons ,p.MPCedu,CAPBAS.MPNOMC PACIENTE,p.ProEsta,p.ProHorI,p.ProHorF,p.ProDurHr ,p.ProDurMn ,d.HorIniCir,d.HorFinCir from PROCIR p
                    
                                LEFT join DESCIRMED d on d.CodCir= p.ProCirCod
                    
                                LEFT JOIN CAPBAS ON	CAPBAS.MPCedu = p.MPCedu
                                                        AND CAPBAS.MPTDoc = p.MPTDoc
                                --INNER JOIN PROCIR1  ON	PROCIR1.ProEmpCod = p.ProEmpCod
                                    --					AND PROCIR1.ProMCDpto = p.ProMCDpto
                                        --				AND PROCIR1.ProCirCod = p.ProCirCod							
                                --LEFT JOIN MAEPRO ON PROCIR1.CrgCod = MAEPRO.PrCodi
                                LEFT JOIN CONSUL ON p.ProCons = CONSUL.ConsCod
                                WHERE CAST(p.ProFec AS DATE) between '" . $dateIni->format('Y-m-d'). "' AND '" . $dateIni->addDay(6)->format('Y-m-d'). "' AND p.ProEsta IN (4,5)
                                ORDER by p.MPCedu ASC
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                $resultado = [];

                foreach ($query as $result) {
                    //return new DateTime('07:54:00');
                    //return  Carbon::createFromDate($result->ProHorI);
                    //$horaInicio = new DateTime($result->ProHorI);
                    //$horaTermino = new DateTime($result->ProHorF);
                      
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

                     if($minutesE==0 ||$minutesP==0){
                        $resultado[] = [
                            'paciente' => trim($result->PACIENTE),
                            'cedula' => trim($result->MPCedu),
                            'minutosProgramados' => $minutesP,
                            'minutosEjecutados' => $minutesE,
                            'eficiencia' => '0'
                        ];
                     }else{
                        $resultado[] = [
                            'paciente' => trim($result->PACIENTE),
                            'cedula' => trim($result->MPCedu),
                            'minutosProgramados' => $minutesP,
                            'minutosEjecutados' => $minutesE,
                            'eficiencia' => round(($minutesP*100)/$minutesE).'%'
                        ];
                     }
                    
                    
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
        //}
    }
    public function getOportunity(Request $request){
        //if ($request->hasHeader('X-Authorization')) {
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
            $fechaActual= date("Y-m-d",strtotime($fechaActual."- 1 days")); 

           // $token = $request->header('X-Authorization');
            //$user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            //if (count($user) < 0) return response()->json([
              //  'msg' => 'Unauthorizedd',
               // 'status' => 401
            //]);
            /*
            if ($contador->format($differenceFormat) > 3) return response()->json([
                'msg' => 'The interval between dates should not be greater than 3 days',
                'status' => 401
            ]);
            */

            try {


                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    select p.ProEsta,count(P.MPCedu) cantidad from PROCIR p where CAST(p.ProFec AS DATE) between '".$fechaInicial."' AND '".$fechaActual."' GROUP BY P.ProEsta 
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                $resultado = [];
                $noRealizado=0;
                $realizado=0;

                foreach ($query as $re) {
                    if($re->ProEsta<=3){
                        $noRealizado=$noRealizado+$re->cantidad;
                    }else{
                        $realizado=$realizado+$re->cantidad;   
                    }
                }

                $resul=($realizado*100)/($realizado+$noRealizado);

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
                    'data' => round($resul).'%'
                ], 200);


                //
            } catch (\Throwable $th) {
                throw $th;
            }
        //}
    }
}
