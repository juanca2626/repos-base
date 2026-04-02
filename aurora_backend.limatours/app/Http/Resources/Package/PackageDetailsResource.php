<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageDetailsResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'type_package' => $this['extension'],
            'nights' => $this['nights'],
            'map_link' => $this['map_link'],
            'favorite' => false,
            'physical_intensity' => new PackagePhysicalIntensityResource($this['physical_intensity']),
            'destinations' => [
                'destinations_display' => $this['destinations'],
                'destinations' => PackageDestinationsResource::collection($this['package_destinations']),
            ],
            'tag' => $this->when(count($this['tag']['translations']) > 0, $this['tag']['translations'][0]['value'], ''),
            'descriptions' => [
                'name' => $this['translations'][0]['tradename'],
                'description' => $this['translations'][0]['description_commercial'],
                'restrictions' => $this['translations'][0]['restriction_commercial'],
                'contact' => $this['translations'][0]['label'],
                'cancellation_policies' => $this['translations'][0]['policies_commercial'],
                'itinerary_link' => $this['translations'][0]['itinerary_link_commercial'],
            ],
            'galleries' => $this->when(count($this['galleries']) > 0,
                PackageGalleriesResource::collection($this['galleries']),
                [['url' => verifyCloudinaryImg('', 500, 450, '')]]),
        ];
    }


}
