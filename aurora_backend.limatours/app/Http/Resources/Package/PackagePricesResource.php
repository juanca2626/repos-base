<?php

namespace App\Http\Resources\Package;

use App\Http\Traits\Package;
use Illuminate\Http\Resources\Json\JsonResource;

class PackagePricesResource extends JsonResource
{
    use Package;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'offer' => $this->when($this->offers->count() > 0, true, false),
            'without_discount' => 0,
//            'price' => $this->price_calculation($this,
//                ($request->input('quantity_persons')['adults'] + $request->input('quantity_persons')['child']),
//                $request->input('type_package'),$request->input('type_class')
//            )
        ];
    }

    public function price_calculation($plan_rate,$total_pax,$type_package,$type_class)
    {
        return $this->price_calculated_packages($plan_rate,$total_pax,$type_package,$type_class);
    }
}
