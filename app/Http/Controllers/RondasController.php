<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RondasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    public function getInformacionEmpleados(Request $request, $cedula = '')
    {
       
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);


            if ($cedula == '') {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }

            try {

                
                $query = DB::connection('sqlsrv_kactusprod')
                    ->select("
                    
                    SELECT RTRIM(NM_CONTR.cod_empl) DOC
                    ,bi_emple.tip_docu TIP_DOC
                    ,RTRIM(bi_emple.nom_empl) Nombre
                    ,RTRIM(bi_emple.ape_empl) Apellidos
                    ,bi_emple.sex_empl sexo
                    ,bi_emple.eee_mail Email
                    ,bi_emple.tel_movi Telefono
                    ,RTRIM(bi_emple.tel_resi) Direccion
                    ,cast(bi_emple.fec_naci as date) Fecha_Nacimiento
                    ,rtrim(b2.nom_empl) +' '+rtrim(b2.ape_empl) AS JEFE_INMEDIATO
                    ,bi_cargo.cod_carg CARGO_COD
                    ,RTRIM(bi_cargo.nom_carg) Cargo
                    ,RTRIM(gn_ccost.nom_ccos) AS CENTRO_COSTO
                    ,(SELECT MAX(NC.FEC_CONT) FROM NM_CONTR AS NC WHERE NC.cod_empl = bi_emple.cod_empl AND NC. ind_acti = 'A' group by NC.FEC_CONT) FECHA_INI_ULT_CONTRATO
                    ,(SELECT MAX(NC.FEC_VENC) FROM NM_CONTR AS NC WHERE NC.cod_empl = bi_emple.cod_empl AND NC. ind_acti = 'A' group by NC.FEC_VENC) FECHA_VENC_ULT_CONTRATO
                    ,RTRIM(NM_CONTR.ind_acti) ESTADO_EMPLEADO
                    --,bi_foemp.fot_empl FOTO_EMPLEADO
                   FROM bi_emple
                               INNER JOIN NM_CONTR ON	bi_emple.cod_empl  = nm_contr.cod_empl
                                                         --AND ind_acti = @empStatus
                                                         --AND ind_acti = 'A' PIDIERON QUITAR LA VALIDACIÓN PARA EMPLEADOS ACTIVOS
                               LEFT JOIN gn_ccost ON	gn_ccost.cod_ccos = NM_CONTR.cod_ccos
                               LEFT JOIN BI_CARGO ON	bi_cargo.cod_carg = NM_CONTR.cod_carg
                               LEFT JOIN bi_emple B2 ON	B2.cod_empl = nm_contr.cod_frep
                               --INNER JOIN bi_FOEMP ON bi_emple.cod_empl = bi_foemp.cod_empl
                     where NM_CONTR.cod_empl = ".$cedula);
                /*

                $query = DB::connection('pgsql_bonna_comunity')
                    ->select("select * from invf_productos_estantes");
                */
                $resultado = [];

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

    public function getLugarEstanciaActual(Request $request, $cedula = ''){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);


            if ($cedula == '') {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }

            try {

                
                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                    select RTRIM(m.MPNomP) as pabellon, RTRIM (m1.MPUced) as cedula , RTRIM(p.MPTDoc)AS tipo_doc ,
                    RTRIM(m1.MPNumC) as  cama, CONCAT(RTRIM(p.MPNom1)
                    ,' ' ,RTRIM(p.MPNom2),' ',RTRIM(p.MPApe1),' ',RTRIM(p.MPApe2)) name_paciente   from MAEPAB1 m1
                    left join MAEPAB m on m1.MPCodP = m.MPCodP
                    left join CAPBAS p on p.MPCedu =m1.MPUced 
                    where p.MPCedu = '".$cedula."' order by m1.MPCodP ");
                
                /*
                $query = DB::connection('pgsql_bonna_comunity')
                    ->select("select * from invf_productos_estantes");
                */
              

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
    public function getDatosBasicosPaciente(Request $request, $cedula = '', $tipo_doc = ''){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);


            if ($cedula == '') {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }

            try {

                
                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                    select DISTINCT 
                    Rtrim(MPNOMC) NOMBRE,
                    substring(INGRESOS.MPCedu,1,LEN(INGRESOS.MPCedu)) as ID,
                    Rtrim(INGRESOS.MPTDoc) TIPO,
                    Rtrim(MpMail) CORREO,
                    Rtrim(MPDire) DIRECCION,
                    Rtrim(EmpDsc) EMPRESA,
                    Rtrim(MPTele) TELEFONO,
                    Rtrim(MPTele2) CELULAR,
                    Rtrim(NivEdu.NivEdDsc) NIVEL_EDUCATIVO,
                    CAPBAS.MPFchN FECHA_NACIMIENTO

                    from CAPBAS
                    INNER JOIN INGRESOS on (INGRESOS.MPCedu = CAPBAS.MPCedu and INGRESOS.MPTDoc = CAPBAS.MPTDoc)
                    LEFT join NivEdu on CAPBAS.MPNivEdu = NivEdu.NivEdCo
                    LEFT join MAEEMP on (INGRESOS.IngNit = MAEEMP.MENNIT)
                    LEFT join EMPRESS on (MAEEMP.MEcntr = EMPRESS.MEcntr)
                    LEFT JOIN MAEPAC ON INGRESOS.MPCEDU=MAEPAC.MPCEDU AND INGRESOS.INGNIT=MAEPAC.MENNIT

                    where MPEstPac='S' and INGRESOS.MPCedu='".$cedula."' and INGRESOS.MPTDoc='".$tipo_doc."'
                    and IngCsc=(select MAX(IngCsc) FROM INGRESOS I 
                    WHERE CAPBAS.MPCedu = MPCedu and CAPBAS.MPTDoc = MPTDoc)

                    ");
                
                /*
                $query = DB::connection('pgsql_bonna_comunity')
                    ->select("select * from invf_productos_estantes");
                */
              

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
    public function getCensoReal(Request $request){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);


            
            try {

                
                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                        SELECT * FROM CENSOREAL() ORDER BY PABELLON, CAMA

                    ");
                /*
                    $result=[];
                
                foreach ($query as $qu) {
                   
                    if($qu->COD_PAB==43){
                        
                        $result[]=$qu;

                    }
                    
                }
              
                */
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

    public function getMedicosEspecialistas(Request $request){
        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);


            
            try {

                
                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                    SELECT RTRIM(me.MMCedM) as cedula,RTRIM(me.MMNomM) AS nombre,RTRIM(esp.MENomE) as especialidad, esp.MECodE
                    FROM MAEMED m
                    left JOIN MaeMed1 me on me.MMCODM = m.MMCODM
                    left JOIN MAEESP esp on esp.MECodE = m.MECodE
                    where m.MEEstE = 'S' and me.MMEstado='A' and esp.MECodE NOT IN (7,42,43,52,90,220,391)
                    ORDER BY me.MMNomM,esp.MECodE

                    ");
               
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

    public function dataqsystem(Request $request){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            
            try {
                
                $query1 = DB::connection('mysql_qsystem')
                    ->select("
                    
                    SELECT psm.SMA_ID ,psm.SMA_SERVICIO  from pim_servicio_mantenimiento psm ORDER BY psm.SMA_SERVICIO ASC

                    ");

                    $query2 = DB::connection('mysql_qsystem')
                    ->select("
                    
                    SELECT pts.TSO_ID ,pts.TSO_TIPO_SOLICITUD, pts.PIM_SERVICIO_MANTENIMIENTO_SMA_ID from pim_tipo_solicitud pts ORDER BY pts.TSO_TIPO_SOLICITUD

                    ");

                    $ubiServicio = DB::connection('mysql_qsystem')
                    ->select("
                    
                    SELECT DISTINCT(pu.UBI_SERVICIO) FROM pim_ubicacion pu WHERE UBI_BORRADO = 0

                    ");

                    $ubiUbicacio = DB::connection('mysql_qsystem')
                    ->select("
                    
                    SELECT pu.UBI_ID ,pu.UBI_SERVICIO,pu.UBI_UBICACION  FROM pim_ubicacion pu 

                    ");
                
                    $data=["pim_servicios"=>$query1,"pim_tipo_de_solicitud"=>$query2,"ubiServicio"=>$ubiServicio,"ubiUbicacio"=>$ubiUbicacio];
                    
                if (sizeof($query1) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                if (count($query1) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Array',
                    'status' => 204,
                    'data' => []
                ], 204);
                

                return response()->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'count' => count($data),
                    'data' => $data
                ], 200);

                //
            } catch (\Throwable $th) {
                throw $th;
            }
        }

    }

    public function getTipoSolicitud(Request $request,$servicio_id){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            
            try {
                
                $query = DB::connection('mysql_qsystem')
                    ->select("
                    
                    SELECT pts.TSO_ID ,pts.TSO_TIPO_SOLICITUD from pim_tipo_solicitud pts where pts.PIM_SERVICIO_MANTENIMIENTO_SMA_ID=".$servicio_id."

                    ");


               
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

    //--------------------------------



    public function getChatbotCitas(Request $request){

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            
            try {
                /*
                $query = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                    SELECT RTRIM(p.MPNom1) as nom1 ,RTRIM(p.MPNom2) as nom2 ,RTRIM(p.MPApe1) ape1 ,RTRIM(p.MPApe2) ape2 ,cita.CitNum as numCita, cita.CitFchHra as fecha, RTRIM(cita.CitCed) cedula 

                    FROM CTRLCITAS cita LEFT JOIN CAPBAS p on p.MPCedu=cita.CitCed and p.MPTDoc = cita.CitTipDoc

                    WHERE cita.CitFchHra > '2024-02-20 00:00:00.000' and cita.CitCmbDto = 'ATENDIDA'

                    ");
                
                    
                foreach ($query as $citas) {
                         
                    $dato1 = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                     SELECT HCDieDsc FROM HCCOM44 where HISCKEY = '".$citas->numCita."'
                    
                    ");
                    //$citas->data=$dato1;    
                        
                   
                    $dato2 = DB::connection('sqlsrv_hosvital')
                    ->select("
                    
                    select HISCENFACT from HCCOM1  WHERE HISCKEY= '".$citas->cedula."' and HisCitNum='".$citas->numCita."'
                   
                    ");
                    
                    $citas->dato1=$dato1;
                    $citas->dato2=$dato2;
                   
                }
                
                    
                    */
                $query[] =  array(
                        "name" => 'Martha qqq',
                        "identificacion"=> '333333',
                        "observacion"=> 'servervev',
                        "status"=> 'Inactivo',
                    );
                    
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

    public function obtenerOrdenes(Request $request,$num_order,$token){
        
        try {

        $ordertoken = DB::connection('pgsql_bonna_comunity_interno')
        ->select(
            "SELECT * from pporder_autorizaciones where tokens_autorizaciones='".$token."' and orden_id='".$num_order."'" 
        );
        
        if(count($ordertoken)==0) return response()->json([
            'msg' => 'N° de orden no valida',
            'status' => 400,
            'count' => 0,
            'data' => []
        ], 400);
              

        $data = DB::connection('sqlsrv_hosvital')
        ->select(
            "SELECT *  from BONNACOMMUNITY_ORDENES_DE_COMPRA_FARMACIA_NROORDEN('" . $num_order . "')"
        );

        foreach($data as $dt){

            $detalle = DB::connection('sqlsrv_hosvital')
            ->select(
                "SELECT * from BONNACOMMUNITY_ORDENES_DE_COMPRA_FARMACIA_DETALLE( '" . $num_order . "')"
            );
            
            $dt->detalle=$detalle ;

        }

        return response()->json([
            'msg' => 'Ok',
            'status' => 200,
            'count' => count($data),
            'data' => $data
        ], 200);

    } catch (\Throwable $th) {
        return $th;
    }

    }
}
