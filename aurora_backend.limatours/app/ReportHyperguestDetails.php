<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportHyperguestDetails extends Model
{

    use SoftDeletes;

    // protected $table = 'report_hyperguest';

    protected $fillable = ['report_hyperguest_id','booking_id','property_id','property_name','property_country', 'property_city','lead_guest_name','adults',
                            'children','infants','start_date','end_date', 'price_amount','price_currency',
                            'status','partner_id','partner_name','agency_reference','booking_date','cancellation_deadline','file_code','status_aurora','price_aurora','fees_aurora','fees_hyperguest','reservations_hotels_rates_plans_room_ids'];

    public function getStartDateAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function getEndDateAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

}
