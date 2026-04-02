<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartDetailController extends Controller
{
    public function index(){
        return view('cart_details.index');
    }
}
