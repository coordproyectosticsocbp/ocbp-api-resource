<?php

namespace App\Http\Controllers\TorreControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

class Urgencias extends Controller
{
    public function getTriageCount(Request $request)
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
            $fechaInicial=date("Y-m").'-1';
            $fechaActual = date('Y-m-d');
            $hora = date("d-m-Y h:i:s");

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
                    SELECT	HCCOM1.HISCKEY DOCUMENTO
		          , HCCOM1.HISTipDoc TIPO_DOC
            		, CAPBAS.MPNOMC PACIENTE
                		, DATEDIFF (YEAR,CAPBAS.MPFchN, GETDATE()) EDAD
	        	, INGRESOS.IngCsc INGRESOS
	        	, HCCOM1.HISCLTR CLASIFICACION_TRIAGE
        		, HCCOM1.HisFhorAt FECHA_DE_ATENCION
	        	, MAEDIA.DMCodi CODIGO_DIAGNOSTICO
	        	, MAEDIA.DMNomb DIAGNOSTICO
	        	, EMPRESS.EmpDsc EPS
	        	, MAEEMP.MENOMB CONTRATO
        		,CAPBAS.MPSexo SEXO
                FROM HCCOM1
		        INNER JOIN INGRESOS ON	INGRESOS.MPCedu = HCCOM1.HISCKEY
								AND INGRESOS.MPTDoc = HCCOM1.HISTipDoc
								AND INGRESOS.IngCsc = HCCOM1.HCtvIn1
		        INNER JOIN CAPBAS ON	CAPBAS.MPCEDU = HCCOM1.HISCKEY
								AND CAPBAS.MPTDoc = HCCOM1.HISTipDoc
		        LEFT JOIN MAEDIA ON	MAEDIA.DMCodi = INGRESOS.IngEntDx
		        INNER JOIN MAEEMP ON MAEEMP.MENNIT = INGRESOS.INGNIT
		        INNER JOIN EMPRESS ON EMPRESS.MEcntr = MAEEMP.MEcntr
                WHERE	CAST(HCCOM1.HisFhorAt AS DATE) between '".$fechaInicial."' AND '".$fechaActual."'
		        AND HCCOM1.HISCLTR <> 0
                ORDER BY HCCOM1.HISCKEY, INGRESOS.IngCsc ASC
                
                    ");

