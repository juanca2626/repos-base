<?php

namespace App\Http\Services\Controllers;

use App\Client;
use App\Http\Controllers\ClientHotelsController as ClientHotelsControllerBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ClientHotelsController extends ClientHotelsControllerBase
{
    public function classHotel(Request $request)
    {
        try {
            if (auth_user()->user_type_id == 4) {// cliente
                // client
                /** @var Client $client */
//                dd(auth_user());
                $client = auth_user()->clientSellers()
                    ->where('clients.status', 1)
                    ->wherePivot('status', 1)
                    ->first();

                if (!$client) {
                    throw new \Exception('Unable To process | error: 3315');
                }

                if ($client['status'] != 1) {
                    throw new \Exception('Unable To process | error: 3316');
                }

                $request->request->add([
                    'client_id' => $client['id'],
                ]);

                return $this->classHotelByClient($request);
            } else {
                throw new \Exception('Unable To process | error: 3317');
            }
        } catch (\Exception $exception){
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }
}
