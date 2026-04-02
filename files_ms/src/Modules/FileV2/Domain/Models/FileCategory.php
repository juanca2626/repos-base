<?php

namespace Src\Modules\FileV2\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_categories';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_id', 
        'category_id', 
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    // public function category(): BelongsTo
    // {
    //     return $this->belongsTo(CategoryEloquentModel::class);
    // }
}
