<?php

namespace Src\Shared\Infrastructure\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notifications';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'subject',
        'module',
        'submodule',
        'object_id',
        'data',
        'to',
        'cc',
        'bcc',
        'attachments',
        'message_id',
        'notification_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
