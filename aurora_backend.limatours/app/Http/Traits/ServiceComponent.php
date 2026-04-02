<?php

namespace App\Http\Traits;


use App\Component;
use App\Service;

trait ServiceComponent
{

    private function max_nights($service_id)
    {
        $service_parent = Service::where('id', $service_id)
            ->with(['service_component'])
            ->first();

        $max_nights = 0;
        if( $service_parent->unit_duration_id === 1 ){ // horas
            $max_nights = ceil($service_parent->duration / 24);
        } elseif( $service_parent->unit_duration_id === 2 ){ // dias
            $max_nights = $service_parent->duration;
        } elseif( $service_parent->unit_duration_id === 3 ){ // semanas
            $max_nights = $service_parent->duration * 7;
        } elseif( $service_parent->unit_duration_id === 4 ){ // meses
            $max_nights = $service_parent->duration * 30;
        } elseif( $service_parent->unit_duration_id === 5 ){ // minutos
            $max_nights = ceil( ( $service_parent->duration / 60 ) / 24 );
        }

        if (count($service_parent->service_component) > 0) {
            $components_service_ids = Component::where('service_component_id', $service_parent->service_component[0]->id)
                ->pluck('service_id');
            $components = Service::whereIn('id', $components_service_ids)->get();
            foreach ($components as $component) {
                $duration_in_days = 0;
                if( $component->unit_duration_id === 1 ){ // horas
                    $duration_in_days = ceil($component->duration / 24);
                } elseif( $component->unit_duration_id === 2 ){ // dias
                    $duration_in_days = $component->duration;
                } elseif( $component->unit_duration_id === 3 ){ // semanas
                    $duration_in_days = $component->duration * 7;
                } elseif( $component->unit_duration_id === 4 ){ // meses
                    $duration_in_days = $component->duration * 30;
                } elseif( $component->unit_duration_id === 5 ){ // minutos
                    $duration_in_days = ceil( ( $component->duration * 60 ) / 24 );
                }

                if ($duration_in_days > $max_nights) {
                    $max_nights = $duration_in_days;
                }

            }
        }

        return $max_nights;

    }

}
