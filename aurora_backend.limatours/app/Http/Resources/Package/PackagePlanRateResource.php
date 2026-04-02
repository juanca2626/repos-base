<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackagePlanRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        dd($this['service_type']);
        return [
            'id' => $this['id'],
            'plan_rate_category_id' => $this['plan_rate_categories'][0]['id'],
            'date_from' => $this['date_from'],
            'date_to' => $this['date_to'],
            'category' => [
                'id' => $this['plan_rate_categories'][0]['type_class_id'],
                'name' => $this['plan_rate_categories'][0]['category']['translations'][0]['value']
            ],
            'type_service' => [
                'id' => $this['service_type']['id'],
                'name' => $this['service_type']['translations'][0]['value']
            ],
        ];
    }
}
