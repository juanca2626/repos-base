<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageTypeServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Obtener el idioma desde la sesión
        $language_id = session()->get('language_id_package');
        // Obtener la traducción para el idioma actual
        $translation = $this->service_type->translations->firstWhere('language_id', $language_id)->value ?? '';

        return [
            'id' => $this->service_type->id,
            'name' => $translation,
        ];
    }
}
