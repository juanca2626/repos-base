<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileComponentDescriptionEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_component_descriptions';

}
