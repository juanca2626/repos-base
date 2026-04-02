<?php
namespace App\Http\Traits;

use App\MasterSheetDay;
use App\MasterSheetService;
use Carbon\Carbon;

trait MasterSheetDays
{
    private function update_numbers( $master_sheet_id ){
        $days = MasterSheetDay::where('master_sheet_id',$master_sheet_id)
            ->orderBy('date_in')
            ->orderBy('updated_at','desc')
            ->get();
        $number = 1;
        foreach ( $days as $k => $day ){
            if( $k >= 1 ){
                $previous_date = Carbon::parse($days[$k-1]->date_in);
                $this_date = Carbon::parse($day->date_in);
                $diff_in_days = $previous_date->diffInDays($this_date);
                $number += $diff_in_days;
            }
            $day->number = $number;
            $day->save();
        }
    }

    private function update_numbers_return_day($master_sheet_id, $master_sheet_day_id){
        $days = MasterSheetDay::where('master_sheet_id',$master_sheet_id)
            ->orderBy('date_in')
            ->orderBy('updated_at','desc')
            ->get();

        $master_sheet_day_new = '';
        $number = 1;
        foreach ( $days as $k => $day ){
            if( $k >= 1 ){
                $previous_date = Carbon::parse($days[$k-1]->date_in);
                $this_date = Carbon::parse($day->date_in);
                $diff_in_days = $previous_date->diffInDays($this_date);
                $number += $diff_in_days;
            }
            $day->number = $number;
            $day->save();
            if( $day->id == $master_sheet_day_id ){
                $master_sheet_day_new = $day;
            }
        }
        return $master_sheet_day_new;
    }

    private function update_destinations($master_sheet_day_id){

        $services = MasterSheetService::where('master_sheet_day_id', $master_sheet_day_id)
            ->orderBy('check_in')
            ->orderBy('origin_city')
            ->where('origin_city', '!=', null)
            ->where('origin_city', '!=', '')
            ->get();

        $destinations = [];
        foreach ( $services as $k => $service ){
            if( $k == 0 ){
                array_push( $destinations, $service->origin_city );
            } else {
                if( $destinations[ count($destinations) - 1 ] !== $service->origin_city ){
                    array_push( $destinations, $service->origin_city );
                }
            }
            if( $service->type_service === 'hotel' ){
                if( $destinations[ count($destinations) - 1 ] !== $service->destination_city ){
                    array_push( $destinations, $service->destination_city );
                }
            }
        }

        $concat_destinations = '';
        foreach ( $destinations as $k => $destination ){
            $separator = ' - ';
            if( $k === 0 ){
                $separator = '';
            }
            $concat_destinations.= $separator . $destination;
        }

        $day = MasterSheetDay::find($master_sheet_day_id);
        $day->destinations = $concat_destinations;
        $day->save();

        return true;
    }
}
