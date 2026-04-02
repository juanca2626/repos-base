<?php

namespace App\Http\Controllers;

use App\Market;
use App\Region;
use Illuminate\Http\Request;

class MasiStatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:masistatistics.read');
    }
    /**
     * Show MASI MAILING module.
     *
     * @return View
     */
    public function __invoke()
    {
        return view('menu.masi_statistics');
    }
}
