<?php

namespace App\Http\Services\Traits;

use App\RatesPlansRooms;

trait ClientHotelOnRequestTrait
{
    public function getClientHotelsAvailOnRequest(
        $period,
        $from,
        $to,
        $reservation_days,
        array $hotels_id,
        $language_id,
        $rates_plan_rooms_on_request
    )
    {
        return RatesPlansRooms::whereIn('id', $rates_plan_rooms_on_request)
            ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                return $query->whereHas('rate_plan', function ($query) use ($hotels_id) {
                    $query->whereIn('hotel_id', $hotels_id);
                });
            })
            ->with([
                'policies_cancelation' => function ($query) {
                    $query->where('type', 'cancellations');
                    $query->with([
                        'policy_cancellation_parameter' => function ($query) {
                            $query->where('min_day', '>=', 0);
                            $query->where('max_day', '<>', 0);
                            $query->with('penalty');
                        },
                    ]);
                },
            ])
            ->with('descriptions')
            ->with([
                'calendarys' => function ($query) use ($from, $to, $language_id) {
                    $query->where('date', '>=', $from);
                    $query->where('date', '<=', $to);
                    $query->orderBy('date');
                    $query->with([
                        'policies_rates' => function ($query) use ($language_id) {
                            $query->with([
                                'policies_cancelation' => function ($query) {
                                    $query->where('type', 'cancellations');
                                    $query->with([
                                        'policy_cancellation_parameter' => function ($query) {
                                            $query->where('min_day', '>=', 0);
                                            $query->where('max_day', '<>', 0);
                                            $query->with('penalty');
                                        },
                                    ]);
                                },
                            ]);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->where('type', 'rate_policies');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);
                    $query->with('rate');
                },
            ])->with('channel')
            ->with('rate_plan.meal.translations')
            ->with([
                'rate_plan' => function ($query) use ($period, $language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                            $query->where('type', 'rates_plan');
                            $query->where('slug', 'commercial_name');
                        }
                    ]);
                    $query->with([
                        'translations_no_show' => function ($query) use ($language_id) {
                            $query->where('slug', 'no_show');
                            $query->where('type', 'rates_plan');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_day_use' => function ($query) use ($language_id) {
                            $query->where('slug', 'day_use');
                            $query->where('type', 'rates_plan');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_notes' => function ($query) use ($language_id) {
                            $query->where('slug', 'notes');
                            $query->where('type', 'rates_plan');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'meal.translations',
                        'markup' => function ($query) use ($period) {
                            $query->where('period', '>=', $period);
                        },
                    ]);
                },
            ])
            ->with([
                'inventories' => function ($query) use ($from, $to) {
                    $query->where('date', '>=', $from);
                    $query->where('date', '<=', $to);
                    $query->where('locked', '<>', 1);
                },
            ])
            ->with([
                'room' => function ($query) {
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'room');
                            $query->where('state', 1);
                        },
                    ]);

                    $query->with([
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
                    ]);

                    $query->with([
                        'room_type' => function ($query) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->where('language_id', 1);
                        },
                    ]);
                }
            ])->where('channel_id', 1)

            ->whereHas('calendarys', function ($query) use (
                $from,
                $reservation_days
            ) {
                $query->where('date', '=', $from);

                $query->whereHas('policies_rates', function ($query) use (
                    $from,
                    $reservation_days
                ) {
                    $query->where('min_length_stay', '<=', $reservation_days);
                    $query->where('max_length_stay', '>=', $reservation_days);
                });
            })
            ->get()->toArray();
    }
}
