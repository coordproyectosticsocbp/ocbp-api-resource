<?php

namespace App\Http\Controllers;

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
        if ($request->hasHeader('X-Authorization')) {

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

                //return $temp;

                return response()->json([
                    'msg' => 'In',
                    'dota' => 'Inn',
                    'data' => $records
                ], 200);

            } else {

                return response()->json([
                   'msg' => 'No hay datos en la respuesta'
                ], 204);

            }

        } else {

            return response()->json([
                'msg' => 'Acceso No autorizado'
            ], 401);

        }
    }
}
