<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->user_type_id === 1 or $user->user_type_id === 4) {
            if ($this->hasPermission('mfpackages.read')) {
                return view('packages.client.packages');
            } else {
                abort(403);
            }
        } else {
            return view('packages.client.packages');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cotizacion()
    {  
        $user = Auth::user();
        if ($user->user_type_id === 1 or $user->user_type_id === 4) {
            if ($this->hasPermission('mfquotationboard.read')) {
                return view('packages.cotizacion');
            } else {
                abort(404);
            }
        } else {
            return view('packages.cotizacion');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function micotizacion()
    {
        return view('packages.micotizacion');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function details()
    {
        return view('packages.details');
    }

    public function details_reserve()
    {
        return view('packages.details_reserve');
    }

}
