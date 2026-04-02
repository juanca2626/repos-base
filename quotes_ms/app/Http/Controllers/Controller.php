<?php

namespace App\Http\Controllers;

use App\Models\ClientSeller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function getClientId($request_client_id)
    {
        $client_id = null;

        if (Auth::check()) {
            if (Auth::user()->user_type_id == 4) {
                $client_data = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
                $client_id = $client_data['client_id'];
            }
            //            dd($client_id);
            if (Auth::user()->user_type_id == 3) {
                $client_id = $request_client_id->client_id;
            }
        } else {
            $client_id = $request_client_id->client_id;
        }

        return $client_id;
    }

    public function throwError($ex)
    {
        return [
            'file'     => $ex->getFile(),
            'line'     => $ex->getLine(),
            'detail'   => $ex->getMessage(),
            'message'  => $ex->getMessage(),
            'type'     => 'error',
            'success'  => false,
            'process'  => false,
            'response' => 'ERR',
        ];
    }

    public function toArray($object = [])
    {
        if (is_object($object) || is_array($object)) {
            $array = [];

            foreach ($object as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = $this->toArray($value);
                }

                $array[$key] = $value;
            }

            return $array;
        } else {
            return $object;
        }
    }

    public function moveToCloudinary($path, $options = [])
    {
        $uploadedFileUrl = Cloudinary::upload($path, $options)->getSecurePath();

        return $uploadedFileUrl;
    }
}
