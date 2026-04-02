<?php

namespace Src\Modules\Passengers\Application\Transformers;

use Src\Modules\Passengers\Domain\Models\Passenger; 
use Src\Modules\Passengers\Domain\Helpers\RoomTypeHelper;
use Src\Shared\Domain\Services\PhoneService;
use Carbon\Carbon;

class PassengerTransformer
{
    public static function transform(Passenger $passenger): array
    {
        $phoneService = app(PhoneService::class);

        $phone = $phoneService->parse(
            $passenger->phone,
            $passenger->country_iso
        );

        return [
            'id' => $passenger->id,
            'file_id' => $passenger->file_id,
            'name' => $passenger->name,
            'surnames' => $passenger->surnames,
            'document_number' => $passenger->document_number,

            'type' => $passenger->type,
            'genre' => $passenger->genre,

            'room_type_description' => RoomTypeHelper::getDescription(
                $passenger->suggested_room_type
            ),

            'date_birth' => $passenger->date_birth,
            'date_birth_format' => $passenger->date_birth
                ? Carbon::parse($passenger->date_birth)->format('d/m/Y')
                : null,

            'phone_code' => $phone['phone_code'],
            'phone_number' => $phone['phone_number'],

            'email' => $passenger->email,
        ];
    }
}