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

    /**
     * @OA\Get (
     *     path="/api/v1/nomina/get/employees-biometric-marks/{document?}/{initdate?}/{enddate?}",
     *     operationId="get Biometric Marks Information",
     *     tags={"Nomina"},
     *     summary="get Biometric Marks Information",
     *     description="Returns get Biometric Marks Information",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="document?",
     *          description="Employee Document",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="initdate?",
     *          description="Init Date - Format Y-m-d",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *  *     @OA\Parameter (
     *          name="enddate?",
     *          description="End Date - Format Y-m-d",
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
    public function getBiometricMarks(Request $request, $document = '', $initalDate = '', $endingDate = '')
    {

        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if (!$document || !$initalDate || !$endingDate) {

                    return response()
                        ->json([
                            'msg' => 'Parameters Cannot Be Empty',
                            'data' => [],
                            'status' => 400
                        ]);
                } else {

                    try {

                        $initDate = Carbon::parse($initalDate)->format('Y-d-m') . ' 00:00:00';
                        $endDate = Carbon::parse($endingDate)->format('Y-d-m') . ' 23:59:59';

                        $query_biometric_marks = DB::connection('sqlsrv_biometrico')
                            ->select("SELECT * FROM EVA_DES_MARCACIONES('$document', '$initDate', '$endDate') ORDER BY MARCACION");

                        if (count($query_biometric_marks) > 0) {

                            $biometric_marks = [];

                            foreach ($query_biometric_marks as $biometric_mark) {

                                $tempBiometricMarks = array(
                                    'employeeDocument' => $biometric_mark->CEDULA,
                                    'employeeName' => $biometric_mark->NOMBRE,
                                    'employeeLastName' => $biometric_mark->APELLIDO,
                                    'employeeMarkDate' => $biometric_mark->MARCACION,
                                    'employeeMarkType' => $biometric_mark->TIPO,
                                );

                                $biometric_marks[] = $tempBiometricMarks;
                            }

                            if (count($biometric_marks) > 0) {

                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $biometric_marks,
                                    ]);
                            } else {

                                $biometric_marks = [];

                                return response()
                                    ->json([
                                        'msg' => 'Empty biometric marks Array',
                                        'data' => [],
                                        'status' => 204
                                    ]);
                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty biometric marks Query',
                                    'data' => [],
                                    'status' => 204
                                ]);
                        }
                    } catch (\Throwable $e) {

                        throw $e;
                    }
                }
            } else {

                return response()
                    ->json([
                        'status' => 401,
                        'message' => 'Unauthorized'
                    ]);
            }
        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/nomina/get/employees-biometric-marks-by-date-range/{initdate?}/{enddate?}",
     *     operationId="get Biometric Marks Information By Date Range",
     *     tags={"Nomina"},
     *     summary="get Biometric Marks Information By Date Range",
     *     description="Returns get Biometric Marks Information By Date Range",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="initdate?",
     *          description="Init Date - Format Y-m-d",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="enddate?",
     *          description="End Date - Format Y-m-d",
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
    public function getBiometricMarksByDateRange(Request $request, $initalDate = '', $endingDate = '')
    {

        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if (!$initalDate || !$endingDate) {

                    return response()
                        ->json([
                            'msg' => 'Parameters Cannot Be Empty',
                            'data' => [],
                            'status' => 400
                        ]);
                } else {

                    try {

                        $initDate = Carbon::parse($initalDate)->format('Y-d-m') . ' 00:00:00';
                        $endDate = Carbon::parse($endingDate)->format('Y-d-m') . ' 23:59:59';

                        $query_biometric_marks = DB::connection('sqlsrv_biometrico')
                            ->select("SELECT * FROM EVA_DES_MARCACIONES_BY_DATE_RANGE('$initDate', '$endDate') ORDER BY NOMBRE, MARCACION");

                        if (count($query_biometric_marks) > 0) {

                            $biometric_marks = [];

                            foreach ($query_biometric_marks as $biometric_mark) {

                                $tempBiometricMarks = array(
                                    'employeeDocument' => $biometric_mark->CEDULA,
                                    //'employeeName' => $biometric_mark->NOMBRE,
                                    //'employeeLastName' => $biometric_mark->APELLIDO,
                                    'employeeMarkDate' => $biometric_mark->MARCACION,
                                    'employeeMarkType' => $biometric_mark->TIPO,
                                );

                                $biometric_marks[] = $tempBiometricMarks;
                            }

                            if (count($biometric_marks) > 0) {

                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $biometric_marks,
                                    ]);
                            } else {

                                $biometric_marks = [];

                                return response()
                                    ->json([
                                        'msg' => 'Empty biometric marks Array',
                                        'data' => [],
                                        'status' => 204
                                    ]);
                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty biometric marks Query',
                                    'data' => [],
                                    'status' => 204,
                                    'comp' => [
                                        'initDate' => $initDate,
                                        'endDate' => $endDate,
                                    ]
                                ]);
                        }
                    } catch (\Throwable $e) {

                        throw $e;
                    }
                }
            } else {

                return response()
                    ->json([
                        'status' => 401,
                        'message' => 'Unauthorized'
                    ]);
            }
        }
    }
}
