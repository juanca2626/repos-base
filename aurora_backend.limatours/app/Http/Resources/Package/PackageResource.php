<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'language_id' => $this['language_id'],
            'available_from' => $this['available_from'],
            'date_reserve' => $this['date_reserve'],
            'type_package' => $this['extension'],
            'nights' => $this['nights'],
            'portada_link' => $this['portada_link'],
            'map_link' => str_replace(['edit', 'HTTPS:'], ['embed', ''], ($this['map_link'])),
            'map_itinerary_link' => $this['map_itinerary_link'],
            'favorite' => false,
            'allow_modify' => $this['allow_modify'],
            'allow_guide' => $this->when(empty($this['allow_guide']), 0, (int)$this['allow_guide']),
            'allow_child' => $this->when(empty($this['allow_child']), 0, (int)$this['allow_child']),
            'allow_infant' => $this->when(empty($this['allow_infant']), 0, (int)$this['allow_infant']),
            'adult_age_from' => $this['adult_age_from'],
            'infant_age_allowed' => [
                'min' => $this->when(empty($this['infant_min_age']), 0, $this['infant_min_age']),
                'max' => $this->when(empty($this['infant_max_age']), 0, $this['infant_max_age'])
            ],
            'children_age_allowed' => $this['child_age_allowed'],
            'type_services' => PackageTypeServicesResource::collection($this['service_types']),
            'categories' => PackageCategoriesResource::collection($this['plan_rates'][0]['plan_rate_categories_all']),
            'physical_intensity' => new PackagePhysicalIntensityResource($this['physical_intensity']),
            'destinations' => [
                'destinations_display' => $this['destinations'],
                'destinations' => PackageDestinationsResource::collection($this['package_destinations']),
            ],
            'tag' => [
                'name' => $this->when(count($this['tag']['translations']) > 0, $this['tag']['translations'][0]['value'],
                    ''),
                'color' => $this->when(isset($this['tag']['color']), $this['tag']['color'], 'BD0D12')
            ],
            'descriptions' => [
                'name' => $this['translations'][0]['tradename'],
                'name_gtm' => strtoupper($this['translations_gtm'][0]['tradename']),
                'label' => $this['translations'][0]['label'],
                'description' => $this['translations'][0]['description_commercial'],
                'itinerary' => $this['itinerary'],
                'restrictions' => $this['translations'][0]['restriction_commercial'],
                'contact' => $this['translations'][0]['label'],
                'cancellation_policies' => $this['translations'][0]['policies_commercial'],
                'itinerary_link' => (isset($this['itineraries']) and count($this['itineraries'])>0) ? $this['itineraries'][0]['itinerary_link_commercial'] : '',
                'priceless_itinerary_link' => (isset($this['itineraries']) and count($this['itineraries'])>0) ?  $this['itineraries'][0]['link_itinerary_priceless'] : '',
                'itinerary_link_current_year' => (isset($this['itineraries_all']) && count($this['itineraries_all']) > 0) ? (
                    collect($this['itineraries_all'])->firstWhere('year', date('Y'))['itinerary_link_commercial'] ??
                    collect($this['itineraries_all'])->firstWhere('year', date('Y'))['itinerary_link'] ??
                    $this['translations'][0]['itinerary_link_commercial'] ??
                    $this['translations'][0]['itinerary_link'] ?? ''
                ) : ($this['translations'][0]['itinerary_link_commercial'] ?? $this['translations'][0]['itinerary_link'] ?? ''),
                'itineraries_all' => (isset($this['itineraries_all']) and count($this['itineraries_all'])>0) ? collect($this['itineraries_all'])->groupBy('year') : [],
            ],
            'inclusions' => $this['inclusions'],
            'included_services' => $this['included_services'],
            'itinerary_hotels' => $this['itinerary_hotels'],
            'fixed_outputs' => $this['fixed_outputs'],
            'rated' => $this['rated'],
            'quantity_adult' => $this['quantity_adult'],
            'quantity_child' => $this['quantity_child'],
            'rate' => new PackagePlanRateResource($this['plan_rates'][0]),
            'galleries' => $this->when(count($this['galleries']) > 0,
                PackageGalleriesResource::collection($this['galleries']),
                [['url' => verifyCloudinaryImg('', 500, 450, '')]]),
            'highlights' => $this->when(count($this['highlights']) > 0,
                PackageHighlightsResource::collection($this['highlights']), []),
            'prices_children' => $this['prices_children'],
            'cancellation_policy' => $this['cancellation_policy'],
            'flights' => $this['flights'],
            'services_gtm' => $this['services_gtm'],
            'amounts' => [
                'offer' => $this['offer'],
                'offer_value' => $this['offer_value'],
                'without_discount' => $this['without_discount'],
                'price_per_person' => $this['price_per_person'],
                'price_per_adult' => $this['price_per_adult'],
                'total_adults' => $this['total_adults'],
                'total_children' => $this['total_children'],
                'price_per_child' => $this['price_per_child'],
                'total_amount' => $this['total_amount'],
            ],
            'token_search' => $this['token_search'],
            'min_date_reserve' => $this['min_date_reserve'],
            'schedules' => $this['schedules'],
            'schedule_days' => $this['schedule_days'],
        ];
    }


}
