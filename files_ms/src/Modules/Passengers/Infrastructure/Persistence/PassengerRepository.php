<?php

namespace Src\Modules\Passengers\Infrastructure\Persistence;

use Src\Modules\Passengers\Domain\Models\Passenger;

class PassengerRepository
{
    public function create(int $fileId, array $data): Passenger
    {
        return Passenger::create([
            'file_id' => $fileId, 
            'sequence_number' => $data['sequence_number'],
            'order_number' => $data['order_number'],
            'frequent' => $data['frequent'],
            'document_type_id' => $data['document_type_id'],
            'doctype_iso' => $data['doctype_iso'],
            'document_number' => $data['document_number'],
            'name' => $data['name'],
            'surnames' => $data['surnames'],
            'date_birth' => $data['date_birth'],
            'type' => $data['type'],
            'suggested_room_type' => $data['suggested_room_type'],
            'genre' => $data['genre'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'country_iso' => $data['country_iso'],
            'city_iso' => $data['city_iso'],
            'dietary_restrictions' => $data['dietary_restrictions'],
            'medical_restrictions' => $data['medical_restrictions'],
            'notes' => $data['notes'],
            'accommodation' => NULL, // se actualiza en otro proceso
            'cost_by_passenger' => 0, // se actualiza en otro proceso
            'document_url' => $data['document_url']            
        ]);
    }

    public function byFile(int $fileId)
    {
        return Passenger::where('file_id', $fileId)->get();
    }
}