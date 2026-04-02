<?php

namespace App\Http\Controllers\Pentagrama;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pentagrama\Models\ExtensionPentagramaDetailService;
use App\Http\Traits\CalculateCancellationPolicies as TraitCalculateCancellationPolicies;
use App\Http\Traits\Package as TraitPackage;
use App\Http\Traits\QuoteDetailsPricePassengers;
use App\Http\Traits\QuoteDetailsPriceRange;
use App\Http\Traits\QuoteHistories;
use App\Http\Traits\Quotes;
use App\Models\ChannelHotel;
use App\Models\ClientSeller;
use App\Models\QuoteAccommodation;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PentagramaController extends Controller
{
    use Quotes;
    use TraitPackage;
    use QuoteHistories;
    use TraitCalculateCancellationPolicies;
    use QuoteDetailsPricePassengers;
    use QuoteDetailsPriceRange;

    protected function normalizeTypeService(string $rawType, string $description = ''): string
    {
        $lower = strtolower(trim($rawType . ' ' . $description));

        if (stripos($lower, 'hotel') !== false || stripos($lower, 'turista superior') !== false || stripos($lower, '(4* - superior)') !== false)
            return 'hotel';
        if (stripos($lower, 'trf') !== false || stripos($lower, 'transfer') !== false || stripos($lower, 'btc') !== false || stripos($lower, 'transferencia') !== false || stripos($lower, 'traslado') !== false)
            return 'service';
        if (stripos($lower, 'h/d') !== false || stripos($lower, 'f/d') !== false || stripos($lower, 'tour') !== false)
            return 'service';
        if (str_contains($lower, 'flight') || str_contains($lower, 'flt'))
            return 'flight';
        if (str_contains($lower, 'restaurant'))
            return 'service';

        return 'service';
    }

    private function buildItinerariesWithErrors(array $data_itineraries, int $adults, int $children): array
    {
        $errors = [];

        $itineraries = collect($data_itineraries)->map(function ($item, $i) use ($adults, $children, &$errors) {

            $entity = $this->normalizeTypeService($item['type_service'] ?? null);

            // Rooms solo aplica a hotel (si quieres que aplique a otros, ajustas aquí)
            $rooms = $this->buildRoomsForItem($item, $entity, $adults, $children, $errors, $i);

            // IDs según entidad + errores si faltan
            [$objectId, $serviceRateId] = $this->resolveEntityIds($entity, $item, $errors, $i);

            // Fechas seguras
            $singleDate = data_get($item, 'single_date');
            $dateIn = $singleDate ? Carbon::parse($singleDate)->format('Y-m-d') : null;
            $dateOut = $singleDate ? Carbon::parse($singleDate)->addDays(1)->format('Y-m-d') : null;

//            if (!$singleDate) {
//                $errors[] = $this->makeError(
//                    'SINGLE_DATE_NULL',
//                    'general',
//                    $i,
//                    $item,
//                    'No se encontró single_date para este item.'
//                );
//            }

            return [
                'id' => data_get($item, 'id'),
                'external_service_id' => data_get($item, 'external_service_id'),
                'description' => data_get($item, 'external_service_description'),
                'entity' => $entity,

                'object_id' => $objectId,
                'service_rate_id' => $serviceRateId,

                'date_in' => $dateIn,
                'date_out' => $dateOut,
                'start_time' => null,

                'total_adults' => $adults,
                'total_children' => $children,
                'total_infants' => 0,

                'confirmation_status' => 0, // 0 = RQ | 1 = OK
                'status' => '1',
                'total_amount' => 0,
                'total_cost_amount' => 0,

                'rooms' => $rooms,
            ];
        })->values();

        return [$itineraries, $errors];
    }

    private function buildRoomsForItem(array $item, string $entity, int $adults, int $children, array &$errors, int $index)
    {
        if ($entity !== 'hotel') {
            return collect([]); // services/flight no tienen rooms aquí
        }

        $ratesPlansRooms = collect(data_get($item, 'type_hotel.hotel.rates_plans_rooms', []));
        $totalCapacity = $adults + $children;

        $filtered = $ratesPlansRooms->filter(function ($r) use ($adults, $children, $totalCapacity) {
            $maxCapacity = (int)data_get($r, 'room.max_capacity', 0);
            $state = (int)data_get($r, 'room.state', 0);
            $maxAdults = (int)data_get($r, 'room.max_adults', 0);
            $maxChild = (int)data_get($r, 'room.max_child', 0);

            return $maxCapacity >= $totalCapacity
                && $state === 1
                && $maxAdults >= $adults
                && $maxChild >= $children;
        })->values();

//        if ($filtered->isEmpty()) {
//            $errors[] = $this->makeError(
//                'NO_ROOMS_AVAILABLE',
//                'hotel',
//                $index,
//                $item,
//                "No hay habitaciones disponibles para {$adults} adulto(s) y {$children} niño(s)."
//            );
//            return collect([]);
//        }

        // Arma payload rooms y toma 1
        return $filtered->map(function ($ratePlanRoom) use ($adults, $children) {
            return $this->buildRoomPayload($ratePlanRoom, $adults, $children);
        })->take(1)->values();
    }

    private function buildRoomPayload(array $ratePlanRoom, int $adults, int $children): array
    {
        $single = 0;
        $double = 0;
        $triple = 0;

        if ($adults === 1) {
            $single = 1;
        } elseif ($adults === 2) { // ✅ FIX (era = 2)
            $double = 1;
        } elseif ($adults >= 3) {
            $triple = 1;
        }

        return [
            'total_adults' => $adults,
            'total_children' => $children,
            'single' => $single,
            'double' => $double,
            'triple' => $triple,

            'confirmation_status' => 0,
            'status' => data_get($ratePlanRoom, 'status'),
            'rate_plan_room_id' => data_get($ratePlanRoom, 'id'),
            'occupation' => $adults,
        ];
    }

    private function resolveEntityIds(string $entity, array $item, array &$errors, int $index): array
    {
        $objectId = null;
        $serviceRateId = null;

        if ($entity === 'hotel') {
            $objectId = data_get($item, 'type_hotel.hotel_id');

            if (!$objectId) {
                $errors[] = $this->makeError(
                    'HOTEL_OBJECT_ID_NULL',
                    'hotel',
                    $index,
                    $item,
                    'No se encontró CODIGO de hotel en Aurora.'
                );
            }

            return [$objectId, null];
        }

        if ($entity === 'service' || $entity === 'flight') {
            $objectId = data_get($item, 'external_type_service.id');
            $serviceRateId = data_get($item, 'external_type_service.service_rate.0.id');

            if (!$objectId) {
                $errors[] = $this->makeError(
                    strtoupper($entity) . '_OBJECT_ID_NULL',
                    $entity,
                    $index,
                    $item,
                    "No se encontró CODIGO ($entity) en Aurora."
                );
            }

            // Si en tu negocio service_rate_id es obligatorio, deja esta validación:
            if ($objectId && !$serviceRateId) {
                $errors[] = $this->makeError(
                    strtoupper($entity) . '_SERVICE_RATE_ID_NULL',
                    $entity,
                    $index,
                    $item,
                    "No se encontró TARIFA ($entity) en Aurora."
                );
            }

            return [$objectId, $serviceRateId];
        }

        $errors[] = $this->makeError(
            'ENTITY_NOT_SUPPORTED',
            'general',
            $index,
            $item,
            "Entidad no soportada: {$entity}."
        );

        return [null, null];
    }

    private function makeError(string $code, string $entity, int $index, array $item, string $message): array
    {
        return [
            'code' => $code,
            'entity' => $entity,
            'index' => $index,
            'id' => data_get($item, 'id'),
            'external_service_id' => data_get($item, 'external_service_id'),
            'description' => data_get($item, 'external_service_description'),
            'message' => $message,
        ];
    }

    public function generateQuote(Request $request): JsonResponse
    {
        // Datos para autogenerar Cotización
        $quote_name = 'Cotización Autogenerada ' . Carbon::now()->format('YmdHis');
        $file_id = null;
        $file_number = null;
        $total_amount = 0.00;
        $service_type_id = 1;
        $user_id = Auth::user()->id;
        $operation = 'passengers';
        $discount = 0;
        $discount_detail = null;
        $order_position = 1;
        $type_class_id = 2;
        $adults = 1;
        $children = 0;
        $passengers = [
            ["type" => "ADL", "name" => "AUTOGENERADO", "surnames" => "AUTOGENERADO", "genre" => "M", "date_birth" => "1999-01-01", "document_number" => null, "doctype_iso" => "DNI", "country_iso" => null],
        ];
        $extension_pentagrama_service_id = $request->input('extension_pentagrama_service_id');
        $status = 1; // 0 => borrador, 1 => confirmada

        // OBTENER ITINERARIOS MOZART
        $data_itineraries = ExtensionPentagramaDetailService::where('extension_pentagrama_service_id', $extension_pentagrama_service_id)
            ->with(['external_type_service', 'type_hotel'])
            ->orderBy('single_date')
            ->orderBy('id')->get()->toArray();

        // Asegúrate de que el arreglo no esté vacío
        if (empty($data_itineraries)) {
            return response()->json([
                'ok' => false,
                'message' => 'No se encontró itinerario cargado.',
            ], 422);
        }

        // Obtener la primera y última fecha del itinerario para calcular date_in, date_estimated y nights
        $firstItinerary = $data_itineraries[0];
        $lastItinerary = $data_itineraries[count($data_itineraries) - 1];

        // Asegurarse de que las fechas estén en el formato adecuado (Y-m-d H:i:s)
        $date_in = Carbon::parse($firstItinerary['single_date'])->format('Y-m-d H:i:s');
        $date_estimated = Carbon::parse($lastItinerary['single_date'])->format('Y-m-d H:i:s');
        $nights = Carbon::parse($date_in)->diffInDays(Carbon::parse($date_estimated));

        [$itineraries, $errors] = $this->buildItinerariesWithErrors($data_itineraries, $adults, $children);

        if (!empty($errors)) {
            return response()->json([
                'ok' => false,
                'message' => 'Se encontraron errores al construir el itinerario.',
                'errors' => $errors,
            ], 422);
        }

        // Obtener el client_id enviado desde el frontend o desde el token
        $client_id = $this->getClientId($request);

        // Obtener solo el valor del campo 'hotel' directamente
        $markup = DB::table('markups')
            ->whereNull('deleted_at')
            ->where('client_id', $client_id)
            ->where('period', Carbon::now()->year)
            ->value('hotel'); // Devuelve el valor del campo 'hotel' o null si no existe

        // Si no se encuentra ningún valor, $markup será 0 (valor por defecto)
        $markup = $markup ?? 0;

        $this->newQuote(
            $quote_name,
            $date_in,
            $date_estimated,
            $nights,
            $service_type_id,
            $user_id,
            [],
            [],
            [],
            [],
            [],
            $operation,
            $status,
            $markup,
            $discount,
            $discount_detail,
            null,
            $order_position,
            $file_id,
            $file_number,
            $total_amount
        );
        $new_quote_id = $this->getObjectId();

        $quoteCategoryInsert = [
            'quote_id' => $new_quote_id,
            'type_class_id' => $type_class_id,
            'created_at' => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
        $new_category_id = DB::table('quote_categories')->insertGetId($quoteCategoryInsert);

        foreach ($passengers as $index => $passenger) {
            $passengers[$index]['age'] = null;
            $quote_age_child_id = null;
            if ($passenger['type'] == 'CHD') {
                $years = 1;

                if ($passenger['date_birth']) {
                    $years = Carbon::parse($passenger['date_birth'])->age;
                    $passengers[$index]['age'] = $years;
                }

                $quote_age_child_id = DB::table('quote_age_child')->insertGetId([
                    'quote_id' => $new_quote_id,
                    'age' => $years,
                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            $quote_passenger_id = DB::table('quote_passengers')->insertGetId([
                'quote_id' => $new_quote_id,
                'first_name' => $passenger['name'],
                'last_name' => $passenger['surnames'],
                'gender' => $passenger['genre'],
                'birthday' => $passenger['date_birth'],
                'document_number' => $passenger['document_number'],
                'doctype_iso' => $passenger['doctype_iso'],
                'country_iso' => $passenger['country_iso'],
                'type' => $passenger['type'],
                'quote_age_child_id' => $quote_age_child_id,
                'created_at' => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            $passengers[$index]['quote_passenger_id'] = $quote_passenger_id;
        }

        $quoteDistributions = [];
        $accommodation = [];
        $orderItinerary = 0;

        foreach ($itineraries as $service) {
            $orderItinerary++;
            $locked = 0;
            $nights = Carbon::parse($service['date_in'])->diffInDays(Carbon::parse($service['date_out']));

            if ($service['entity'] == 'service') {

                $quoteServiceInsert = [
                    'quote_category_id' => $new_category_id,
                    'type' => $service['entity'],
                    'object_id' => $service['object_id'],
                    'order' => $orderItinerary,
                    'date_in' => $service['date_in'],
                    'date_out' => $service['date_out'],
                    'hour_in' => $service['start_time'],
                    'nights' => $nights,
                    'adult' => $service['total_adults'],
                    'child' => $service['total_children'],
                    'infant' => $service['total_infants'],
                    'single' => 0,
                    'double' => 0,
                    'triple' => 0,
                    'locked' => $locked,
                    'triple_active' => 0,
                    'on_request' => $service['confirmation_status'],
                    'optional' => 0,
                    'code_flight' => null,
                    'origin' => null,
                    'destiny' => null,
                    'extension_id' => null,
                    'parent_service_id' => null,
                    'schedule_id' => null,

                    /* TODO: Esto no usaremos */
                    'is_file' => 0,
                    'file_itinerary_id' => null,
                    'file_status' => 0,
                    'file_amount_sale' => 0,
                    'file_amount_cost' => 0,
                    /* TODO: Esto no usaremos */

                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                $new_service_id = DB::table('quote_services')->insertGetId($quoteServiceInsert);

                $quoteServiceRateInsert = [
                    'quote_service_id' => $new_service_id,
                    'service_rate_id' => $service['service_rate_id'],
                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                DB::table('quote_service_rates')->insert($quoteServiceRateInsert);

                foreach ($passengers as $passenger) {
                    $passengerInsert = [
                        'quote_service_id' => $new_service_id,
                        'quote_passenger_id' => $passenger['quote_passenger_id'],
                        'created_at' => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ];
                    DB::table('quote_service_passengers')->insert($passengerInsert);
                }
            }

            if ($service['entity'] == 'flight') {
                $quoteServiceInsert = [
                    'quote_category_id' => $new_category_id,
                    'type' => $service['entity'],
                    'object_id' => $service['object_id'],
                    'order' => $orderItinerary,
                    'date_in' => $service['date_in'],
                    'date_out' => $service['date_out'],
                    'hour_in' => $service['start_time'],
                    'nights' => $nights,
                    'adult' => $service['total_adults'],
                    'child' => $service['total_children'],
                    'infant' => $service['total_infants'],
                    'single' => 0,
                    'double' => 0,
                    'triple' => 0,
                    'locked' => $locked,
                    'triple_active' => 0,
                    'on_request' => $service['confirmation_status'],
                    'optional' => 0,
                    'code_flight' => $service['object_code'],
                    'origin' => null,
                    'destiny' => null,
                    'extension_id' => null,
                    'parent_service_id' => null,
                    'schedule_id' => null,

                    /* TODO: Esto no usaremos */
                    'is_file' => 0,
                    'file_itinerary_id' => null,
                    'file_status' => 0,
                    'file_amount_sale' => 0,
                    'file_amount_cost' => 0,
                    /* TODO: Esto no usaremos */

                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                $new_service_id = DB::table('quote_services')->insertGetId($quoteServiceInsert);
            }

            if ($service['entity'] == 'hotel') {
                $order = 1;
                $single = 0;
                $double = 0;
                $triple = 0;
                $quoteDistributionRooms = [];

                foreach ($service['rooms'] as $room) {
                    $quoteServiceInsert = [
                        'quote_category_id' => $new_category_id,
                        'type' => $service['entity'],
                        'object_id' => $service['object_id'],
                        'order' => $orderItinerary,
                        'date_in' => $service['date_in'],
                        'date_out' => $service['date_out'],
                        'hour_in' => $service['start_time'],
                        'nights' => $nights,
                        'adult' => $room['total_adults'],
                        'child' => $room['total_children'],
                        'infant' => $service['total_infants'],
                        'single' => $room['single'],
                        'double' => $room['double'],
                        'triple' => $room['triple'],
                        'locked' => $locked,
                        'triple_active' => 0,
                        'on_request' => $room['confirmation_status'],
                        'optional' => 0,
                        'code_flight' => null,
                        'origin' => null,
                        'destiny' => null,
                        'extension_id' => null,
                        'parent_service_id' => null,
                        'schedule_id' => null,

                        /* TODO: Esto no usaremos */
                        'is_file' => 0,
                        'file_itinerary_id' => null,
                        'file_status' => 0,
                        'file_amount_sale' => 0,
                        'file_amount_cost' => 0,
                        /* TODO: Esto no usaremos */

                        'created_at' => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ];
                    $new_service_id = DB::table('quote_services')->insertGetId($quoteServiceInsert);

                    $quoteServiceRoomInsert = [
                        'quote_service_id' => $new_service_id,
                        'rate_plan_room_id' => $room['rate_plan_room_id'],
                        'created_at' => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ];
                    DB::table('quote_service_rooms')->insert($quoteServiceRoomInsert);

                    foreach ($passengers as $passenger) {
                        $passengerInsert = [
                            'quote_service_id' => $new_service_id,
                            'quote_passenger_id' => $passenger['quote_passenger_id'],
                            'created_at' => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ];
                        DB::table('quote_service_passengers')->insert($passengerInsert);
                    }

                    if (count($quoteDistributions) == 0) {
                        $typeRoom = '';
                        $typeRoomName = '';
                        if ($room['occupation'] == 1) {
                            $typeRoom = 'single';
                            $typeRoomName = 'SGL';
                            $single++;
                        }
                        if ($room['occupation'] == 2) {
                            $typeRoom = 'double';
                            $typeRoomName = 'DBL';
                            $double++;
                        }
                        if ($room['occupation'] == 3) {
                            $typeRoom = 'triple';
                            $typeRoomName = 'TPL';
                            $triple++;
                        }

                        $item = [
                            'type_room' => $typeRoom,
                            'type_room_name' => $typeRoomName,
                            'occupation' => $room['occupation'],
                            'single' => $room['single'],
                            'double' => $room['double'],
                            'triple' => $room['triple'],
                            'adult' => $room['total_adults'],
                            'child' => $room['total_children'],
                            'order' => $order,
                            'quote_id' => $new_quote_id,
                            'passengers' => $passengers,
                        ];
                        $quoteDistributionRooms[] = $item;

                        $order++;
                    }
                }

                if (count($quoteDistributions) == 0) {
                    $accommodation['single'] = $single;
                    $accommodation['double'] = $double;
                    $accommodation['double_child'] = 0;
                    $accommodation['triple'] = $triple;
                    $accommodation['triple_child'] = 0;
                    $quoteDistributions = $quoteDistributionRooms;
                }
            }
        }

        $quotePeopleInsert = [
            'adults' => $adults,
            'child' => $children,
            'quote_id' => $new_quote_id,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
        DB::table('quote_people')->insert($quotePeopleInsert);

        foreach ($quoteDistributions as $distribution) {
            $quoteDistInsert = [
                'type_room' => $distribution['type_room'],
                'type_room_name' => $distribution['type_room_name'],
                'occupation' => $distribution['occupation'],
                'single' => $distribution['single'],
                'double' => $distribution['double'],
                'triple' => $distribution['triple'],
                'adult' => $distribution['adult'],
                'child' => $distribution['child'],
                'order' => $distribution['order'],
                'quote_id' => $new_quote_id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ];
            $quote_distribution = DB::table('quote_distributions')->insertGetId($quoteDistInsert);
            foreach ($distribution['passengers'] as $passenger) {
                $passengerInsert = [
                    'quote_distribution_id' => $quote_distribution,
                    'quote_passenger_id' => $passenger['quote_passenger_id'],
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                DB::table('quote_distribution_passengers')->insertGetId($passengerInsert);
            }
        }

        if (count($accommodation) > 0) {
            $new_accommodation = new QuoteAccommodation();
            $new_accommodation->quote_id = $new_quote_id;
            $new_accommodation->single = $accommodation['single'];
            $new_accommodation->double = $accommodation['double'];
            $new_accommodation->double_child = $accommodation['double_child'];
            $new_accommodation->triple = $accommodation['triple'];
            $new_accommodation->triple_child = $accommodation['triple_child'];
            $new_accommodation->save();
        }

        return response()->json(['success' => true, 'quote_id' => $new_quote_id]);
    }
}
