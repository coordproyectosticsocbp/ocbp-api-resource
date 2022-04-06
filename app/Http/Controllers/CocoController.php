<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CocoController extends Controller
{

    /**
     * @OA\Get (
     *     path="/api/v1/coco/patient/{patientdoc}/type/{patientdoctype}/information",
     *     operationId="initialPatientInfoCoco",
     *     tags={"COCO"},
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
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="patientdoctype",
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
    public function initialPatientInfo(Request $request, $patientDoc, $patientTipoDoc)
    {

        if ($request->hasHeader('X-Authorization')) {

            if ($patientDoc != "" && $patientTipoDoc != "") {

                $query = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM ESPARTA_INFORMACION_PACIENTE('$patientDoc', '$patientTipoDoc')");


                if (sizeOf($query) > 0) {

                    $records = [];

                    foreach ($query as $item) {
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
                            'civilStatus' => $item->ESTADOCIVIL,
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
                        'msg' => 'Error en el envio de los Parametros a la Solicitud',
                        'status' => 500
                    ]);
            }
        }
    }
}
