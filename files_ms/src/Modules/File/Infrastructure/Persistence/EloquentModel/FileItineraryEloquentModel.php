<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileItineraryEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    const ENTITY_SERVICE = 'service';
    const ENTITY_HOTEL = 'hotel';
    const ENTITY_FLIGHT = 'flight';

    protected $table = 'file_itineraries';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura 

    protected $fillable = [
        'id',
        'file_id',
        'entity',
        'object_id',
        'name',
        'category',
        'object_code',
        'country_in_iso',
        'country_in_name',
        'city_in_iso',
        'city_in_name',
        'zone_in_iso',
        'zone_in_id',
        'zone_in_airport',
        'country_out_iso',
        'country_out_name',
        'city_out_iso',
        'city_out_name',
        'zone_out_iso',
        'zone_out_id',
        'zone_out_airport',
        'start_time',
        'departure_time', 
        'date_in',
        'date_out',
        'total_adults',
        'total_children',
        'total_infants',
        'markup_created',
        'status',
        'confirmation_status',
        'total_amount',
        'total_cost_amount',
        'profitability',
        'serial_sharing',
        'data_passengers',
        'policies_cancellation_service',
        'service_rate_id',
        'is_in_ope',
        'sent_to_ope',
        'hotel_origin',
        'hotel_destination',
        'service_supplier_code',
        'service_supplier_name',
        'protected_rate',
        'view_protected_rate',
        'service_category_id',
        'service_sub_category_id',
        'service_type_id',
        'service_summary',
        'service_itinerary',
        'add_to_statement',
        'aurora_reservation_id',
        'files_ms_parameters',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $appends = ['nights','is_programmable'];
    

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class);
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany(FileItineraryDescriptionEloquentModel::class, 'file_itinerary_id');
    }

    public function hotelRooms(): HasMany
    {
        return $this->hasMany(FileHotelRoomEloquentModel::class, 'file_itinerary_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(FileHotelRoomEloquentModel::class, 'file_itinerary_id');
    }

    public function flights(): HasMany
    {
        return $this->hasMany(FileItineraryFlightEloquentModel::class, 'file_itinerary_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(FileItineraryDetailEloquentModel::class, 'file_itinerary_id');
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(FileItineraryAccommodationEloquentModel::class, 'file_itinerary_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(FileServiceEloquentModel::class, 'file_itinerary_id')
            ->orderBy('start_time', 'ASC');
    }

    public function service_amount_logs(): HasMany
    {
        return $this->hasMany(FileItineraryServiceAmountLogEloquentModel::class, 'file_itinerary_id');
    } 
    
    public function room_amount_logs(): HasMany
    {
        return $this->hasMany(FileItineraryRoomAmountLogEloquentModel::class, 'file_itinerary_id');
    } 
    
    public function calculateTotalCostAmount(): float
    {
        $total_cost_amount = 0;

        if($this->entity == 'hotel'){
            $total_cost_amount = $this->rooms->sum('amount_cost');
        }
        
        if(in_array($this->entity, ['service','service-temporary'])){ 
            $total_cost_amount = $this->services->sum('amount_cost');
        } 

        return $total_cost_amount;
    }

    public function calculateTotalAmount(): float
    {
        $total_amount = 0;

        if($this->entity == 'hotel'){
            $total_amount = $this->rooms->sum('amount_sale');
        }
         
        if(in_array($this->entity, ['service','service-temporary'])){                        
            $total_amount = $this->services->sum('amount_cost');
        } 

        return $total_amount;
    }


    public function calculateProfitability(): float
    {
        $totalSale = $this->total_amount;
        $totalCost = $this->total_cost_amount;

        $profitability = 0;
        if($totalCost > 0){
            $profitability = round((($totalSale - $totalCost ) / $totalCost) * 100, 2);
        }    
        return $profitability;
    } 

    public function getNightsAttribute(): int 
    {
        $date1 = new \DateTime($this->date_in);
        $date2 = new \DateTime($this->date_out);
        $diff = $date1->diff($date2);

        return $diff->days;
    }

    public function getIsProgrammableAttribute(): int 
    { 
        if($this->entity == "flight") {
            return true;
        }
        
        if($this->entity == "service" || $this->entity == "service-temporary") {
            return true;
        }

        return false;
    }
    
}
