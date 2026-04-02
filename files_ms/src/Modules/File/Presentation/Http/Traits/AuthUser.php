<?php

namespace Src\Modules\File\Presentation\Http\Traits;
use Illuminate\Http\Request;

 

trait AuthUser
{
    /**
     * Debe retornar el usuario que esta logueado en el sistema header authorization
     * Decode correspondingly the response
     * @param  array $response
     * @return stdClass
     */
    public function getAuthUser(Request $request)
    { 
        return [
            'id' => 1
        ];
         
    }

  
}
