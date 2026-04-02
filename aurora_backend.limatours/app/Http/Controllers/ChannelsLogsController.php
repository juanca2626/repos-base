<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Http\Traits\ChannelLogs as XmlLogs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ChannelsLogsController extends Controller
{
    use XmlLogs;

    public function __construct()
    {
        $this->middleware('permission:channels.read')->only('index');
//        $this->middleware('permission:channels.create')->only('store');
//        $this->middleware('permission:channels.update')->only('update');
//        $this->middleware('permission:channels.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index($channel_id)
    {
        $channel = collect([1, 2, 3, 4, 5]);
        $channel->transform(function ($item, $key) {
            return $item * 2;
        });
        $channel = $channel->groupBy('account_id');


        $channel = Channel::where('id', '=', $channel_id)->with('logs')->first();

        $channel->logs->transformGoupBy(function ($log) {
            $log->method = explode('/', $log->log_directory)[1];
            return $log;
        }, ['method']);

        return Response::json(['success' => true, 'data' => $channel]);
    }


    /**
     * Display the specified resource.
     *
     * @param $channel_id
     * @param $log_id
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function show($channel_id, $log_id)
    {
        $channel = Channel::find($channel_id);

        $log = $channel->logs()->find($log_id);

        $Files = [
            'request' => $this->getLogRequest($log->log_directory),
            'response' => $this->getLogResponse($log->log_directory),
        ];

        return Response::json(['success' => true, 'data' => $Files]);
    }
}
