<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTypes extends Model
{
    const CUSTOMER = 1;
    const SUPPLIER = 2;
    const INTERNAL_USER = 3;

    const SELLER = 4;
    const SCORT = 5;
    const TC = 6;
}
