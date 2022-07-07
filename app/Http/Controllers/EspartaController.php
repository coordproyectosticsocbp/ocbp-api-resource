<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspartaController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1/esparta/patient/{patientdoc?}/type/{patientdoctype?}/information",
     *     operationId="initialPatientInfoEsparta",
     *     tags={"Esparta"},
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
     *          name="patientdoc",
     *          description="Documento del Paciente",
     *          required=false,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype",
     *          description="Tipo de Documento del Paciente - CC, TI, RC, PE",
     *          required=false,
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


                if ($patientDoc != "" || $patientTipoDoc != "") {

                    $query = DB::connection('sqlsrv_hosvital')
                        ->select("SELECT * FROM ESPARTA_INFORMACION_PACIENTE('$patientDoc', '$patientTipoDoc')");


                    if (sizeOf($query) > 0) {

                        $records = [];

                        foreach ($query as $item) {

                            $query2 = DB::connection('sqlsrv_hosvital')
                                ->select("SELECT * FROM ESPARTA_INFORMACION_FOLIOS_PACIENTES($item->DOCUMENTO, '$item->TIP_DOC')");

                            if (sizeOf($query2) > 0) {

                                $folios = [];

                                foreach ($query2 as $row) {
                                    $temp2 = array(
                                        'document' => $row->DOCUMENTO,
                                        'documentType' => $row->TIPO_DOC,
                                        'attentionType' => $row->TIPO_ATENCION,
                                        'folio' => $row->FOLIO,
                                        'consulationDate' => $row->FECHA_CONSULTA,
                                        'motConsulation' => mb_convert_encoding(strtoupper($row->MOTIVO_CONSULTA), 'UTF-8', 'UTF-8'),
                                        'currentIllness' => mb_convert_encoding(strtoupper($row->ENFERMEDAD_ACTUAL), 'UTF-8', 'UTF-8'),
                                        'physicalExam' => mb_convert_encoding(strtoupper($row->EXAMEN_FISICO), 'UTF-8', 'UTF-8'),
                                        //'currentIllness' => strtoupper($row->ENFERMEDAD_ACTUAL),
                                        //'physicalExam' => $row->EXAMEN_FISICO,
                                        'systemReview' => $row->RX_SISTEMA,
                                        'doctor' => $row->MEDICO,
                                    );

                                    $folios[] = $temp2;
                                }
                            } else {

                                $folios = [];
                                /* return response()
                                    ->json([
                                        'msg' => 'No hay datos en la respuesta a esta solicitud de folios',
                                        'status' => 500,
                                        'data' => $query2
                                    ]); */
                            }


                            $temp = array(
                                'tipDoc' => $item->TIP_DOC,
                                'document' => $item->DOCUMENTO,
                                'patientCompany' => $item->EMPRESA,
                                'fName' => $item->PRIMER_NOMBRE,
                                'sName' => $item->SEGUNDO_NOMBRE,
                                'fLastname' => $item->PRIMER_APELLIDO,
                                'sLastname' => $item->SEGUNDO_APELLIDO,
                                'birthDate' => $item->FECHA_NAC,
                                'age' => $item->EDAD,
                                'gender' => $item->SEXO,
                                'civilStatus' => $item->ESTADOCIVIL == null ? "" : $item->ESTADOCIVIL,
                                'bloodType' => $item->GRUPO_SANGUINEO,
                                'mobilePhone' => $item->TELEFONO1,
                                'address' => $item->DIRECCION,
                                'state' => $item->DEPARTAMENTO,
                                'city' => $item->MUNICIPIO,
                                'neighborhood' => $item->BARRIO,
                                'occupation' => $item->OCUPACION,
                                'ethnicity' => $item->BARRIO,
                                'educationLevel' => $item->NIVEL_EDUCATIVO,
                                'specialAttention' => $item->ATEN_ESPECIAL,
                                'disability' => $item->DISCAPACIDAD,
                                'populationGroup' => $item->GRUPO_POBLA,
                                'diagnostics_cod' => $item->DX_COD,
                                'diagnostics' => $item->DX,
                                'invoices' => $folios
                            );

                            $records[] = $temp;
                        }

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'status' => 200,
                                'data' => $records
                            ]);
                    } else {

                        return response()
                            ->json([
                                'msg' => 'No hay datos en la respuesta a esta solicitud',
                                'status' => 200
                            ]);
                    }
                } else {

                    return response()
                        ->json([
                            'msg' => 'Parameters Cannot be Empty',
                            'status' => 500
                        ]);
                }
            } else {
                return response()
                    ->json([
                        'msg' => 'Unauthorized',
                        'status' => 401
                    ], 401);
            }
        }
    }
}
