<?php

namespace App\Http\Services\Controllers;

use App\Client;
use App\UpSelling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HotelsUpSellingController extends ServiceController
{
    public function list(Request $request)
    {
        try {
            $token_search = $request->post('token_search_frontend');

            $hotels = $this->getHotelsByTokenSearch($token_search);
            if (!empty($hotels['error'])) {
                throw new \Exception($hotels['error']);
            }

            $hotel_id = $request->post('hotel_id');
            $hotels_upselling = UpSelling::where('hotel_id', $hotel_id)->pluck('hotel_child_id')->toArray();

            if (!$hotels_upselling) {
                $hotels = [
                    [
                        "city" => [
                            "token_search" => $token_search,
                            "token_search_frontend" => "",
                            "ids" => $hotels[0]["city"]["search_parameters"]['destiny']["code"],
                            "description" => $hotels[0]["city"]["search_parameters"]['destiny']["label"],
                            "class" => [],"zones" => [],"hotels" => [],
                            "search_parameters" => $hotels[0]["city"]["search_parameters"],
                            "quantity_hotels" => 0
                        ],
                    ],
                ];

                return Response::json([
                    'success' => true,
                    'data' => $hotels
                ]);
            } else {
                $hotels[0]["city"]["search_parameters"]["hotels_id"] = $hotels_upselling;
                $request->request->replace($hotels[0]["city"]["search_parameters"]);

                return $this->hotels($request);
            }
        } catch (\Exception $exception) {
            return Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
                'human_read_message' => $exception->getMessage()
            ]);
        }
//        return \response()->json($newRequest->all(), 200);
    }
}
