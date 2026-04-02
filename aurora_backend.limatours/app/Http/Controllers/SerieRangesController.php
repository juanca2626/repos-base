<?php

namespace App\Http\Controllers;

use App\SerieRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieRangesController extends Controller
{
    public function index($serie_id)
    {
        $ranges = SerieRange::where('serie_id', $serie_id)->orderBy('min')->get();

        return Response::json(['success' => true, 'data'=> $ranges ]);
    }

    public function update_multiple($serie_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ranges' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $ranges = $request->input('ranges');
            $new_range_ids = [];

            foreach( $ranges as $k_r => $range ){
                if( $range['id'] === null ){

                    $find_same = SerieRange::where('serie_id', $serie_id)
                        ->where('min', $range['min'])->first();
                    $find_same_in_trashes = SerieRange::where('serie_id', $serie_id)
                        ->where('min', $range['min'])
                        ->where('deleted_at', '!=', null)
                        ->orderBy('created_at','desc')
                        ->withTrashed()->first();

                    $found = 0;
                    if( $find_same ){
                        $range_ = $find_same;
                        $found++;
                    } else {
                        if( $find_same_in_trashes ){
                            $range_ = $find_same_in_trashes;
                            $range_->deleted_at = null;
                            $found++;
                        }
                    }
                    if( $found === 0 ){
                        $range_ = new SerieRange();
                        $range_->min = $range['min'];
                        $range_->serie_id = $serie_id;
                    }
                } else {
                    $range_ = SerieRange::find( $range['id'] );
                }
                $range_->max = $range['max'];
                $range_->free_scort = $range['free_scort'];
                $range_->free_tc = $range['free_tc'];
                $range_->save();

                if( $range['id'] === null ) {
                    $ranges[$k_r]['id'] = $range_->id;
                    array_push( $new_range_ids, $range_->id );
                }
            }

            $range_ids = [];
            foreach( $ranges as $range ){
                array_push( $range_ids, $range['id'] );
            }

            // delete outs
            SerieRange::whereNotIn('id', $range_ids)->where('serie_id', $serie_id)->delete();

            return Response::json(['success' => true]);
        }
    }

}
