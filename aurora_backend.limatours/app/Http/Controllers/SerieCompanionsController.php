<?php

namespace App\Http\Controllers;

use App\Language;
use App\SerieCompanion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieCompanionsController extends Controller
{
    public function index($serie_id, Request $request)
    {
        $lang = strtolower($request->input('lang'));
        $language_id = Language::select('id')->where('iso',$lang)->first()->id;

        $data_companions = SerieCompanion::where('serie_id', $serie_id)
            ->with(['pay_mode.translations'=>function($query) use($language_id){
                $query->where('language_id',$language_id);
             }])
            ->with(['user_type'])
            ->get();

        return Response::json(['success' => true, 'data'=> $data_companions ]);
    }

    public function update_multiple($serie_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'companions' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $companions = $request->input('companions');

            SerieCompanion::where('serie_id', $serie_id)->delete();

            foreach ( $companions as $companion ){
                $new_companion = new SerieCompanion();
                $new_companion->serie_id = $serie_id;
                $new_companion->quantity = $companion['quantity'];
                $new_companion->pay_mode_id = $companion['pay_mode_id'];
                $new_companion->user_type_id = $companion['user_type_id'];
                $new_companion->save();
            }

            return Response::json(['success' => true]);
        }
    }

}
