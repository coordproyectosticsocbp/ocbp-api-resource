<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurnDeliveryController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.apikey');
    }

    /**
     * @OA\Get (
     *     path="/api/v1/turnos/get/turns-by-date/{pavilion?}/{date?}",
     *     operationId="get Turns by date",
     *     tags={"Hito"},
     *     summary="get get Turns by date",
     *     description="Returns get Turns by date",
     *     security = {
     *          {
     *              "type": "apikey",
     *              "in": "header",
     *              "name": "X-Authorization",
     *              "X-Authorization": {}
     *          }
     *     },
     *     @OA\Parameter (
     *          name="pavilion?",
     *          description="Required",
     *          required=true,
     *          in="path",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date?",
     *          description="Required",
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
    public function getTurnsByDate(Request $request, $pavilion = '', $date = '')
    {

        if ($request->hasHeader('X-Authorization')) {
            $token = $request->header('X-Authorization');
            $user = DB::select("SELECT TOP 1 * FROM api_keys AS ap WHERE ap.[key] = '$token'");

            if (count($user) > 0) {

                if ($pavilion == '' || $date == '') {
                    return response()->json([
                        'error' => 'El pabellón y la fecha Son Requeridos',
                        'status' => 400
                    ], 400);
                } else {

                    // Query to get the turns by date
                    $query = DB::connection('pgsql')
                        ->table('turn-doctor')
                        ->where('PatPavilion', $pavilion)
                        ->where('dateCurrent', '=', $date)
                        //->whereBetween('dateCurrent', ['18:00', '20:00'])
                        ->orderBy('PatHabitation', 'ASC')
                        ->distinct('PatHabitation')
                        ->get();

                    if (count($query) > 0) {

                        $turn = [];

                        foreach ($query as $row) {

                            $cadena = preg_split('/&#x0D;\n/', $row->diagnosis);

                            $diag = [];
                            for ($x = 1; $x < sizeof($cadena); $x++) {
                                $diag[] = $cadena[$x];
                            }

                            $tempTurn = array(
                                'patient' => $row->nameComplete,
                                'patientDoc' => $row->PatDoc,
                                'patientAdmDate' => $row->PatAdmDate,
                                'patientCompany' => $row->PatCompany,
                                'patientPavilion' => $row->PatPavilion,
                                'patientHabitation' => $row->PatHabitation,
                                'patientDx' => $diag,
                                'patientTreatment' => $row->treatment,
                                'patientPendingRecommendations' => $row->PendingRecommendations,
                                'doctorWhoDelivers' => $row->doctorWhoDelivers,
                                'doctorWhoReceives' => $row->doctorReceives,
                                'turnChangeDateTime' => $row->dateCurrent . ' ' . $row->hourCurrent
                            );

                            $turn[] = $tempTurn;
                        }

                        if (count($turn) > 0) {
                            return response()
                                ->json([
                                    'msg' => 'OK',
                                    'status' => 200,
                                    'data' => $turn
                                ])->header("Access-Control-Allow-Origin",  "*");
                        }
                    } else {
                        return response()
                            ->json([
                                'msg' => 'No se encontraron turnos para el pabellón y fecha seleccionados',
                                'status' => 204
                            ], 204)->header("Access-Control-Allow-Origin",  "*");
                    }
                }
            } else {
                return response()->json([
                    'error' => 'Usuario no autorizado',
                    'status' => 401
                ], 401)->header("Access-Control-Allow-Origin",  "*");
            }
        }
    }
}
