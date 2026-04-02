<?php

namespace Src\Shared\Presentation\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BaseResource extends JsonResource
{
    public function with($request)
    {
        return [
            'success' => true,
            'code' => Response::HTTP_OK,
        ];
    }
}
