<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    const APP_NAME_ECOMMERCE = 1;
    const APP_NAME_ADMINISTRATION = 2;
    const APP_NAME_MANAGEMENT = 3;
    const APP_NAME_AUXILIARIES = 4;
    const APP_NAME_OPERATIONS = 5;
    const APP_NAME_NEGOTIATIONS = 6;
    const APP_NAME_ACCOUNTING = 7;
    public $incrementing = false;
    public $timestamps = false;

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
