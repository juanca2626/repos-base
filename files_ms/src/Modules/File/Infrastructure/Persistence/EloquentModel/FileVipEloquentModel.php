<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileVipEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_vips';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_id',
        'vip_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class);
    }

    public function vip(): BelongsTo
    {
        return $this->belongsTo(VipEloquentModel::class,'vip_id');
    }
}
