<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Application\Mappers\FilePassengerMapper;
use Src\Modules\File\Domain\Repositories\FilePassengerRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;

class FilePassengerRepository implements FilePassengerRepositoryInterface
{
    public function create(int $fileId): bool
    {
        return false;
    }


    /**
     * @param int $id
     * @param File $userData
     * @return bool
     */
    public function updateAll(int $fileId, array $filePassengers): bool
    {

        DB::transaction(function () use ($fileId, $filePassengers) {

            foreach($filePassengers as $filePassenger){

                // $accommodations = [];
                // foreach($filePassenger['accommodations'] as  $accommodation){
                //     $passenger_name = $this->getNamePassenger($filePassengers, $accommodation);
                //     array_push($accommodations,[
                //         'id' => $accommodation,
                //         'name' => $passenger_name
                //     ]);
                // }

                FilePassengerEloquentModel::find($filePassenger['id'])->update([
                    'document_type_id' => $filePassenger['document_type_id'],
                    'doctype_iso' => $filePassenger['doctype_iso'],
                    'document_number' => $filePassenger['document_number'],
                    'name' => $filePassenger['name'],
                    'surnames' => $filePassenger['surnames'],
                    'date_birth' => $filePassenger['date_birth'],
                    'type' => $filePassenger['type'],
                    'suggested_room_type' => $filePassenger['room_type'],
                    'genre' => $filePassenger['genre'],
                    'email' => $filePassenger['email'],
                    'phone' => $filePassenger['phone_code'] . $filePassenger['phone'],
                    // 'phone_code' => $filePassenger['phone_code'],
                    'country_iso' => $filePassenger['country_iso'],
                    'city_iso' => $filePassenger['city_iso'],
                    'dietary_restrictions' => $filePassenger['dietary_restrictions'],
                    'medical_restrictions' => $filePassenger['medical_restrictions'],
                    // 'notes' => $filePassenger['notes'],
                    'document_url' => $filePassenger['document_url']
                    // 'accommodation' => json_encode($accommodations)
                ]);

            }

            event(new FilePassToOpeEvent($fileId));

        });

        return true;

    }

    /**
     * @param int $id
     * @param File $userData
     * @return bool
     */
    public function updateAccommodation(int $fileId, array $filePassengers): bool
    {
        DB::transaction(function () use ($filePassengers) {
            foreach($filePassengers as $filePassenger){
                FilePassengerEloquentModel::find($filePassenger['file_passenger_id'])->update([
                    'accommodation' => json_encode($filePassenger['accommodation'])
                ]);
            }
        });
        return true;
    }




    public function getNamePassenger($filePassengers, $accommodation): string{

          $passenger = collect($filePassengers)->firstWhere('id', $accommodation);
          return $passenger ? $passenger['name'] .' '. $passenger['surnames'] : '';
    }

    public function searchAll(int $fileId): array
    {
        $filePassengers = FilePassengerEloquentModel::query()->where('file_id', '=', $fileId)->get()->toArray();
        // $filePassengers = FilePassengerMapper::fromArraySearch($filePassengers);
        return $filePassengers;
    }

    /**
     * @param int $id
     * @return File|null
     */
    public function findById(int $id): array
    {
        $filePassenger = FilePassengerEloquentModel::with('file')->find($id)->toArray();

        return $filePassenger;
    }

}
