<?php

namespace App\Http\Controllers;

use App\ClientEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientEcommerceController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:clientecommerce.read')->only('index');
        $this->middleware('permission:clientecommerce.create')->only('store');
        $this->middleware('permission:clientecommerce.update')->only('update');
        $this->middleware('permission:clientecommerce.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($client_id)
    {
        $e_commerce = ClientEcommerce::where('client_id', $client_id)->get();
        return Response::json(['success' => true, 'data' => $e_commerce]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $ecommerce = new ClientEcommerce();
            $ecommerce->client_id = $request->input('client_id');
            $ecommerce->email = $request->input('email');
            $ecommerce->email_questions = $request->input('email_questions');
            $ecommerce->phone = $request->input('phone');
            $ecommerce->direction = $request->input('direction');
            $ecommerce->facebook = $request->input('facebook');
            $ecommerce->instagram = $request->input('instagram');
            $ecommerce->linkedin = $request->input('linkedin');
            $ecommerce->twitter = $request->input('twitter');
            $ecommerce->whatsapp = $request->input('whatsapp');
            $ecommerce->save();
            return Response::json(['success' => true]);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $ecommerce = ClientEcommerce::find($id);
            $ecommerce->email = $request->input('email');
            $ecommerce->email_questions = $request->input('email_questions');
            $ecommerce->phone = $request->input('phone');
            $ecommerce->direction = $request->input('direction');
            $ecommerce->facebook = $request->input('facebook');
            $ecommerce->instagram = $request->input('instagram');
            $ecommerce->linkedin = $request->input('linkedin');
            $ecommerce->twitter = $request->input('twitter');
            $ecommerce->whatsapp = $request->input('whatsapp');
            $ecommerce->save();
            return Response::json(['success' => true]);
        }
    }

}
