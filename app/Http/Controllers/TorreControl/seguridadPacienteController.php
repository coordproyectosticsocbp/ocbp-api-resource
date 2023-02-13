<?php

namespace App\Http\Controllers\TorreControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;
use function Ramsey\Uuid\v1;
class seguridadPacienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }
}

