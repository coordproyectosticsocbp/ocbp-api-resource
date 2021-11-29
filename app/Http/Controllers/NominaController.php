<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    //
    public function __construct()
    {
        return $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1//nomina/get/employees-database",
     *     operationId="Nomina",
     *     tags={"Nomina"},
     *     summary="Get Employees Database With Salary",
     *     description="Returns Get Employees Database With Salary",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
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
    public function getEmployeesDatabaseWithSalary(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            try {

                $query_employees = DB::connection('sqlsrv_kactusprod')
                    ->select("SELECT * FROM NOMINA_BD_EMPLEADOS_ACTIVOS() ORDER BY Nombre");

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
                            'empDoc' => $employee->DOC,
                            'empDocType' => $employee->TIP_DOC,
                            'empName' => $employee->Nombre,
                            'empLastName' => $employee->Apellidos,
                            'empGender' => $employee->sexo,
                            'empEmail' => $employee->Email,
                            'empPhone' => $employee->Telefono,
                            'empAddress' => $employee->Direccion,
                            'empBirthDate' => $employee->Fecha_Nacimiento,
                            'empImmediateBoss' => $employee->JEFE_INMEDIATO,
                            'empPosition' => $employee->Cargo,
                            'empCostCenter' => $employee->CENTRO_COSTO,
                            'empLastContractInitDate' => $employee->FECHA_INI_ULT_CONTRATO,
                            'empLastContractExpDate' => $employee->FECHA_VENC_ULT_CONTRATO,
                            'empStatus' => $employeeStatus,
                            'empSalary' => $employee->SUELDO_BASICO,
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
}
