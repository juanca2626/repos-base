<?php

namespace App\Http\Multichannel\Hyperguest\Traits;

use App\Room;
use App\Country;
use App\Package;
use App\Currency;
use App\RoomType;
use Carbon\Carbon;
use App\RatesPlans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionRoomStatusHyperguest;
use App\Mail\NotificationRatePlansStatusHyperguest;

trait HotelSyncTrait
{
    public function isValidArrayResponse($data): bool
    {
        if (!isset($data)) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        return true;
    }

    public function getCountryIdByISO(?string $iso): ?int
    {
        if (!$iso) {
            return null;
        }

        return Country::where('iso', $iso)->value('id');
    }

    public function getCurrencyIdByISO(?string $iso): ?int
    {
        if (!$iso) {
            $iso = 'USD';
        }

        return Currency::where('iso', $iso)->value('id');
    }

    public function validarCamposHotel(array $externalHotel, $hotelIdExternal = null)
    {
        if (!$externalHotel) {
            $errorMessage = "El externalHotel no es un array valido";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        if ($externalHotel['status'] != 'Approved') {
            $errorMessage = "El estado del hotel aún no ha sido aprobado. Status: " . ($externalHotel['status'] ?? 'N/A');
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        $settings = $externalHotel['settings'] ?? [];
        $coordinates = $externalHotel['coordinates'] ?? [];
        $location = $externalHotel['location'] ?? [];
        $countryISO = $location['countryCode'] ?? null;
        $currencyISO = $settings['currency'] ?? null;
        $checkInTime = $settings['checkIn'] ?? null;
        $checkOutTime = $settings['checkOut'] ?? null;
        $latitude = $coordinates['latitude'] ?? null;
        $longitude = $coordinates['longitude'] ?? null;
        $name = $externalHotel['name'] ?? null;
        $stars = $externalHotel['rating'] ?? null;
        $webSite = $externalHotel['webSite'] ?? null;
        $city = $externalHotel['city'] ?? null;
        $chain = $externalHotel['settings']['chain'] ?? null;
        $rooms = $externalHotel['rooms'] ?? [];

        $now = now();

        if (!$countryISO || !$currencyISO) {
            $errorMessage = "Faltan códigos ISO: país = {$countryISO}, moneda = {$currencyISO}";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        $countryId = $this->getCountryIdByISO($countryISO);
        if (!$countryId) {
            $errorMessage = "País no encontrado: $countryISO";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        $currencyId = $this->getCurrencyIdByISO($currencyISO);
        if (!$currencyId) {
            $errorMessage = "Moneda no encontrada: $currencyISO";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        if (is_null($latitude) || is_null($longitude)) {
            $errorMessage = "Faltan coordenadas del hotel: latitude o longitude nulos.";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        if (is_null($checkInTime) || is_null($checkOutTime)) {
            $errorMessage = "Faltan horarios de check-in/check-out.";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        if (empty($name) || is_null($stars)) {
            $errorMessage = "Faltan nombre o rating del hotel.";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        if (empty($rooms)) {
            $errorMessage = "Faltan rooms a este hotel: {$externalHotel['hotelId']}";
            $this->registrarErrorHotel($hotelIdExternal, $errorMessage);
            return false;
        }

        return [
            'countryId' => $countryId,
            'currencyId' => $currencyId,
            'checkInTime' => $checkInTime,
            'checkOutTime' => $checkOutTime,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'name' => $name,
            'stars' => $stars,
            'webSite' => $webSite,
            'now' => $now,
            'city' => $city,
            'chain' => $chain,
            'rooms' => $rooms,
            'settings' => $settings,
            'location' => $location
        ];
    }

    public function validarCamposHabitacion(array $externalRoom, int $hotelId)
    {
        if (!isset($externalRoom['settings'])) {
            return false;
        }

        $settings = $externalRoom['settings'];
        // $status = in_array($externalRoom['status'], ['enabled', 'new']) ? 1 : 0;
        $maxCapacity = $settings['maxOccupancy'] ?? 0;
        $minAdults = $settings['baseAdults'] ?? 0;
        $maxAdults = $settings['adultsNumber'] ?? 0;
        $maxChildren = $settings['childrenNumber'] ?? 0;
        $maxInfants = $settings['infantsNumber'] ?? 0;
        // $state = $status === 'enabled' ? 1 : 0;
        $state = in_array($externalRoom['status'], ['enabled', 'new']) ? 1 : 0;
        $name = $externalRoom['name'] ?? '';
        $type_room = $externalRoom['type'] ?? '';
        $name = $externalRoom['name'] ?? '';

        $buscarTipoHabitacion = $this->buscarTipoDeHabitacion($type_room, $maxAdults);
        // Defaults del legacy
        $minInventory = 1;
        $seeInRates = 1;
        $order = 1;
        $inventory = 1;
        $bedAdditional = 0;
        $ignoreRateChild = 0;
        $roomTypeId = $buscarTipoHabitacion->id ?? 5;
        $now = now();

        $fields = [
            'maxCapacity' => $maxCapacity,
            'minAdults' => $minAdults,
            'maxAdults' => $maxAdults,
            'maxChildren' => $maxChildren,
            'maxInfants' => $maxInfants,
            'minInventory' => $minInventory,
            'state' => $state,
            'seeInRates' => $seeInRates,
            'order' => $order,
            'inventory' => $inventory,
            'bedAdditional' => $bedAdditional,
            'ignoreRateChild' => $ignoreRateChild,
            'roomTypeId' => $roomTypeId,
            'now' => $now,
            'name' => $name
        ];

        return $fields;
    }

    public function validarCamposPlanTarifa(array $externalRatePlan, int $hotelId)
    {
        if (empty($externalRatePlan['id']) || empty($externalRatePlan['name'])) {
            return false;
        }

        // if (!isset($externalRatePlan['settings']['status'])) {
        //     Log::warning("Falta estado del plan tarifario en settings. Hotel ID: $hotelId, Plan: {$externalRatePlan['id']}");
        //     return false;
        // }

        // $status = $externalRatePlan['settings']['status'] === 'enabled' ? 1 : 0;
        $status = 1;
        $name = $externalRatePlan['name'];
        $mealId = 1;
        $typeId = 2;
        $chargeTypeId = 1;
        $now = now();

        $fields = [
            'status' => $status,
            'name' => $name,
            'mealId' => $mealId,
            'typeId' => $typeId,
            'chargeTypeId' => $chargeTypeId,
            'now' => $now
        ];

        return $fields;
    }

    public function actualizarOInsertarImagenes(array $data, string $type, bool $isUpdate = false, ?string $slug = null): void
    {
        $now = now();

        $attributes = [
            'object_id' => $data['object_id'],
            'channel_gallery_id' => $data['channel_gallery_id'],
            'type' => $type,
        ];
        $values = [
            'slug' => $slug ?? 'hotel_gallery',
            'type' => $type,
            'object_id' => $data['object_id'],
            'channel_gallery_id' => $data['channel_gallery_id'],
            'url' => $data['url'],
            'position' => $data['position'] ?? 1,
            'state' => 1,
        ];

        if ($isUpdate) {
            $values['updated_at'] = $now;
        } else {
            $values['created_at'] = $now;
        }
        DB::table('galeries')->updateOrInsert($attributes, $values);
    }

    public function buscarTipoDeHabitacion(?string $type, int $maxAdults): ?RoomType
    {
        if (blank($type)) {
            return null;
        }

        // Si el número de adultos es 4 o más, siempre usar 4
        $occupation = $maxAdults >= 4 ? 4 : $maxAdults;

        // Buscar coincidencia por traducción y ocupación
        $roomType = RoomType::with('translations')
            ->whereHas('translations', function ($query) use ($type) {
                $query->where('value', 'like', '%' . $type . '%');
            })
            ->where('occupation', $occupation)
            ->first();

        if (!$roomType) {
            $roomType = RoomType::with('translations')
                ->where('occupation', $occupation)
                ->first();
        }

        return $roomType;
    }

    public function enviarCorreoCambioEstadoHabitacion(Room $room): void
    {
        $roomId = $room->id;
        $packages = $this->get_room_uses($roomId);

        if (count($packages) > 0) {
            foreach ($packages as $package) {
                $data["packages"][] = [
                    "categories" => [$package->plan_rates[0]->plan_rate_categories[0]->category->translations[0]->value],
                    "package" => "{$package->id} - [$package->code] - {$package->translations[0]->value}",
                    "period" => Carbon::parse($package->plan_rates[0]->date_from)->format('d/m/Y') . ' - ' . Carbon::parse($package->plan_rates[0]->date_to)->format('d/m/Y'),
                    "plan_rate" => "[{$package->plan_rates[0]->service_type->code}] - {$package->plan_rates[0]->name}"
                ];
            }


            $data["user"] = "AURORA (HYPERGUEST)";

            $room = Room::where('id', $roomId)->with('hotel.channels')->first();
            $data["hotel"] = $room->hotel->channels[0]->pivot->code . ' - ' . $room->hotel->name;
            $data["room_id"] = $roomId;
            $data["room_name"] = "[{$roomId}] $room->name";

            $mail = Mail::to("producto@limatours.com.pe");
            $mail->cc(["neg@limatours.com.pe","kams@limatours.com.pe", "qr@limatours.com.pe"]);
            $mail->send(new NotificacionRoomStatusHyperguest($data));
        }
    }

    public function enviarCorreoCambioEstadoPlanTarifa(RatesPlans $ratePlan): void
    {
        $ratePlanId = $ratePlan->id;
        $packages = $this->get_rate_plan_uses($ratePlanId);

        if (count($packages) > 0) {
            foreach ($packages as $package) {
                $data["packages"][] = [
                    "categories" => [$package->plan_rates[0]->plan_rate_categories[0]->category->translations[0]->value],
                    "package" => "{$package->id} - [$package->code] - {$package->translations[0]->value}",
                    "period" => Carbon::parse($package->plan_rates[0]->date_from)->format('d/m/Y') . ' - ' . Carbon::parse($package->plan_rates[0]->date_to)->format('d/m/Y'),
                    "plan_rate" => "[{$package->plan_rates[0]->service_type->code}] - {$package->plan_rates[0]->name}"
                ];
            }

            $data["user"] = "AURORA (HYPERGUEST)";

            $data["rate_plan_name"] = $ratePlan->name;
            $data["rate_plan_id"] = $ratePlanId;

            $rate_plan = RatesPlans::where('id', $ratePlanId)->with('hotel.channels')->first();
            $data["hotel"] = $rate_plan->hotel->channels[0]->pivot->code . ' - ' . $rate_plan->hotel->name;

            $mail = Mail::to("producto@limatours.com.pe");
            $mail->cc(["neg@limatours.com.pe","kams@limatours.com.pe", "qr@limatours.com.pe"]);
            $mail->send(new NotificationRatePlansStatusHyperguest($data));
        }
    }

    private function get_room_uses($room_id)
    {

        $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('room_id', $room_id)->pluck('id');

        $package_service_rooms_package_service_ids = DB::table('package_service_rooms')
            ->whereIn('rate_plan_room_id', $rate_plan_room_ids)
            ->pluck('package_service_id');

        $package_service_rooms_package_service_hyperguest_ids = DB::table('package_service_rooms_hyperguest')
            ->where('room_id', $room_id)
            ->pluck('package_service_id');

        $package_service_rooms_all_ids = $package_service_rooms_package_service_ids->merge(
            $package_service_rooms_package_service_hyperguest_ids
        )->unique();

        $packages = Package::select('id', 'code', 'extension')
            ->whereHas('plan_rates.plan_rate_categories.services',
                function ($query) use ($package_service_rooms_all_ids) {
                    $query->whereIn('id', $package_service_rooms_all_ids);
                })
            ->with([
                'plan_rates' => function ($query) use ($package_service_rooms_all_ids) {
                    $query->with(['service_type']);
                    $query->with([
                        'plan_rate_categories' => function ($query) use ($package_service_rooms_all_ids) {
                            $query->with([
                                'services' => function ($query2) use ($package_service_rooms_all_ids) {
                                    $query2->select('id', 'type', 'package_plan_rate_category_id', 'object_id',
                                        'date_in', 'date_out', 'order');
                                    $query2->where("type", "hotel");
                                    $query2->whereIn("id", $package_service_rooms_all_ids);
                                },
                            ]);
                            $query->with([
                                'category' => function ($query) {
                                    $query->with([
                                        'translations' => function ($q) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', 1);
                                        },
                                    ]);

                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->with([
                'translations' => function ($query) {
                    $query->where('language_id', 1);
                },
            ])
            ->where('status', 1)
            ->get();

        return $packages;
    }

    private function get_rate_plan_uses($rate_plan_id)
    {

        $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('rates_plans_id', $rate_plan_id)->pluck('id');

        $package_service_rooms_package_service_ids = DB::table('package_service_rooms')
            ->whereIn('rate_plan_room_id', $rate_plan_room_ids)
            ->pluck('package_service_id');

        $package_service_rooms_package_service_hyperguest_ids = DB::table('package_service_rooms_hyperguest')
            ->whereIn('rate_plan_id', [$rate_plan_id])
            ->pluck('package_service_id');

        $package_service_rooms_package_service_ids = $package_service_rooms_package_service_ids->merge(
            $package_service_rooms_package_service_hyperguest_ids
        )->unique();

        $packages = Package::select('id', 'code', 'extension')
            ->whereHas('plan_rates.plan_rate_categories.services',
                function ($query) use ($package_service_rooms_package_service_ids) {
                    $query->whereIn('id', $package_service_rooms_package_service_ids);
                })
            ->with([
                'plan_rates' => function ($query) use ($package_service_rooms_package_service_ids) {
                    $query->with(['service_type']);
                    $query->with([
                        'plan_rate_categories' => function ($query) use ($package_service_rooms_package_service_ids) {
                            $query->with([
                                'services' => function ($query2) use ($package_service_rooms_package_service_ids) {
                                    $query2->select('id', 'type', 'package_plan_rate_category_id', 'object_id',
                                        'date_in', 'date_out', 'order');
                                    $query2->where("type", "hotel");
                                    $query2->whereIn("id", $package_service_rooms_package_service_ids);
                                },
                            ]);
                            $query->with([
                                'category' => function ($query) {
                                    $query->with([
                                        'translations' => function ($q) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', 1);
                                        },
                                    ]);

                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->with([
                'translations' => function ($query) {
                    $query->where('language_id', 1);
                },
            ])
            ->where('status', 1)
            ->get();

        return $packages;
    }

    /**
     * Registra un error para un hotel específico en el batch
     */
    protected function registrarErrorHotel($hotelIdExternal, string $errorMessage): void
    {
        // Solo registrar si hay un batchId configurado
        if (isset($this->batchId) && $this->batchId && $hotelIdExternal) {
            try {
                $batch = \App\HyperguestHotelImportBatch::find($this->batchId);
                if ($batch) {
                    // Registrar el error solo en error_message como JSON
                    $errorMessages = $batch->error_message ?? [];
                    if (!is_array($errorMessages)) {
                        $errorMessages = [];
                    }

                    // Truncar el mensaje de error
                    $errorTruncated = substr($errorMessage, 0, 500); // Limitar tamaño

                    // Agregar o actualizar el error para este hotel
                    $errorMessages[$hotelIdExternal] = $errorTruncated;

                    $batch->error_message = $errorMessages;
                    $batch->save();
                }
            } catch (\Exception $e) {
            }
        }
    }

}
