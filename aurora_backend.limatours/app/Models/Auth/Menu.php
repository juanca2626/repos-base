<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    const TARGET_SITE_A2 = 'a2';
    const TARGET_SITE_A3 = 'a3';
    const PUBLIC_SLUG = 'all';
    public $incrementing = false;
    public $timestamps = false;

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function sub_menus()
    {
        return $this->hasMany(SubMenu::class);
    }
    
    /**
     * @param  Builder $query
     * @return Builder
     */
    public function scopeFilterPublicSlug(Builder $query): Builder
    {
        return $query->where('slug', self::PUBLIC_SLUG);
    }

    /**
     * @param  Builder $query
     * @return Builder
     */
    public function scopeFilterHelpDesk(Builder $query): Builder
    {
        return $query->whereRaw(
            "REPLACE(LOWER(TRIM(name)), ' ', '') = ?",
            [strtolower(str_replace(' ', '', 'Mesa de Ayuda'))]
        );
    }

}
