<?php

namespace App\Http\Controllers\Alicanto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function getAuditoria(Request $request){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
          
            /*
            if ($fechaInicial == '' || $fechaActual == '' ) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            */

            try {
                $case=", CASE  WHEN control.state = 0 THEN 'PROGRAMADA'
                WHEN control.state = 1 THEN 'REALIZADA'
                WHEN control.state = 2 THEN 'CANCELADA POR ENTIDAD'
                WHEN control.state = 3 THEN 'CANCELADA POR OCBP'
        END ESTADO
        , case WHEN control.state IN(2, 3) then control.reason
            else ''
        END MOTIVO_CANCELACION
        , CASE  WHEN audits.external = true THEN 'EXTERNA'
                WHEN audits.external = false THEN 'INTERNA'
        END TIPO_AUDITORIA";

                $query = DB::connection('pgsql_bonna_comunity')
                    ->select('
                    
                    SELECT  control.id ID
                    , trim(control.description)  DESCRIPCION
                    , control."startDate" FECHA_EJECUCION
                    , control."endDate" FECHA_FINALIZACION
                    '.$case.'
                    , entity_type."name" TIPO_ENTIDAD
                    , entity.name ENTIDAD
                    , source_oportunity."name" SUBFUENTE
                    , area.name PROCESO
                    , ( SELECT count(noncompliance.activity) FROM noncompliance WHERE noncompliance."auditId" = control.id) AS CANTIDAD_H_I_NC
                    , audits.quantitative_fulfilment  CALIFICACION_CUANTITATIVA
            FROM audits LEFT JOIN control ON audits."controlId" = control.id
                        LEFT JOIN entity ON audits."entityId" = entity.id
                        LEFT JOIN entity_type ON entity_type.id  = entity."typeId" 
                        LEFT JOIN source_oportunity ON source_oportunity.id = audits."sourceId" 
                        LEFT JOIN area ON audits."areaId" = area.id
            where  audits."deletedAt" is NULL and cast(control."startDate" as DATE) between '."'2021-01-01'".' and '."'2023-12-31'".'
                        --LEFT JOIN noncompliance ON audits.id = noncompliance."auditId"
                        
            ORDER BY control."startDate" desc
            
                    
                    ');


                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                if (count($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);


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
    public function getComites(Request $request){
        
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
          
            /*
            if ($fechaInicial == '' || $fechaActual == '' ) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            */

            $case1=", case	when cm.status = 1 then 'PROGRAMADA'
            when cm.status = 2 then 'REALIZADA'
            when cm.status = 3 then 'CANCELADA OCBP'
    end ESTADO_REUNION";

            $case2=", case	when cc.completed = true then 'CERRADO'
            else 'EN PROCESO'
    end ESTADO_COMPROMISO";

            $concat=", CONCAT (u.name, ' ', u.lastname ) RESPONSABLE";
            try {
              

                $query = DB::connection('pgsql_bonna_comunity')
                    ->select('
                    
                    select	c."name" COMITE
		, cm.theme TEMA
		, cm.place LUGAR
		, cm.schedule FECHA'.
		$case1.'
		, cc.compromise COMPROMISO
		'.$concat.''.$case2.'
		, cc.observations OBSERVACION
		, (
			select COUNT(id) from meeting_member mm_s where mm_s."meetingId" = cm.id 
		) as CANTIDAD_CITADA
		, (
			select COUNT(id) from meeting_member mm_s where mm_s."meetingId" = cm.id 
															and mm_s.attended = true
		) as CANTIDAD_ASISTIDA
from committee_meeting cm 	LEFT join committee c on c.id  = cm."committeeId"
							LEFT join committee_compromise cc on cc."meetingId" = cm.id  
							LEFT join committee_member cm2 on cm."committeeId" = cm2."committeeId"
															and cc."attendantId" = cm2.id 
							LEFT join user_details ud on cm2."memberId" = ud.id 
							LEFT join users u on u.id = ud."userId" 
where	c."deletedAt" is null 
		and cm."deletedAt" is null
        and cast(cm.schedule as date) between '."'2023-01-01'".' and '."'2023-12-31'".'
order by cm.schedule DESC
            
                    
                    ');


                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                if (count($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);


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

    public function getAsitenciaComite(Request $request){
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
          
           $case1=", case	when cm.status = 1 then 'PROGRAMADA'
           when cm.status = 2 then 'REALIZADA'
           when cm.status = 3 then 'CANCELADA OCBP'
   end ESTADO_REUNION";

            try {
              

                $query = DB::connection('pgsql_bonna_comunity')
                    ->select('

                    select	c."name" COMITE
		, cm.theme TEMA
		, cm.place LUGAR
		, cm.schedule FECHA
		
        '.$case1.'
		
		, (
			select COUNT(id) from meeting_member mm_s where mm_s."meetingId" = cm.id 
		) as CANTIDAD_CITADA
		, (
			select COUNT(id) from meeting_member mm_s where mm_s."meetingId" = cm.id 
															and mm_s.attended = true
		) as CANTIDAD_ASISTIDA
from committee_meeting cm 	LEFT join committee c on c.id  = cm."committeeId"
							
where	c."deletedAt" is null 
		and cm."deletedAt" is null
		and cast(cm.schedule as date) between '."'2023-01-01'".' and '."'2023-12-31'".'
order by cm.schedule,c."name"  DESC
                    
                    ');


                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                if (count($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);


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
}
