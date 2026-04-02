<?php

namespace App\Http\Controllers;

use App\DeactivatableEntity;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class DeactivatableEntitiesController extends Controller
{
    public function search(Request $request){

        $object_id = $request->input('object_id');
        $entity = $request->input('entity');

        $data = DeactivatableEntity::where('entity',$entity )
            ->where('object_id', $object_id)
            ->get();

        return Response::json(["success"=> true, "data"=> $data]);
    }
}