                //return $query;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) $resultado[] = [
                    'Documento' => trim($result->DOCUMENTO),
                    'TipoDoc' => trim($result->TIPO_DOC),
                    'Nombre' => trim($result->PACIENTE),
                    'Edad' => intval($result->EDAD),
                    'Sexo' => trim($result->SEXO),
                    'Eps' => trim($result->EPS),
                    'Contrato' => trim($result->CONTRATO),
                    'Triage' => intval($result->CLASIFICACION_TRIAGE),
                    'FechaDeAtencion' => $result->FECHA_DE_ATENCION,
                    'Diagnostico' => trim($result->DIAGNOSTICO),
                    'horaActual'=>$hora
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
    public function getReEntryUrgency(Request $request)
    {
        //if ($request->hasHeader('X-Authorization')) {
            /*
            if ($fechaInicial == ''
             //|| $fechaActual == ''
             ) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            */
            $fechaInicial = date('Y-m-d');
            //$fechaInicial='2023-04-11';

            $fechaAtras= date("Y-m-d",strtotime($fechaInicial."- 4 days")); 
            $fechaAnteriorAlActual= date("Y-m-d",strtotime($fechaInicial."- 1 days")); 
            
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
                    SELECT	HCCOM1.HISCKEY DOCUMENTO
		            , HCCOM1.HISTipDoc TIPO_DOC
            		, CAPBAS.MPNOMC PACIENTE
                	, DATEDIFF (YEAR,CAPBAS.MPFchN, GETDATE()) EDAD
	        	    , INGRESOS.IngCsc INGRESOS
	        	    , HCCOM1.HISCLTR CLASIFICACION_TRIAGE
        		    , HCCOM1.HisFhorAt FECHA_DE_ATENCION
	        	    , MAEDIA.DMCodi CODIGO_DIAGNOSTICO
	        	    , MAEDIA.DMNomb DIAGNOSTICO
	        	    , EMPRESS.EmpDsc EPS
	        	    , MAEEMP.MENOMB CONTRATO
        		    ,CAPBAS.MPSexo SEXO
                    FROM HCCOM1
		            INNER JOIN INGRESOS ON	INGRESOS.MPCedu = HCCOM1.HISCKEY
								AND INGRESOS.MPTDoc = HCCOM1.HISTipDoc
								AND INGRESOS.IngCsc = HCCOM1.HCtvIn1
		            INNER JOIN CAPBAS ON	CAPBAS.MPCEDU = HCCOM1.HISCKEY
								AND CAPBAS.MPTDoc = HCCOM1.HISTipDoc
		            LEFT JOIN MAEDIA ON	MAEDIA.DMCodi = INGRESOS.IngEntDx
		            INNER JOIN MAEEMP ON MAEEMP.MENNIT = INGRESOS.INGNIT
		            INNER JOIN EMPRESS ON EMPRESS.MEcntr = MAEEMP.MEcntr
                    WHERE	CAST(HCCOM1.HisFhorAt AS DATE) BETWEEN '".$fechaAtras."' AND '".$fechaInicial."'
		            AND HCCOM1.HISCLTR <> 0
                    ORDER BY HCCOM1.HISCKEY, INGRESOS.IngCsc ASC
                    ");

                $sw=true;

                $resultado=[];
                $docuTemp='';
                $diagnosticoTemp='';
                $fecha1=new DateTime();

                foreach ($query as $result) {
                  
                    if($sw){
                        $docuTemp=$result->DOCUMENTO;
                        $diagnosticoTemp=$result->CODIGO_DIAGNOSTICO;
                        $fecha1=new DateTime($result->FECHA_DE_ATENCION);
                        $sw=false;
                        
                    }
                    $igualAlreingreso=0;
                   if($result->DOCUMENTO==$docuTemp){

                    $fecha2=new DateTime($result->FECHA_DE_ATENCION); 
                     
                    $intervalo = $fecha1->diff($fecha2);

                    $newDate = date("Y-m-d", strtotime($result->FECHA_DE_ATENCION));

                    if($intervalo->format('%H')>2 && $intervalo->format('%d')<3
                     && $newDate==$fechaInicial
                     || $newDate==$fechaAnteriorAlActual
                     ){
                        if($result->CODIGO_DIAGNOSTICO==$diagnosticoTemp){
                            $igualAlreingreso=1;
                        }
    
                        $resultado[] = [
                            'Documento' => trim($result->DOCUMENTO),
                            'TipoDoc' => trim($result->TIPO_DOC),
                            'Nombre' => trim($result->PACIENTE),
                            'Edad' => ($result->EDAD),
                            'Sexo' => trim($result->SEXO),
                            'Eps' => trim($result->EPS),
                            'Contrato' => trim($result->CONTRATO),
                            'Triage' => $result->CLASIFICACION_TRIAGE,
                            'FechaDeAtencion' => $result->FECHA_DE_ATENCION,
                            'Diagnostico' => trim($result->DIAGNOSTICO),
                            'DiagnosticoIgualAlReingreso' =>  $igualAlreingreso,
                            'Intervalo'=>$intervalo->format('%d Dias %H Horas %i Minutos %s Segundos')
                        ];
                        }
                    }
                    $docuTemp=$result->DOCUMENTO;
                    $diagnosticoTemp=$result->CODIGO_DIAGNOSTICO;
                    $fecha1=new DateTime($result->FECHA_DE_ATENCION);
                }

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
    
}
