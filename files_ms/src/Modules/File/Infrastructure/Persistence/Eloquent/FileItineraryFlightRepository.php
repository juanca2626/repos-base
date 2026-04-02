<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Modules\File\Application\Mappers\FileItineraryMapper;
use Src\Modules\File\Application\Mappers\FileItineraryFlightMapper;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Application\Mappers\FileItineraryFlightAccommodationMapper;
use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryFlightEloquentModel;

class FileItineraryFlightRepository implements FileItineraryFlightRepositoryInterface
{

    public function findById(int $id): array
    {
        $fileServiceEloquent = FileItineraryFlightEloquentModel::query()->with([
            'fileItinerary.file',
        ])->findOrFail($id);
        return $fileServiceEloquent->toArray();
    }

    public function create(int $file_id,FileItineraryFlight $fileItineraryFlight): FileItineraryFlight
    {
        return DB::transaction(function () use ($file_id, $fileItineraryFlight) {
            $fileItineraryEloquent = $this->saveFileItineraryFlight($fileItineraryFlight);
            event(new FilePassToOpeEvent($file_id));
            return FileItineraryFlightMapper::fromEloquent($fileItineraryEloquent);
        });
    }

    protected function saveFileItineraryFlight(FileItineraryFlight $fileItineraryFlight): FileItineraryFlightEloquentModel
    {
        $fileItineraryEloquent = FileItineraryFlightMapper::toEloquent($fileItineraryFlight);
        $fileItineraryEloquent->save();
        $this->saveFileFlightAccommodations($fileItineraryEloquent, $fileItineraryFlight->accommodations);
        return $fileItineraryEloquent;
    }

    protected function saveFileFlightAccommodations(
        FileItineraryFlightEloquentModel $fileItineraryFlightEloquentModel,
        $fileItineraryFlightAccommodationsData
    ): void {
        foreach ($fileItineraryFlightAccommodationsData as $accommodations) {

            $fileItineraryFlightAccommodationMapper = FileItineraryFlightAccommodationMapper::toEloquent($accommodations);
            $fileItineraryFlightEloquentModel->accommodations()->save($fileItineraryFlightAccommodationMapper);

        }
    }

    public function update(int $file_id,FileItineraryFlight $fileItineraryFlight): FileItineraryFlight
    {
        return DB::transaction(function () use ($file_id, $fileItineraryFlight) {
            $fileItineraryEloquent = $this->updateFileItineraryFlight($fileItineraryFlight);
            event(new FilePassToOpeEvent($file_id));
            return FileItineraryFlightMapper::fromEloquent($fileItineraryEloquent);
        });
    }

    protected function updateFileItineraryFlight(FileItineraryFlight $fileItineraryFlight): FileItineraryFlightEloquentModel
    {
        $fileItineraryEloquent = FileItineraryFlightMapper::toEloquent($fileItineraryFlight);
        // dd($fileItineraryEloquent->toArray());
        $fileItineraryEloquent->save();
        $fileItineraryEloquent->accommodations()->delete();
        $this->saveFileFlightAccommodations($fileItineraryEloquent, $fileItineraryFlight->accommodations);
        return $fileItineraryEloquent;
    }

    public function destroy(int $file_id, int $fileItineraryFlightId): array
    {
        $fileItineraryFlightEloquentModel = FileItineraryFlightEloquentModel::with('fileItinerary')->findOrFail($fileItineraryFlightId);
        $flight = $fileItineraryFlightEloquentModel->toArray();
        $fileItineraryFlightEloquentModel->delete();
        event(new FilePassToOpeEvent($file_id));

        return $flight;
    }

    public function updateCityIso(int $file_id, int $fileItineraryFlightId, array $params): array
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'file',
        ])->find($fileItineraryFlightId);

        if($params['type_flight'] == 'in'){
            $fileItineraryEloquent->city_in_iso     = $params['city_iso'];
            $fileItineraryEloquent->city_in_name     = $params['city_name'];
            $fileItineraryEloquent->country_in_iso     = $params['country_iso'];
            $fileItineraryEloquent->country_in_name     = $params['country_name'];
        }else{
            $fileItineraryEloquent->city_out_iso    = $params['city_iso'];
            $fileItineraryEloquent->city_out_name     = $params['city_name'];
            $fileItineraryEloquent->country_out_iso     = $params['country_iso'];
            $fileItineraryEloquent->country_out_name     = $params['country_name'];
        }
        $fileItineraryEloquent->update();

        return [
            "file_number"   => $fileItineraryEloquent->file->file_number,
            "date"          => $fileItineraryEloquent->date_in
        ];
    }

    public function updateDate(int $file_id, int $id, array $params): array
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'file',
        ])->find($id);

        $fileItineraryEloquent->date_in = $params['date'];
        $fileItineraryEloquent->save();

        return [
            "file_number"   => $fileItineraryEloquent->file->file_number
        ];
    }

    public function updateItineraryTime(int $itineraryId) : bool {
        $fileItineraryEloquent = FileItineraryEloquentModel::find($itineraryId);
        $flights = $fileItineraryEloquent->flights()->get()->toArray();
        if($fileItineraryEloquent->object_code === "AEIFLT" && $fileItineraryEloquent->city_in_iso === "LIM"){
            $minorDeparture = max(array_column($flights, 'departure_time'));
        }else{
            $minorDeparture = min(array_column($flights, 'departure_time'));
        }
        $greatestArrival = max(array_column($flights, 'arrival_time'));
        $fileItineraryEloquent->start_time = $minorDeparture;
        $fileItineraryEloquent->departure_time = $greatestArrival;
        $fileItineraryEloquent->save();
        return true;
    }
}
