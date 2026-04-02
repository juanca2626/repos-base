<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageDestinationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $value = null;

        if (
            isset($this->state) &&
            isset($this->state->translations) &&
            isset($this->state->translations[0]) &&
            isset($this->state->translations[0]->value)
        ) {
            $value = $this->state->translations[0]->value;
        }

        return [
            'state' => $value,
        ];
    }
}
