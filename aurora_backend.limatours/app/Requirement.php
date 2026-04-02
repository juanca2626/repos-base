<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Requirement extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['requirementService'];
    }

    public function requirementService()
    {
        return $this->hasMany('App\RequirementService');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'requirement');
    }
}
