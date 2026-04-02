<?php

namespace App\Http\Controllers;

use App\ClientPackageRated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientPackageRatedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'package_id' => 'required',
            'rated' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
        } else {
            try {
                $client_id = $request->input('client_id');
                $package_id = $request->input('package_id');
                $rated = $request->input('rated');
                $find = ClientPackageRated::where('client_id', $client_id)
                    ->where('package_id', $package_id)
                    ->get();
                if (count($find) == 0) {
                    $new = new ClientPackageRated();
                    $new->client_id = $client_id;
                    $new->package_id = $package_id;
                    $new->rated = $rated;
                    $new->save();
                } else {
                    $find = ClientPackageRated::find($find[0]['id']);
                    $find->rated = $rated;
                    $find->save();
                }

                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                return Response::json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

    }

}
