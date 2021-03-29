<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntelOptionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    public function getDataUsersPopulation(Request $request, $age)
    {
        if ($request->hasHeader('X-Authorization'))
        {

            if ($age)
            {

                if ($age >= 50)
                {
                    $query = DB::connection('sqlsrv_hosv')
                        ->select(
                            DB::raw("SELECT TOP(100) * FROM DATOS_DEMOGRAFICOS('$age')")
                        );

                    if (count($query ) > 0)
                    {

                        $records = [];

                        foreach ($query as $item)
                        {

                            $temp = array(
                                'tipDoc' => $item->TIP_DOC,
                                'documento' => $item->DOCUMENTO,
                                'pNombre' => $item->PRIMER_NOMBRE,
                                'sNombre' => $item->SEGUNDO_NOMBRE,
                                'pApellido' => $item->PRIMER_APELLIDO,
                                'sApellido' => $item->SEGUNDO_APELLIDO,
                                'sexo' => $item->SEXO,
                                'fNacimiento' => $item->FECHA_NACIMIFECHA_NACIMI,
                                'empresa' => $item->EMPRESA,
                                'regimen' => $item->REGIMEN,
                                'categoria' => $item->CATEGORIA,
                                'telefono1' => $item->TELEFONO1,
                                'telefono2' => $item->TELEFONO2,
                                'telefono3' => $item->TELEFONO3,
                            );

                            $records[] = $temp;
                        }

                        return response()->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $records
                        ], 200);

                    } else {

                        return response()->json([
                            'msg' => 'No Hay Datos en la Respuesta',
                            'status' => 204
                        ], 204);

                    }
                } else {

                    return response()->json([
                        'msg' => 'La Edad Debe Ser Mayor o Igual a 50 Años',
                        'status' => 200
                    ], 200);

                }

            } else {

                return response()->json([
                    'msg' => 'El Parametro Edad No Puede Estar Vacío',
                    'status' => 200
                ], 200);

            }

        } else {

            return response()->json([
                'msg' => 'Acceso No Autorizado',
                'status' => 401
            ], 401);

        }
    }
}
