<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

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
                    //->select("SELECT * FROM NOMINA_BD_EMPLEADOS_ACTIVOS() WHERE CARGO LIKE '%AUXILIARES CLINICOS%' AND ESTADO_EMPLEADO = 'A' ORDER BY Nombre");
                    ->select("SELECT * FROM NOMINA_BD_EMPLEADOS_ACTIVOS() ORDER BY Nombre");

                if (count($query_employees) > 0) {

                    $employees = [];
                    $employeeStatus = "";

                    foreach ($query_employees as $employee) {

                        /* if ($employee->ESTADO_EMPLEADO === 'A') {
                            $employeeStatus = 1;
                        } else if ($employee->ESTADO_EMPLEADO === 'I') {
                            $employeeStatus = 0;
                        } */


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
                            'empImmediateBossDocument' => $employee->DOC_JEFE_INMEDIATO,
                            'empImmediateBossDocumentType' => $employee->TIPODOC_JEFE_INMEDIATO,
                            'empImmediateBoss' => $employee->JEFE_INMEDIATO,
                            'empImmediateBossEmail' => $employee->EMAIL_JEFE_INMEDIATO,
                            'empImmediateBossPhone' => $employee->TELEFONO_JEFE_INMEDIATO,
                            'empPosition' => $employee->Cargo,
                            'empCostCenter' => $employee->CENTRO_COSTO,
                            'empLastContractInitDate' => $employee->FECHA_INI_ULT_CONTRATO,
                            'empLastContractExpDate' => $employee->FECHA_VENC_ULT_CONTRATO,
                            'empStatus' => $employee->ESTADO_EMPLEADO,
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

                                $marcacion = explode(' ', $biometric_mark->MARCACION);
                                $fecha = $marcacion[0];
                                $hora = $marcacion[1];

                                $tempBiometricMarks = array(
                                    'employeeDocument' => $biometric_mark->CEDULA,
                                    'employeeName' => $biometric_mark->NOMBRE,
                                    'employeeLastName' => $biometric_mark->APELLIDO,
                                    'employeeMarkDate' => $fecha,
                                    'employeeMarkHour' => $hora,
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

    /**FUNCIÓN PARA AGRUPAR PROPIEDADES POR CUALQUIER LLAVE */
    function array_group_by(array $array, $key)
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
            trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
            return null;
        }

        $func = (!is_string($key) && is_callable($key) ? $key : null);
        $_key = $key;

        // Load the new array, splitting by the target key
        $grouped = [];
        foreach ($array as $value) {
            $key = null;

            if (is_callable($func)) {
                $key = call_user_func($func, $value);
            } elseif (is_object($value) && property_exists($value, $_key)) {
                $key = $value->{$_key};
            } elseif (isset($value[$_key])) {
                $key = $value[$_key];
            }

            if ($key === null) {
                continue;
            }

            $grouped[$key][] = $value;
        }

        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();

            foreach ($grouped as $key => $value) {
                $params = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $params);
            }
        }

        return $grouped;
    }


    /**
     * @OA\Get (
     *     path="/api/v1/nomina/get/immediate-bosses",
     *     operationId="get Immediate Bosses",
     *     tags={"Nomina"},
     *     summary="get Immediate Bosses",
     *     description="Returns Immediate Bosses",
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
    public function getAllImmediateBoss(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                try {

                    $queryImmediateBoss = DB::connection('sqlsrv_kactusprod')
                        ->select("SELECT * FROM NOMINA_JEFES_INMEDIATOS()");

                    $arrayIB = json_decode(json_encode($queryImmediateBoss), true);

                    if (count($queryImmediateBoss) > 0) {

                        $records = [];


                        foreach ($arrayIB as $row) {
                            $records[$row['DOC_JEFE_INMEDIATO']]['cc'] = $row['DOC_JEFE_INMEDIATO'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['tipDoc'] = $row['TIP_DOC_JEFE_INMEDIATO'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['name'] = $row['NOMBRE_JEFE_INMEDIATO'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['lastName'] = $row['APELLIDO_JEFE_INMEDIATO'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['gender'] = $row['SEXO'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['email'] = $row['EMAIL'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['phone'] = $row['TELEFONO'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['address'] = $row['DIRECCION'];
                            $records[$row['DOC_JEFE_INMEDIATO']]['status'] = $row['ESTADO_EMPLEADO'];

                            $records[$row['DOC_JEFE_INMEDIATO']]['costCenter'][] = array('center' => $row['CENTRO_COSTO']);
                        }

                        return response()
                            ->json([
                                'msg' => 'Empty Immediate Boss Array',
                                'data' => $records,
                                'status' => 200
                            ]);
                    } else {

                        return response()
                            ->json([
                                'msg' => 'Empty Immediate Boss Array',
                                'data' => [],
                                'status' => 204
                            ]);
                    }
                } catch (\Throwable $e) {

                    throw $e;
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
     *     path="/api/v1/nomina/get/immediate-bosses-by-document/{document?}",
     *     operationId="get Immediate Bosses By Document",
     *     tags={"Nomina"},
     *     summary="get Immediate Bosses By Document",
     *     description="Returns Immediate Bosses By Document",
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
     *          description="Required Document",
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
    public function getAllImmediateBossByDocument(Request $request, $document = '')
    {
        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                // VALIDACIÓN SI EL DOCUMENTO Y EL TIPO DE DOCUMENTO NO SE ENVIAN COMO PARAMETROS
                // POR SOLICITUD DEL PROVEEDOR SE QUITA EL TIPO DE DOCUMENTO COMO PARAMETRO 22-03-2022 07:30
                if (!$document) {

                    // RESPUESTA PARA ESTE CASO
                    return response()
                        ->json([
                            'status' => 400,
                            'message' => 'Parameters cannot be empty'
                        ], 400);
                } else {

                    // MANEJO DE EERORES EN CASO DE QUE EL DOCUMENTO EXISTA
                    try {

                        // QUERY PARA OBTENER EL JEFE INMEDIATO POR DOCUMENTO
                        $queryImmediateBoss = DB::connection('sqlsrv_kactusprod')
                            ->select("SELECT * FROM NOMINA_JEFES_INMEDIATOS() WHERE DOC_JEFE_INMEDIATO = '$document'");

                        // CONVERTIR EL RESULTADO DE LA CONSULTA A JSON
                        $arrayIB = json_decode(json_encode($queryImmediateBoss), true);

                        if (count($queryImmediateBoss) > 0) {

                            $records = [];

                            // RECORRER EL ARREGLO PARA OBTENER LOS DATOS
                            foreach ($arrayIB as $row) {
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossDocument'] = $row['DOC_JEFE_INMEDIATO'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossDocumentType'] = $row['TIP_DOC_JEFE_INMEDIATO'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossName'] = $row['NOMBRE_JEFE_INMEDIATO'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossLastName'] = $row['APELLIDO_JEFE_INMEDIATO'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossGender'] = $row['SEXO'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossEmail'] = $row['EMAIL'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossPhone'] = $row['TELEFONO'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossAddress'] = $row['DIRECCION'];
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossStatus'] = $row['ESTADO_EMPLEADO'];
                                // AQUÍ SE LISTA LOS CENTROS DE COSTO ASOCIADOS A ESTA PERSONA
                                $records[$row['DOC_JEFE_INMEDIATO']]['immediateBossCostCenter'][] = array('center' => $row['CENTRO_COSTO']);
                            }

                            return response()
                                ->json([
                                    'msg' => 'Empty Immediate Boss Array',
                                    'data' => $records,
                                    'status' => 200
                                ]);
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Immediate Boss Array',
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
}
