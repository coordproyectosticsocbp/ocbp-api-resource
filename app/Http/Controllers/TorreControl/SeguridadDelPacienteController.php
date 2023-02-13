<?php

namespace App\Http\Controllers\TorreControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;
use function Ramsey\Uuid\v1;

class SeguridadDelPacienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/indicadores/get/SeguridadPaciente/TrazabilidadEgresos",
     *     operationId="getTrazabilidadEgresos",
     *     tags={"Indicadores"},
     *     summary="Get getTrazabilidadEgresos",
     *     description="Returns getTrazabilidadEgresos",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },@OA\Parameter (
     *          name="fechaInicial?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="fechaActual?",
     *          description="Required",
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
    public function getTrazabilidadEgresos(Request $request,$fechaInicial='',$fechaActual='')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) < 0) return response()->json([
                'msg' => 'Unauthorized',
                'status' => 401
            ]);
            // $fechaFinal = '2022-12-12';

            
            //$fechaInicial = date("Y") . '-01-01';
            //$fechaActual = date('Y-m-d');
            
            //$fechaActual='2023-01-12';

            if ($fechaInicial == '' || $fechaActual == '' ) {

                return response()
                    ->json([
                        'msg' => 'Parameters Cannot Be Empty!',
                        'status' => 400
                    ]);
            }
            
            try {


                $query = DB::connection('sqlsrv_fenix')
                    ->select("
                    
                    SELECT eh.id,eh.codigo cedula,eh.fecha fecha,eh.cama, eh.ingreso consecutivoIngreso
                    ,ee.observacion
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=3 and eh2.codigo=eh.codigo order by eh.id DESC) fecha_alta_Especialista
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=3 and eh2.codigo=eh.codigo order by eh.id DESC) medico_alta_Especialista
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=4 and eh2.codigo=eh.codigo order by eh.id DESC) fecha_epicrisis
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=4 and eh2.codigo=eh.codigo order by eh.id DESC) medico_epicrisis
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=5 and eh2.codigo=eh.codigo order by eh.id DESC) fecha_jefe_facturacion
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=5 and eh2.codigo=eh.codigo order by eh.id DESC) medico_jefe_facturacion
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=6 and eh2.codigo=eh.codigo order by eh.id DESC) fecha_admisiones
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=6 and eh2.codigo=eh.codigo order by eh.id DESC) medico_admisiones
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=7 and eh2.codigo=eh.codigo) fecha_boleta_salida
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=7 and eh2.codigo=eh.codigo) medico_boleta_salida
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=8 and eh2.codigo=eh.codigo) fecha_llamda_auxilio_genera
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=8 and eh2.codigo=eh.codigo) medico_llamda_auxilio_genera
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=9 and eh2.codigo=eh.codigo) fecha_llamda_auxilio_clinico
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=9 and eh2.codigo=eh.codigo) medico_llamda_auxilio_clinico
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=10 and eh2.codigo=eh.codigo) fecha_salida_paciente
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=10 and eh2.codigo=eh.codigo) medico_salida_paciente
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=11 and eh2.codigo=eh.codigo) fecha_salida_porteria
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=11 and eh2.codigo=eh.codigo) medico_salida_porteria
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=12 and eh2.codigo=eh.codigo) fecha_llegada_Servicios_generales
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=12 and eh2.codigo=eh.codigo) medico_llegada_Servicios_generales
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=13 and eh2.codigo=eh.codigo) fecha_fin_Servicios_generales
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=13 and eh2.codigo=eh.codigo) medico_fin_Servicios_generales
                    
                    ,(select top 1 CONCAT(eh2.fecha,' ',eh2.hora) from eg_historicocenso eh2 where eh2.disp=14 and eh2.codigo=eh.codigo) fecha_ingreso_paciente
                    ,(select top 1 eh2.usuario_proceso from eg_historicocenso eh2 where eh2.disp=14 and eh2.codigo=eh.codigo) medico_ingreso_paciente
                    
                    from eg_historicocenso eh 
                    left join eg_estados ee on ee.proceso =eh.disp
                    WHERE ee.proceso=11 and fecha between '".$fechaInicial."' and '".$fechaActual."'
                    order by eh.codigo
                    
                    ");

                $resultado=[] ;

                if (sizeof($query) < 0) return response()->json([
                    'msg' => 'Empty Diagnoses Query Response',
                    'status' => 204
                ], 204);


                foreach ($query as $result) {
                    
                    

                        $intvEpicrisis_Especialista = '';
                        $intvFacturacion_Epicrisis = '';
                        $intvAdmisiones_JefeFacturacion='';
                        $intvfacturacion_boletaSalida = '';
                        $intvboletaSalida_admisiones= '';
                        $intvEpicrisis_Especialista= '';
                        $intvsalidaPaciente_altaespecialista= '';
                        $intvSalidapaciente_llamadaauxilioClinico= '';

                        
                        
                        if ($result->fecha_epicrisis != '' && $result->fecha_alta_Especialista !='') {
                            
                            $intvEpicrisis_Especialista=self::sacarDiferenciaEntreFechas($result->fecha_epicrisis,$result->fecha_alta_Especialista);

                        }
                        
                        if ($result->fecha_jefe_facturacion != '' && $result->fecha_epicrisis != '') {

                            $intvFacturacion_Epicrisis=self::sacarDiferenciaEntreFechas($result->fecha_jefe_facturacion,$result->fecha_epicrisis);

                        }
                       
                        if ($result->fecha_admisiones != '' && $result->fecha_jefe_facturacion != '') {
                           
                            $intvAdmisiones_JefeFacturacion=self::sacarDiferenciaEntreFechas($result->fecha_admisiones,$result->fecha_jefe_facturacion);

                        }
                        
                        if ($result->fecha_boleta_salida != '' && $result->fecha_admisiones != '') {

                            $intvboletaSalida_admisiones=self::sacarDiferenciaEntreFechas($result->fecha_boleta_salida,$result->fecha_admisiones);


                        }
                        if ($result->fecha_jefe_facturacion != '' && $result->fecha_boleta_salida != '') {

                            $intvfacturacion_boletaSalida=self::sacarDiferenciaEntreFechas($result->fecha_jefe_facturacion,$result->fecha_boleta_salida);
                            
                        }
                        
                        if ($result->fecha_salida_paciente != '' && $result->fecha_llamda_auxilio_clinico != '') {

                            $intvSalidapaciente_llamadaauxilioClinico=self::sacarDiferenciaEntreFechas($result->fecha_salida_paciente,$result->fecha_llamda_auxilio_clinico);

                        }
                        
                        if ($result->fecha_salida_paciente != '' && $result->fecha_alta_Especialista != '') {

                            $intvsalidaPaciente_altaespecialista=self::sacarDiferenciaEntreFechas($result->fecha_salida_paciente,$result->fecha_alta_Especialista);

                        }
                        
                        $resultado[] = [
                            'id' => trim($result->id),
                            'cedula' => trim($result->cedula),
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

                            'intervaloEpicrisis_Especialista' => $intvEpicrisis_Especialista,
                            'intervalointvFacturacion_Epicrisis' => $intvFacturacion_Epicrisis,
                            'intervalointvadmisiones_JefeFacturacion' => $intvAdmisiones_JefeFacturacion,
                            'intervalointvboletadesalida_admisiones' => $intvboletaSalida_admisiones,
                            //boletadesalida_admisiones^^^^^
                            'intervalointvJefeFacturacion_Boletasalida' => $intvfacturacion_boletaSalida,
                            'intervalointvsalidaPaciente_auxiliarClinico' => $intvSalidapaciente_llamadaauxilioClinico,
                            'intervalointSalidapaciente_especialista' => $intvsalidaPaciente_altaespecialista,
                        ];
                        

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
        }
    }
    private static function sacarDiferenciaEntreFechas($fechaUno,$fechaDos){
        $creacion = new DateTime($fechaUno);
        //$creacion = DateTime::createFromFormat('Y/m/d H:i:s', $fecha_epicrisis);
        //$cierre = DateTime::createFromFormat('Y/m/d H:i:s', $fecha_alta_Especialista);
        $cierre = new DateTime($fechaDos);
        
      
        $intervalo = $cierre->diff($creacion);

        $hora = $intervalo->format('%h');
        $minutos = $intervalo->format('%i');

        $resultado=intval($minutos);
        $hora=$hora-9;
        if($hora>0){
            $min=$hora*60;
            $resultado=$minutos+$min;
        }
        return $resultado;
    }
}
