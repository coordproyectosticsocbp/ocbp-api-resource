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
                    
                    select RTRIM(m.MPNomP) as pabellon, RTRIM (m1.MPUced) as cedula ,
                    RTRIM(m1.MPNumC) as  cama, CONCAT(RTRIM(p.MPNom1)
                    ,' ' ,RTRIM(p.MPNom2),' ',RTRIM(p.MPApe1),' ',RTRIM(p.MPApe2)) name_paciente   from MAEPAB1 m1
                    left join MAEPAB m on m1.MPCodP = m.MPCodP
                    left join CAPBAS p on p.MPCedu =m1.MPUced 
                    where p.MPCedu = ".$cedula." order by m1.MPCodP ");
                
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
}
