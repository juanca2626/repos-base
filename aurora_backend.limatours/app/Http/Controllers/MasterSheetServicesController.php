<?php

namespace App\Http\Controllers;

use App\MasterSheetService;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class MasterSheetServicesController extends Controller
{
    public function update_comment($id, Request $request){

        $attached = $request->input('attached');
        $comment = $request->input('comment');
        $comment_status = $request->input('comment_status');

        $service = MasterSheetService::find($id);
        $service->attached = $attached;
        $service->comment = $comment;
        $service->comment_status = $comment_status;
        $service->save();

        return Response::json([ "success" => true ]);

    }

}
