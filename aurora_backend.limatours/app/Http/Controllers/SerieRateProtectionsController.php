<?php

namespace App\Http\Controllers;

use App\SerieRateProtection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieRateProtectionsController extends Controller
{
    public function index($serie_id)
    {
        $data = SerieRateProtection::where('serie_id', $serie_id)->orderBy('year')->get();

        return Response::json(['success' => true, 'data'=> $data ]);
    }


    public function update_multiple($serie_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_protections' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $rate_protections = $request->input('rate_protections');
            $new_rate_protection_ids = [];

            foreach( $rate_protections as $k_r => $rate_protection ){
                if( $rate_protection['id'] === null ){

                    $find_same = SerieRateProtection::where('serie_id', $serie_id)
                        ->where('year', $rate_protection['year'])->first();
                    $find_same_in_trashes = SerieRateProtection::where('serie_id', $serie_id)
                        ->where('year', $rate_protection['year'])
                        ->where('deleted_at', '!=', null)
                        ->orderBy('created_at','desc')
                        ->withTrashed()->first();

                    $found = 0;
                    if( $find_same ){
                        $rate_protection_ = $find_same;
                        $found++;
                    } else {
                        if( $find_same_in_trashes ){
                            $rate_protection_ = $find_same_in_trashes;
                            $rate_protection_->deleted_at = null;
                            $found++;
                        }
                    }
                    if( $found === 0 ){
                        $rate_protection_ = new SerieRateProtection();
                        $rate_protection_->serie_id = $serie_id;
                    }
                } else {
                    $rate_protection_ = SerieRateProtection::find( $rate_protection['id'] );
                }
                $rate_protection_->year = $rate_protection['year'];
                $rate_protection_->hotel = $rate_protection['hotel'];
                $rate_protection_->service = $rate_protection['service'];
                $rate_protection_->train = $rate_protection['train'];
                $rate_protection_->save();

                if( $rate_protection['id'] === null ) {
                    $rate_protections[$k_r]['id'] = $rate_protection_->id;
                    array_push( $new_rate_protection_ids, $rate_protection_->id );
                }
            }

            $rate_protection_ids = [];
            foreach( $rate_protections as $rate_protection ){
                array_push( $rate_protection_ids, $rate_protection['id'] );
            }

            // delete outs
            SerieRateProtection::whereNotIn('id', $rate_protection_ids)->where('serie_id', $serie_id)->delete();

            return Response::json(['success' => true]);
        }
    }

    public function get_by_year($serie_id, $year){

        $data = SerieRateProtection::where('serie_id', $serie_id)->where('year', $year)->first();
        return Response::json(['success' => ( $data !== null ), 'data'=> $data ]);
    }

}
