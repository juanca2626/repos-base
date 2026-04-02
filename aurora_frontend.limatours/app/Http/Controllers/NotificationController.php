<?php

namespace App\Http\Controllers;

use App\Clients;
use Illuminate\Http\Request;
use App\Notification;

class NotificationController extends Controller
{
    public function __construct() {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){}

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function createProgrammingClosure(Request $request) {

        $data = $request->__get('data');
        if(count($data)>0) {
            foreach($data as $k => $v) {
                $this->create('CIERRE PROGRAMACIÓN', 'FILE: #' . $v['nroref'], $v['codcli']);
            }
        }
    }

    public function create($title, $content, $user) {
        $notification = new Notification;
        $notification->title = $title;
        $notification->content = $content;
        $notification->target = 1;
        $notification->type = 1;
        $notification->url = '';
        $notification->user = $user;
        $notification->status = 1;
        $notification->data = '';
        $notification->created_by = 'ADMIN';
        $notification->updated_by = 'ADMIN';
        $notification->module = '';
        $notification->save();
    }

}
