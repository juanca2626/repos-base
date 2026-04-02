<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Modules\File\Presentation\Http\Traits\PhoneNumberFormat;

class FilePassengerEloquentModel extends Model
{
    use HasFactory, SoftDeletes;
    use PhoneNumberFormat;

    protected $table = 'file_passengers';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $appends = ['type', 'room_type_description', 'date_birth_format', 'phone_code', 'phone_number'];

    protected static $typeRooms = [
        [
            'occupation' => 1,
            'description' => 'SGL'
        ],
        [
            'occupation' => 2,
            'description' => 'DBL'
        ],
        [
            'occupation' => 3,
            'description' => 'TPL'
        ]
    ];

    protected $fillable = [
        'id',
        'file_id',
        'sequence_number',
        'order_number',
        'frequent',
        'document_type_id',
        'doctype_iso',
        'document_number',
        'name',
        'surnames',
        'date_birth',
        'type',
        'suggested_room_type',
        'genre',
        'email',
        'phone',
        'country_iso',
        'city_iso',
        'dietary_restrictions',
        'medical_restrictions',
        'notes',
        'accommodation',
        'cost_by_passenger',
        'document_url',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getTypeAttribute()
    {
        return strtoupper($this->attributes['type']);
    }

    public function setTypeAttribute($value)
    {
        return $this->attributes['type'] = strtoupper($value);
    }

    public function getGenreAttribute()
    {
        return strtoupper($this->attributes['genre']);
    }

    public function setGenreAttribute($value)
    {
        return $this->attributes['genre'] = strtoupper($value);
    }

    public static function getRoomTypeDescriptionAttribute($suggested_room_type): string
    {
        $results = collect(static::$typeRooms)->firstWhere('occupation', $suggested_room_type );

        return isset($results['description']) ? $results['description'] : '';

    }

    public function getDateBirthFormatAttribute(): string
    {
        if($this->date_birth){
            return Carbon::parse($this->date_birth)->format('d/m/Y');
        }

        return '';
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class);
    }

    public function roomAccommodations(): HasMany
    {
        return $this->hasMany(FileRoomAccommodationEloquentModel::class);
    }

    public function getPhoneCodeAttribute()
    {
        $phone_parse = $this->phoneNumberFormat($this->phone, $this->country_iso);
        return $phone_parse['phone_code'];

    }

    public function getPhoneNumberAttribute()
    {
        $phone_parse = $this->phoneNumberFormat($this->phone, $this->country_iso);

        if($phone_parse['phone_code'])
        {
            return $phone_parse['phone_number'];
        }
    }
}
