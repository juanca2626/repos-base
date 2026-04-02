<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    public $incrementing = false;
    public $timestamps = false;


    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    
}
