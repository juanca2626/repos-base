<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCompositionEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'type_compositions';

}
