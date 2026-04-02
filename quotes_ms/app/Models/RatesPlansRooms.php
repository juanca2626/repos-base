<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property Collection calendarys
 * @property Collection|RatesPlansCalendarys[] $inventories
 */
class RatesPlansRooms extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rates_plans_id',
        'room_id',
        'bag',
        'status',
        'channel_id',
        'channel_child_price',
        'channel_infant_price',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo('App\Models\Room');
    }

    public function rate_plan(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlans', 'rates_plans_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany('App\Models\Inventory', 'rate_plan_rooms_id');
    }

    public function bag_rates(): HasMany
    {
        return $this->hasMany('App\Models\BagRate', 'rate_plan_rooms_id');
    }

    public function bag_rate(): BelongsTo
    {
        return $this->belongsTo('App\Models\BagRate', 'id', 'rate_plan_rooms_id');
    }

    public function channel(): HasOne
    {
        return $this->hasOne('App\Models\Channel', 'id', 'channel_id');
    }

    public function markup(): HasOne
    {
        return $this->hasOne('App\Models\ClientRatePlan', 'rate_plan_id', 'rates_plans_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rate_plan_room');
    }

    public function policies_cancelation(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\PoliciesCancelations', null, null, 'policies_cancelation_id');
    }

    public function setCalendaryRange($dateStart, $dateEnd): RatesPlansRooms
    {
        $calendarys = $this->calendarys()->whereBetween(
            'date',
            [$dateStart, $dateEnd]
        )->with([
            'rate',
        ])->get();

        $this->setRelation('calendarys', $calendarys);

        return $this;
    }

    public function calendarys(): HasMany
    {
        return $this->hasMany('App\Models\RatesPlansCalendarys', 'rates_plans_room_id');
    }

    public function getCalendaryDate($date): RatesPlansCalendarys
    {
        return $this->calendarys->first(function ($item) use ($date) {
            return $item->date == $date;
        });
    }

    public function addCalendaryDate($date): RatesPlansCalendarys
    {
        $calendaryDate = new RatesPlansCalendarys();
        $calendaryDate->date = $date;
        $calendaryDate->min_ab_offset = 1;
        $calendaryDate->max_ab_offset = 365;
        $calendaryDate->min_length_stay = 1;
        $calendaryDate->max_length_stay = 99;

        $this->calendarys->add($calendaryDate);

        return $calendaryDate;
    }

    public function getInventoriDate($date): Inventory
    {
        return $this->inventories->first(function ($item) use ($date) {
            return $item->date == $date;
        });
    }

    public function addInventoriDate($date): Inventory
    {
        $inventoryDate = new Inventory();
        $inventoryDate->date = $date;
        $inventoryDate->day = substr($date, -2);
        $inventoryDate->inventory_num = 0;
        $inventoryDate->total_booking = 0;
        $inventoryDate->total_canceled = 0;
        $inventoryDate->locked = 0;

        $this->inventories->add($inventoryDate);

        return $inventoryDate;
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'rate_plan_room')
            ->where('translations.slug', 'rate_plan_room_description');
    }
}
