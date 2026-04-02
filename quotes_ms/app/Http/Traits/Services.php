<?php

namespace App\Http\Traits;

use App\Models\ServiceRate;
use App\Models\ServiceSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait Services
{
    private array $selected_tokens_search = []; // 24 horas

    public function getRatePlansSupplement($supplement_id, $dates, $language_id = 1, $total_pax = 1): Collection
    {
        $supplement_rate_plans = collect();
        foreach ($dates as $key => $date) {
            $date_to = Carbon::createFromFormat('d/m/Y', $date);
            $rate_plans = $this->getServiceRatePlans($supplement_id, $date_to, $total_pax, $language_id);
            if ($rate_plans) {
                if ($rate_plans->service_rate_plans->count() > 0) {
                    $supplement_rate_plans->add([
                        'date'      => $date,
                        'available' => true,
                        'rate'      => $rate_plans,
                    ]);
                } else {
                    $supplement_rate_plans->add([
                        'date'      => $date,
                        'available' => false,
                        'rate'      => $rate_plans,
                    ]);
                }
            } else {
                $supplement_rate_plans->add([
                    'date'      => $date,
                    'available' => false,
                    'rate'      => collect(),
                ]);
            }

        }

        return $supplement_rate_plans;
    }

    public function getServiceRatePlans($service_id, $date_to, $total_pax, $language_id = 1)
    {
        return ServiceRate::where('service_id', $service_id)->where('status', 1)
            ->whereDoesntHave('clients_rate_plan', function ($query) use ($date_to) {
                $query->where('client_id', $this->client_id());
                $query->where('period', Carbon::parse($date_to)->year);
            })->with([
                'service_rate_plans' => function ($query) use ($date_to, $total_pax) {
                    $query->select([
                        'id',
                        'service_rate_id',
                        'service_cancellation_policy_id',
                        'user_id',
                        'date_from',
                        'date_to',
                        'pax_from',
                        'pax_to',
                        'price_adult',
                        'price_child',
                        'price_infant',
                        'price_guide',
                        'status',
                    ]);
                    $query->where('date_from', '<=', $date_to->format('Y-m-d'))
                        ->where('date_to', '>=', $date_to->format('Y-m-d'));
                    $query->where('pax_from', '<=', $total_pax)
                        ->where('pax_to', '>=', $total_pax)
                        ->where('status', 1);
                    $query->with([
                        'policy' => function ($query) {
                            $query->where('status', 1);
                            $query->with([
                                'parameters' => function ($query) {
                                    $query->with('penalty');
                                },
                            ]);
                        },
                    ]);
                },
            ])->with([
                'translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])->first(['id', 'name', 'service_id', 'status']);
    }

    public function calculateAmountSupplement($rates_plans, $quantity_adults, $quantity_child, $markup): Collection
    {
        $prices = collect([
            'price_per_adult'    => 0,
            'price_per_child'    => 0,
            'price_per_person'   => 0,
            'total_adult_amount' => 0,
            'total_child_amount' => 0,
            'total_amount'       => 0,
        ]);
        foreach ($rates_plans as $rate) {
            $prices['price_per_adult'] += (($rate['price_adult'] * 1) + (($rate['price_adult'] * 1) * ($markup / 100)));
            if ($quantity_child > 0) {
                $prices['price_per_child'] += (($rate['price_child'] * 1) + (($rate['price_child'] * 1) * ($markup / 100)));
            }
            $price_per_person_adult = (float) roundLito($prices['price_per_adult']);
            $price_per_person_child = (float) roundLito($prices['price_per_child']);
            $prices['price_per_adult'] = $price_per_person_adult;
            $prices['price_per_child'] = $price_per_person_child;
            $prices['total_adult_amount'] = $price_per_person_adult * $quantity_adults;
            $prices['total_child_amount'] = $price_per_person_child * $quantity_child;
            $total_amount_rate = $prices['total_adult_amount'] + $prices['total_child_amount'];
            $price_per_person = $total_amount_rate / ($quantity_adults + $quantity_child);
            $prices['price_per_person'] = $price_per_person;
            $prices['total_amount'] = $total_amount_rate;
        }

        return $prices;
    }

    public function storeTokenSearchSupplements($token_search, $supplements, $minutes): void
    {
        Cache::put($token_search, $supplements, now()->addMinutes($minutes));
    }

    public function get_hour_ini($schedule_id, $date_in, $service_id)
    {

        $hour = null;

        if ($schedule_id != null && $schedule_id != '') {
            $schedule = ServiceSchedule::with(['servicesScheduleDetail'])->find($schedule_id);
            if ($schedule) {
                $date_in = Carbon::parse($date_in);
                $week_name = strtolower($date_in->format('l'));
                $schedule = $schedule->toArray();
                $hour = $schedule['services_schedule_detail'][0][$week_name];
                $hour = ($hour === '') ? null : $hour;
            }
        }

        if ($hour === null && $service_id !== null) {

            $schedule = ServiceSchedule::with(['servicesScheduleDetail'])
                ->where('service_id', $service_id)
                ->where('featured', 1)
                ->first();

            // Si no encuentra 'featured = 1', busca sin 'featured'
            if (!$schedule) {
                $schedule = ServiceSchedule::with(['servicesScheduleDetail'])
                    ->where('service_id', $service_id)
                    ->first();
            }

            if ($schedule) {
                $date_in = Carbon::parse($date_in);
                $week_name = strtolower($date_in->format('l'));
                $schedule = $schedule->toArray();
                $hour = $schedule['services_schedule_detail'][0][$week_name];
                $hour = ($hour === '') ? null : $hour;
            }
        }

        return $hour;
    }

}
