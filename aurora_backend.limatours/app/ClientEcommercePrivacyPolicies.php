<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientEcommercePrivacyPolicies extends Model
{
    use SoftDeletes;

    protected $table = 'client_ecommerce_privacy_policies';

    public function translations_title()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'ecommerce_privacy_policy')
            ->where('translations.slug', '=', 'ecommerce_privacy_policy_title');
    }

    public function translations_privacy_policy()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'ecommerce_privacy_policy')
            ->where('translations.slug', '=', 'ecommerce_privacy_policy_content');
    }
}
