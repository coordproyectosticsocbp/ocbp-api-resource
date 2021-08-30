<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EthereumController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }


    /**
     * @OA\Get (
     *     path="/api/v1/ethereum/get/specialties",
     *     operationId="Specialties",
     *     tags={"Ethereum"},
     *     summary="Get Specialties Info",
     *     description="Returns Specialties Info",
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
    public function getSpecialties(Request $request)
    {
        if ($request->hasHeader('X-Authorization')) {

            try {

                $query = DB::connection('sqlsrv_hosvital')
                    ->select("SELECT * FROM MAEESP WHERE EspEst = 'S'");

                if (sizeof($query) > 0) {

                    $records = [];

                    foreach ($query as $item) {

                        $temp = array(
                            'speCode' => $item->MECodE,
                            'speDescription' => trim($item->MENomE),
                            'speState' => $item->EspEst
                        );

                        $records[] = $temp;

                    }


                    if (sizeof($records) > 0) {

                        return response()
                            ->json([
                                'msg' => 'Ok',
                                'status' => 200,
                                'data' => $records
                            ]);

                    } else {

                        return response()
                            ->json([
                                'msg' => 'No hay datos en respuesta a la solicitud',
                                'status' => 400,
                                'data' => []
                            ]);

                    }

                } else {

                    return response()
                        ->json([
                            'msg' => 'No hay datos en respuesta a la solicitud',
                            'status' => 400,
                            'data' => []
                        ]);

                }

            } catch (\Throwable $e) {

                throw $e;

            }

        }
    }


    /**
     * @OA\Get (
     *     path="/api/v1/ethereum/get/doctors-with-spe-regm",
     *     operationId="Doctors",
     *     tags={"Ethereum"},
     *     summary="Get Doctors Info",
     *     description="Returns Doctors Info",
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
    public function getDoctorsWithSpecialty(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            $query = DB::connection('sqlsrv_hosvital')
                ->select("SELECT * FROM ETHEREUM_MEDICOS_ESP_REGM()");

            if (sizeof($query) > 0) {

                $records = [];

                foreach ($query as $item) {

                    $temp = array(
                        'docCode' => $item->CODIGO_MEDICO,
                        'docName1' => $item->NOMBRE1,
                        'docName2' => $item->NOMBRE2,
                        'docLName1' => $item->APELLIDO1,
                        'docLName2' => $item->APELLIDO2,
                        'docDocType' => $item->TIP_DOC,
                        'docDoc' => $item->DOCUMENTO,
                        'medRec' => $item->REGISTRO,
                        'speCode' => $item->COD_ESPECIALIDAD,
                        'speName' => $item->NOM_ESPECIALIDAD,
                    );

                    $records[] = $temp;

                }


                if (sizeof($records) > 0) {

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $records
                        ]);

                } else {

                    return response()
                        ->json([
                            'msg' => 'No hay datos en respuesta a la solicitud',
                            'status' => 400,
                            'data' => []
                        ]);

                }

            } else {

                return response()
                    ->json([
                        'msg' => 'No hay datos en respuesta a la solicitud',
                        'status' => 400,
                        'data' => []
                    ]);

            }

        }
    }

    /**
     * @OA\Get (
     *     path="/api/v1/ethereum/get/diagnostics",
     *     operationId="Diagnostics",
     *     tags={"Ethereum"},
     *     summary="Get Diagnostics Info",
     *     description="Returns Diagnostics Info",
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
    public function getDiagnostics(Request $request)
    {

        if ($request->hasHeader('X-Authorization')) {

            //try {

            $query = DB::connection('sqlsrv_hosvital')
                ->select("SELECT DMCodi, DMNomb, DgnTpo, DgnEst FROM MAEDIA WHERE DgnEst = 'A'");

            if (sizeof($query) > 0) {

                $records = [];

                foreach ($query as $item) {

                    $temp = array(
                        'dxCode' => trim($item->DMCodi),
                        'dxDescription' => trim($item->DMNomb),
                        'dxType' => $item->DgnTpo,
                        'dxState' => $item->DgnEst
                    );

                    $records[] = $temp;

                }


                if (sizeof($records) > 0) {

                    return response()
                        ->json([
                            'msg' => 'Ok',
                            'status' => 200,
                            'data' => $records
                        ]);

                } else {

                    return response()
                        ->json([
                            'msg' => 'No hay datos en respuesta a la solicitud',
                            'status' => 400,
                            'data' => []
                        ]);

                }

            } else {

                return response()
                    ->json([
                        'msg' => 'No hay datos en respuesta a la solicitud',
                        'status' => 400,
                        'data' => []
                    ]);

            }

            /*} catch (\Throwable $e) {

                throw $e;

            }*/

        }
    }
}
