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

                $resultado = [];

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);

                /*
                foreach ($query as $result) {

                    $resultado[] = [
                        'DOC' => trim($result->DOC),
                        'TIPO_DOC' => trim($result->TIPO_DOC),
                        'fecha' => trim($result->fecha),
                        'Cama' => trim($result->cama),
                        'ConsecutivoIngreso' => trim($result->consecutivoIngreso),
                        'epicrisis' => ['fecha' => $result->fecha_epicrisis, 'medico' => trim($result->medico_epicrisis)],
                        'alta_Especialista' => ['fecha' => $result->fecha_alta_Especialista, 'medico' => trim($result->medico_alta_Especialista)],
                        'jefe_facturacion' => ['fecha' => $result->fecha_jefe_facturacion, 'medico' => trim($result->medico_jefe_facturacion)],
                        'admisiones' => ['fecha' => $result->fecha_admisiones, 'medico' => trim($result->medico_admisiones)],
                        'boleta_salida' => ['fecha' => $result->fecha_boleta_salida, 'medico' => trim($result->medico_boleta_salida)],
                        'llamda_auxilio_general' => ['fecha' => $result->fecha_llamda_auxilio_genera, 'medico' => trim($result->medico_llamda_auxilio_genera)],
                        'llamda_auxilio_clinico' => ['fecha' => $result->fecha_llamda_auxilio_clinico, 'medico' => trim($result->medico_llamda_auxilio_clinico)],
                        'salida_paciente' => ['fecha' => $result->fecha_salida_paciente, 'medico' => trim($result->medico_salida_paciente)],
                        'salida_porteria' => ['fecha' => $result->fecha_salida_porteria, 'medico' => trim($result->medico_salida_porteria)],
                        'llegada_Servicios_generales' => ['fecha' => $result->fecha_llegada_Servicios_generales, 'medico' => trim($result->medico_llegada_Servicios_generales)],
                        'fin_Servicios_generales' => ['fecha' => $result->fecha_fin_Servicios_generales, 'medico' => trim($result->medico_fin_Servicios_generales)],
                        'ingreso_paciente' => ['fecha' => $result->fecha_ingreso_paciente, 'medico' => trim($result->medico_ingreso_paciente)],

                    ];
                }
                */

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
