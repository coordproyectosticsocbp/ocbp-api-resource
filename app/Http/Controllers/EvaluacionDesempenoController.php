<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluacionDesempenoController extends Controller
{
    //

    public function __construct()
    {

        return $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/eva-des/get/employees-database/status/{status?}",
     *     operationId="Evades",
     *     tags={"Evaluacion y Desempeño"},
     *     summary="Get Employees Database",
     *     description="Returns Get Employees Database",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter(
     *        name="status",
     *        in="path",
     *        description="Status",
     *        required=false,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
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
    public function getEmployeesDatabase(Request $request, $empStatus = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            try {

                if ($empStatus === '') {

                    $query_employees = DB::connection('sqlsrv_kactusprod')
                        ->select("SELECT * FROM EVA_DES_BD_EMPLEADOS_ACTIVOS() ORDER BY Nombre, ESTADO_EMPLEADO DESC");
                } else {

                    $query_employees = DB::connection('sqlsrv_kactusprod')
                        ->select("SELECT * FROM EVA_DES_BD_EMPLEADOS_ACTIVOS() WHERE ESTADO_EMPLEADO = '$empStatus' ORDER BY Nombre, ESTADO_EMPLEADO DESC");
                }



                if (count($query_employees) > 0) {

                    $employees = [];
                    $employeeStatus = "";

                    foreach ($query_employees as $employee) {

                        if ($employee->ESTADO_EMPLEADO === 'A') {
                            $employeeStatus = 1;
                        } else if ($employee->ESTADO_EMPLEADO === 'I') {
                            $employeeStatus = 0;
                        }


                        $temp = array(
                            'empDoc' => trim($employee->DOC),
                            'empDocType' => trim($employee->TIP_DOC),
                            'empName' => trim($employee->Nombre),
                            'empLastName' => trim($employee->Apellidos),
                            'empGender' => $employee->sexo,
                            'empEmail' => $employee->Email,
                            'empPhone' => $employee->Telefono,
                            'empAddress' => $employee->Direccion,
                            'empBirthDate' => $employee->Fecha_Nacimiento,
                            'empImmediateBoss' => trim($employee->JEFE_INMEDIATO),
                            'empPosition' => trim($employee->Cargo),
                            'empCostCenter' => trim($employee->CENTRO_COSTO),
                            'empLastContractInitDate' => $employee->FECHA_INI_ULT_CONTRATO,
                            'empLastContractExpDate' => $employee->FECHA_VENC_ULT_CONTRATO,
                            'empStatus' => $employeeStatus,
                            //'empPhoto' => $image,
                        );

                        $employees[] = $temp;
                    }

                    if (count($employees) > 0) {

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'data' => $employees,
                                'status' => 200
                            ]);
                    } else {

                        $employees = [];

                        return response()
                            ->json([
                                'msg' => 'Empty employees Array',
                                'data' => [],
                                'status' => 200
                            ]);
                    }
                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty employees Query',
                            'data' => [],
                            'status' => 200
                        ]);
                }
            } catch (\Throwable $e) {

                throw $e;
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/eva-des/get/novelties-concepts/from/{init?}/{end?}",
     *     operationId="getNoveltiesConcepts",
     *     tags={"Evaluacion y Desempeño"},
     *     summary="Get Novelties Concepts",
     *     description="Returns Novelties Concepts",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="init?",
     *          description="Fecha Inicio para Búsqueda - Opcional - Formato: Y-m-d",
     *          in="path",
     *          required=false,
     *          @OA\Schema (
     *              type="date"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="end?",
     *          description="Fecha Final para Búsqueda - Opcional - Formato: Y-m-d",
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
    public function getNoveltiesConcepts(Request $request, $initalDate = null, $finalDate = null)
    {


        if ($request->hasHeader('X-Authorization')) {

            if ($initalDate === null && $finalDate === null) {

                $initalDate = Carbon::now()->format('Y-m-d');
                $finalDate = Carbon::now()->format('Y-m-d');
            }

            $query_novelties = DB::connection('sqlsrv_kactusprod')
                ->select("SELECT * FROM EVA_DES_NOVEDADES_CONCEPTOS('$initalDate', '$finalDate') ORDER BY FECHA_INICIAL");


            if (count($query_novelties) > 0) {

                $novelties = [];

                foreach ($query_novelties as $novelty) {

                    $temp = array(
                        'empDoc' => trim($novelty->DOC),
                        'empDocType' => trim($novelty->TIP_DOC),
                        'empName' => trim($novelty->NOMBRE),
                        'empLastName' => trim($novelty->APELLIDOS),
                        'empPosition' => trim($novelty->CARGO),
                        'empCostCenter' => trim($novelty->CENTRO_COSTO),
                        'empConceptCode' => trim($novelty->CODIGO_CONCEPTO),
                        'empConceptDesc' => trim($novelty->CONCEPTO),
                        'empConceptInitialDate' => $novelty->FECHA_INICIAL,
                        'empConceptFinalDate' => $novelty->FECHA_FINAL,
                    );

                    $novelties[] = $temp;
                }

                if (count($novelties) > 0) {

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $novelties,
                        ]);
                } else {

                    return response()
                        ->json([
                            'msg' => 'Empty Novelties Array',
                            'status' => 200,
                            'data' => [],
                        ]);
                }
            } else {

                return response()
                    ->json([
                        'msg' => 'Ok',
                        'status' => 200,
                        'data' => [],
                    ]);
            }
        }
    }
}
