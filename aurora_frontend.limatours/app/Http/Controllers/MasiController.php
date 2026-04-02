<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class MasiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:masi.view'); // Validación de permisos
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [];

        return view('masi.index')->with('data', $data);
    }
}
