<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasiMailingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:masimailing.read');
    }
    /**
     * Show MASI MAILING module.
     *
     * @return View
     */
    public function __invoke()
    {
        return view('menu.masi_mailing');
    }
}
