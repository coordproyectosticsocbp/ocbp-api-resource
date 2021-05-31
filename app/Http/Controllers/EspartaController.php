<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspartaController extends Controller
{
    //
    public function __construct() {
        $this->middleware('auth.apikey');
    }

    public function initialPatientInfo(Request $request, $patientDoc, $patientTipoDoc) {

        if ($request->hasHeader('X-Authorization')) {

            //$patientDoc = '84030440';
            //$patientTipoDoc = 'CC';

            if ($patientDoc != "" && $patientTipoDoc != "") {

                $query = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM ESPARTA_INFORMACION_PACIENTE('$patientDoc', '$patientTipoDoc')");

                if (sizeOf($query) > 0) {

                    $records = [];

                    foreach ($query as $item)
                    {
                        $query2 = DB::connection('sqlsrv_hosvital')
                            ->select("SELECT * FROM ESPARTA_INFORMACION_FOLIOS_PACIENTES('$item->DOCUMENTO', '$item->TIP_DOC') ORDER BY FOLIO DESC");

                        if (sizeOf($query2) > 0) {

                            $folios = [];

                            foreach ($query2 as $row) {
                                $temp2 = array(
                                    'document' => $row->DOCUMENTO,
                                    'documentType' => $row->TIPO_DOC,
                                    'kindAttention' => $row->TIPO_ATENCION,
                                    'folio' => $row->FOLIO,
                                    'consulationDate' => $row->FECHA_CONSULTA,
                                    'motConsulation' => $row->MOTIVO_CONSULTA,
                                    'currentIllness' => $row->ENFERMEDAD_ACTUAL,
                                    'physicalExam' => $row->EXAMEN_FISICO,
                                    'systemReview' => $row->RX_SISTEMA,
                                    'doctor' => $row->MEDICO,
                                );

                                $folios[] = $temp2;

                            }

                        } else {

                            return response()
                            ->json([
                                'msg' => 'No hay datos en la respuesta a esta solicitud de folios',
                                'status' => 500,
                            ]);

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
                            'civilStatus' => $item->ESTADOCIVIL,
                            'bloodType' => $item->GRUPO_SANGUINEO,
                            'mobilePhone' => $item->TELEFONO1,
                            'civilStatus' => $item->DIRECCION,
                            'state' => $item->DEPARTAMENTO,
                            'city' => $item->MUNICIPIO,
                            'neighborhood' => $item->BARRIO,
                            'occupation' => $item->OCUPACION,
                            'ethnicity' => $item->BARRIO,
                            'educationLevel' => $item->NIVEL_EDUCATIVO,
                            'specialAttention' => $item->ATEN_ESPECIAL,
                            'disability' => $item->DISCAPACIDAD,
                            'populationGroup' => $item->GRUPO_POBLA,
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
                        'status' => 200,
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
