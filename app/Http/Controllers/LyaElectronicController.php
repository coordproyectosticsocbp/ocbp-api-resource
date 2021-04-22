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
                    DB::raw("SELECT ")
                );
        }
    }

}
