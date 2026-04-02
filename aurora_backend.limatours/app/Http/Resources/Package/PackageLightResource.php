<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageLightResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        session()->put('language_id_package', $this['language_id']);

        return [
            'id' => $this['id'],
            'code' => $this['code'],
            'country_id' => $this['country_id'],
            'nights' => $this['nights'],
            'children_age_allowed' => $this['child_age_allowed'],
            'type_services' => PackageTypeServicesResource::collection($this['service_types']),
            'categories' => PackageCategoriesResource::collection($this['plan_rates'][0]['plan_rate_categories_all']),
            'physical_intensity' => new PackagePhysicalIntensityResource($this['physical_intensity']),
            'destinations' => [
                'destinations_display' => $this['destinations'],
            ],
            'tag' => [
                'name' => $this->when(count($this['tag']['translations']) > 0, $this['tag']['translations'][0]['value'],
                    ''),
                'color' => $this->when(isset($this['tag']['color']), $this['tag']['color'], 'BD0D12')
            ],
            'descriptions' => [
                'name' => $this['translations'][0]['tradename'],
                'label' => $this['translations'][0]['label'],
                'description' => $this['translations'][0]['description_commercial'],
                'itinerary' => $this['itinerary'],
                'itinerary_link_current_year' => (isset($this['itineraries_all']) && count($this['itineraries_all']) > 0) ? (
                    collect($this['itineraries_all'])->firstWhere('year', date('Y'))['itinerary_link_commercial'] ??
                    collect($this['itineraries_all'])->firstWhere('year', date('Y'))['itinerary_link'] ??
                    $this['translations'][0]['itinerary_link_commercial'] ??
                    $this['translations'][0]['itinerary_link'] ?? ''
                ) : ($this['translations'][0]['itinerary_link_commercial'] ?? $this['translations'][0]['itinerary_link'] ?? ''),
            ],
            'min_date_reserve' => $this['min_date_reserve'],
            'included_services' => $this['included_services'],
            'galleries' => $this->when(count($this['galleries']) > 0,
                PackageGalleriesResource::collection($this['galleries']),
                [['url' => verifyCloudinaryImg('', 500, 450, '')]]),
            'highlights' => $this->when(count($this['highlights']) > 0,
                PackageHighlightsResource::collection($this['highlights']), []),
            'amounts' => [
                'price_per_person' => $this['price_per_person'],
                'total_amount' => $this['total_amount'],
            ],
            'token_search' => $this['token_search'],
        ];
    }


}
