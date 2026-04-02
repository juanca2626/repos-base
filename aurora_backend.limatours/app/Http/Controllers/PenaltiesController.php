<?php

namespace App\Http\Controllers;

use App\Penalty;
use Illuminate\Support\Facades\Response;

class PenaltiesController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:contacts.read')->only('index');
        // $this->middleware('permission:contacts.create')->only('store');
        // $this->middleware('permission:contacts.update')->only('update');
        // $this->middleware('permission:contacts.delete')->only('delete');
    }

    public function selectBox()
    {
        $penalties = Penalty::select('id', 'name')->get();
        $result = [];
        foreach ($penalties as $penalty) {
            array_push($result, ['text' => $penalty->name, 'value' => $penalty->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }
}
