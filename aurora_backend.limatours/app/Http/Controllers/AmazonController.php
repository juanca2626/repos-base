<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\Amazon;

class AmazonController extends Controller
{
    use Amazon;

    public function notification(Request $request)
    {
        try
        {
            $data = [];

            $response = $this->send_mail($data);

            return response()->json([
                'type' => 'success',
                'response' => $response,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function webhook(Request $request)
    {
        try
        {
            $data = $request->all();

            return response()->json([
                'type' => 'success',
                'data' => $data,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }
}
