<?php

namespace App\Http\Controllers;

use App\PermissionRole;
use Illuminate\Http\Request;

class QuotesController extends Controller
{
    public function permissions(){
        return [
            'converttopackage' => $this->hasPermission('quotes.converttopackage'),
            'adddiscount' => $this->hasPermission('quotes.adddiscount'),
            'updatemarkup' => $this->hasPermission('quotes.updatemarkup')
        ];
    }

}
