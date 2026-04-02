<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Repositories\FileNoteExternalHousingRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteExternalHousingEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteExternalHousingPassengersEloquentModel;

class FileNoteExternalHousingRepository implements FileNoteExternalHousingRepositoryInterface{
    public function index(int $file_id): array {
        $list = FileNoteExternalHousingEloquentModel::query()->with('passengers')->where('file_id',$file_id)->orderBy('date_check_in', 'ASC')->get();
        return $list->toArray();
    }

    public function findById(int $file_id, int $id): array {
        $record = FileNoteExternalHousingEloquentModel::with('passengers')->find($id);
        return $record ? $record->toArray() : [];
    }

    public function store(int $file_id, array $params): array{
        $FileNoteExternalHousingEloquentModel = FileNoteExternalHousingEloquentModel::create([
            'date_check_in'               => $params['date_check_in'],
            'date_check_out'              => $params['date_check_out'],
            'accommodation_name'          => $params['name_housing'],
            'accommodation_code_phone'    => $params['code_phone'],
            'accommodation_number_phone'  => $params['number_phone'],
            'accommodation_address'       => $params['address'],
            'accommodation_lat'           => $params['lat'],
            'accommodation_lng'           => $params['lng'],
            'city'                        => $params['city'],
            'created_by'                  => $params['created_by'],
            'created_by_code'             => $params['created_by_code'],
            'created_by_name'             => $params['created_by_name'],
            'file_id'                     => $file_id,
        ]);

        if(!empty($params['passengers'])){
            foreach($params['passengers'] as $passenger){
                FileNoteExternalHousingPassengersEloquentModel::create([
                    'file_notes_external_housing_id'    => $FileNoteExternalHousingEloquentModel->id,
                    'passengers_id'                     => $passenger
                ]);
            }
        }

        return $FileNoteExternalHousingEloquentModel
                ->load(['passengers'])
                ->toArray();
    }

    public function update(int $file_id, int $id , array $params): array{

        $record = DB::transaction(function () use ($file_id, $id, $params) {
            $FileNoteExternalHousingEloquentModel = FileNoteExternalHousingEloquentModel::find($id);

            $FileNoteExternalHousingEloquentModel->date_check_in = $params['date_check_in'];
            $FileNoteExternalHousingEloquentModel->date_check_out = $params['date_check_out'];
            $FileNoteExternalHousingEloquentModel->accommodation_name = $params['name_housing'];
            $FileNoteExternalHousingEloquentModel->accommodation_code_phone = $params['code_phone'];
            $FileNoteExternalHousingEloquentModel->accommodation_number_phone = $params['number_phone'];
            $FileNoteExternalHousingEloquentModel->accommodation_address = $params['address'];
            $FileNoteExternalHousingEloquentModel->accommodation_lat = $params['lat'];
            $FileNoteExternalHousingEloquentModel->accommodation_lng = $params['lng'];
            $FileNoteExternalHousingEloquentModel->file_id = $file_id;
            $FileNoteExternalHousingEloquentModel->city = $params['city'];
            $FileNoteExternalHousingEloquentModel->created_by = $params['created_by'];
            $FileNoteExternalHousingEloquentModel->created_by_code = $params['created_by_code'];
            $FileNoteExternalHousingEloquentModel->created_by_name = $params['created_by_name'];
            $FileNoteExternalHousingEloquentModel->updated_at = now();

            $FileNoteExternalHousingEloquentModel->update();

            $passengerIds = $params['passengers'];
            $currentIds = $FileNoteExternalHousingEloquentModel->passengers()->pluck('passengers_id')->toArray();

            $idsToDetach = array_diff($currentIds, $passengerIds);
            $idsToAttach = array_diff($passengerIds, $currentIds);

            if (!empty($idsToDetach)) {
                $FileNoteExternalHousingEloquentModel->passengers()
                    ->whereIn('passengers_id', $idsToDetach)
                    ->delete();
            }

            $attached = [];
            foreach ($idsToAttach as $id) {
                $existing = $FileNoteExternalHousingEloquentModel->passengers()
                    ->withTrashed()
                    ->where('passengers_id', $id)
                    ->first();

                if ($existing) {
                    // Restaurar si ya existía
                    $existing->restore();
                    $attached[] = $existing;
                } else {
                    // Crear nuevo
                    $attached[] = $FileNoteExternalHousingEloquentModel->passengers()->create(['passengers_id' => $id]);
                }
            }

            return $FileNoteExternalHousingEloquentModel
                    ->load(['passengers'])
                    ->toArray();
        });

        return $record;
    }

    public function delete(int $file_id, int $id): bool{
        DB::transaction(function () use ($id) {
            $fileNoteExternalHousing = FileNoteExternalHousingEloquentModel::findOrFail($id);

            // Eliminar todas las relaciones primero (con soft delete si está configurado)
            $fileNoteExternalHousing->passengers()->delete();

            // $fileNoteExternalHousing->created_by = $params['created_by'];
            // $fileNoteExternalHousing->created_by_code = $params['created_by_code'];
            // $fileNoteExternalHousing->created_by_name = $params['created_by_name'];
            // Luego eliminar el modelo principal
            $fileNoteExternalHousing->delete();
        });
        return true;
    }
}
