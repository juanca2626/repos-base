<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BagRoom extends Model
{
    use SoftDeletes;

    protected  $fillable = ['bag_id','room_id'];

    public function bag()
    {
        return $this->belongsTo('App\Bag');
    }
    public function room()
    {
        return $this->belongsTo('App\Room');
    }
    public function inventory_bags()
    {
        return $this->hasMany('App\InventoryBag');
    }
}
