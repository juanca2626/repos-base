<?php

namespace Src\Modules\File\Presentation\Http\Traits;

trait RemoveItemInArray
{
    /**
     * Decode correspondingly the response
     * @param  string $field
     * @param  array $params
     * @return array
     */
    public function removeItemInArray(string $field,array $params): array
    { 
        unset($params[$field]);           
        return $params;
    }

  
}
