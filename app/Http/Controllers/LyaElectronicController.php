<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LyaElectronicController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    public function getPatientsData(Request $request)
    {
        if ($request->hasHeader('X-Authorization'))
        {
            $query = DB::connection('sqlsrv_hosv')
                ->select(
                    DB::raw("SELECT * FROM DIAGNOSTICOS_C90()")
                );

            if (count($query) > 0)
            {
                $records = [];

                foreach ($query as $item)
                {

                    $temp = array(
                        ''
                    );

                    $records = $temp;

                }
            }

            return response()
                ->json([
                    'msg' => 'Ok',
                    'status' => 200,
                    'data' => $records
                ], 200);
        }
    }

}
