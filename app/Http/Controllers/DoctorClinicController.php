<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorClinicController extends Controller
{
    //
    public function __construct()
    {
        return $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1/doctor-clinic/patient/{patientdoc?}/type/{patientdoctype?}/information",
     *     operationId="initialPatientInfo",
     *     tags={"Doctor Clinic"},
     *     summary="Get Patient Informations",
     *     description="Returns Patient Information",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="patientdoc?",
     *          description="Documento del Paciente",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype?",
     *          description="Tipo de Documento del Paciente - CC, TI, RC, PE",
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
    public function initialPatientInfo(Request $request, $patientDoc = '', $patientTipoDoc = '')
    {

        if ($request->hasHeader('X-Authorization')) {

            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {
                try {

                    if ($patientDoc != "" && $patientTipoDoc != "") {

                        $query = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM DOCTOR_CLINIC_INFORMACION_PACIENTE('$patientDoc', '$patientTipoDoc')");


                        if (sizeOf($query) > 0) {

                            $records = [];
                            $contactInfo = '';

                            foreach ($query as $item) {

                                $contactInfo = [
                                    'email' => $item->EMAIL,
                                    'phoneNumber' => trim($item->TELEFONO1),
                                    'address' => trim($item->DIRECCION),
                                    'city' => trim($item->MUNICIPIO),
                                    'state' => trim($item->DEPARTAMENTO),
                                    'country' => null,
                                ];

                                $temp = array(
                                    'name' => $item->PRIMER_NOMBRE,
                                    'secondName' => $item->SEGUNDO_NOMBRE,
                                    'lastName' => $item->PRIMER_APELLIDO,
                                    'secondLastName' => $item->SEGUNDO_APELLIDO,
                                    'documentNumber' => $item->DOCUMENTO,
                                    'age' => $item->EDAD,
                                    'gender' => $item->SEXO,
                                    'dateBirth' => $item->FECHA_NAC,
                                    'state' => $item->DEPARTAMENTO,
                                    'city' => $item->MUNICIPIO,
                                    'bloodType' => $item->GRUPO_SANGUINEO,
                                    'maritalStatus' => $item->ESTADOCIVIL,
                                    'healthProvider' => $item->EMPRESA,
                                    'documentType' => $item->TIP_DOC,
                                    'informationContac' => $contactInfo
                                );

                                $records[] = $temp;
                            }

                            if (count($records) > 0) {
                                return response()
                                    ->json([
                                        'msg' => 'Ok',
                                        'status' => 200,
                                        'data' => $records
                                    ], 200);
                            } else {
                                return response()
                                    ->json([
                                        'status' => 204,
                                        'message' => 'Empty User Array',
                                        'data' => []
                                    ]);
                            }
                        } else {

                            return response()
                                ->json([
                                    'msg' => 'Empty Query Response',
                                    'status' => 200
                                ]);
                        }
                    } else {
                        return response()
                            ->json([
                                'msg' => 'Parameters cannot be Empty',
                                'status' => 500
                            ]);
                    }
                } catch (\Throwable $e) {
                    throw $e;
                }
            }
        }
    }
}
