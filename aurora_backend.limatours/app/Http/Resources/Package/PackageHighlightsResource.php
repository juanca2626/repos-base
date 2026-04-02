<?php

namespace App\Http\Resources\Package;

use App\Http\Traits\Images;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageHighlightsResource extends JsonResource
{
    use Images;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'url' => verifyCloudinaryImg($this['highlights']['url'], 350, 0, ''),
            'name' => $this['highlights']['translations'][0]['value'],
            'description' => (isset($this['highlights']['translations_content'][0])) ? $this['highlights']['translations_content'][0]['value'] : '',
        ];
    }
}
