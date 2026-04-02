<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteStatusHistoryEloquentModel;
use Src\Modules\File\Domain\Enums\FileNoteStatusHistoryEnum;
use Src\Modules\File\Domain\Enums\FileNoteStatusEnum;

class FileNoteItineraryRepository implements FileNoteItineraryRepositoryInterface{
    public function listFileNoteItinerary(int $fileId, int $id): array
    {
        $listNotesItinerary = FileNoteEloquentModel::where('file_id',$fileId)
                                                    ->where('file_itinerary_id',$id)
                                                    ->where('status','!=',0)
                                                    ->orderBy('created_at', 'DESC')
                                                    ->get()
                                                    ->map(function ($note) {
                                                        return [
                                                            'id' => $note->id,
                                                            'type_note' => $note->type_note,
                                                            'record_type'=> $note->record_type,
                                                            'assignment_mode' => $note->assignment_mode,
                                                            'dates' => $note->dates,
                                                            'description' => $note->description,
                                                            'file_classification_code' => $note->file_classification_code,
                                                            'file_classification_name' => $note->file_classification_name,
                                                            'file_itinerary_id' => $note->file_itinerary_id,
                                                            'file_mascara_id' => $note->file_mascara_id,
                                                            'file_id' => $note->file_id,
                                                            'status' => $note->status,
                                                            'created_by' => $note->created_by,
                                                            'created_by_code' => $note->created_by_code,
                                                            'created_by_name' => $note->created_by_name,
                                                            'created_date' => $note->updated_at != null ? $note->updated_at->format('d/m/Y') : $note->created_at->format('d/m/Y'),
                                                            'created_time' => $note->updated_at != null ? $note->updated_at->format('G:i:s') : $note->created_at->format('G:i:s'),
                                                        ];
                                                    });
        return $listNotesItinerary->toArray();
    }

    public function storeFileNoteItinerary(int $fileNoteId,int $id, array $params): array
    {
        $fileNoteEloquent = FileNoteEloquentModel::create([
            'type_note'                 => $params['type_note'],
            'record_type'               => $params['record_type'],
            'assignment_mode'           => $params['assignment_mode'],
            'dates'                     => $params['dates'],
            'description'               => $params['description'],
            'file_classification_code'  => $params['classification_code'],
            'file_classification_name'  => $params['classification_name'],
            'file_itinerary_id'         => $id,
            'file_id'                   => $fileNoteId,
            'created_by'                => $params['created_by'],
            'created_by_code'           => $params['created_by_code'],
            'created_by_name'           => $params['created_by_name'],
            'status'                    => ($params['type_note'] === "INFORMATIVE" ? FileNoteStatusEnum::ACTIVE : FileNoteStatusEnum::PENDING),
            'created_at'                => now(),
            'updated_at'                => NULL,
        ]);

        // AGREGAR EL ENVIO PARA GENERAR UN STATUS
        if($params['type_note'] === "REQUIREMENT"){
            FileNoteStatusHistoryEloquentModel::create([
                'status'            => FileNoteStatusHistoryEnum::PENDING,
                'date'              => now(),
                'comment'           => 'NOTE CREATION',
                'file_note_id'      => $fileNoteEloquent->id,
                'user_id'           => $params['created_by'],
                'user_by_code'      => $params['created_by_code'],
                'user_by_name'      => $params['created_by_name']
            ]);
        }

        return [
            "id" => $fileNoteEloquent->id,
            "type_note" => $fileNoteEloquent->type_note,
            "record_type" => $fileNoteEloquent->record_type
        ];
    }

    public function updateFileNoteItinerary(int $fileNoteId, int $itinerary_id, int $note_id, array $params): array
    {
        $fileNoteItinerary = FileNoteEloquentModel::findOrFail($note_id);
        $response = [];

        $fileNoteItinerary->type_note                   = $params['type_note'];
        $fileNoteItinerary->record_type                 = $params['record_type'];
        $fileNoteItinerary->assignment_mode             = $params['assignment_mode'];
        $fileNoteItinerary->dates                       = $params['dates'];
        $fileNoteItinerary->description                 = $params['description'];
        $fileNoteItinerary->file_classification_code    = $params['classification_code'];
        $fileNoteItinerary->file_classification_name    = $params['classification_name'];
        $fileNoteItinerary->file_itinerary_id           = $itinerary_id;
        $fileNoteItinerary->file_id                     = $fileNoteId;
        $fileNoteItinerary->created_by                  = $params['created_by'];
        $fileNoteItinerary->created_by_code             = $params['created_by_code'];
        $fileNoteItinerary->created_by_name             = $params['created_by_name'];
        $fileNoteItinerary->status                      = (($fileNoteItinerary->status == FileNoteStatusEnum::ACTIVE && $params['type_note'] === "INFORMATIVE") ? FileNoteStatusEnum::ACTIVE : FileNoteStatusEnum::PENDING);
        $fileNoteItinerary->updated_at                  = now();

        if($fileNoteItinerary->file_mascara_id !== null){
            $fileNoteItinerary->file_mascara_id = null;
        }

        $fileNoteItinerary->update();

        if($params['type_note'] === "REQUIREMENT"){
            FileNoteStatusHistoryEloquentModel::create([
                'status'            => FileNoteStatusHistoryEnum::PENDING,
                'date'              => now(),
                'comment'           => 'NOTE UPDATE',
                'file_note_id'      => $fileNoteItinerary->id,
                'user_id'           => $params['created_by'],
                'user_by_code'      => $params['created_by_code'],
                'user_by_name'      => $params['created_by_name']
            ]);
        }

        return [
            "id" => $fileNoteItinerary->id,
            "type_note" => $fileNoteItinerary->type_note,
            "record_type" => $fileNoteItinerary->record_type
        ];
    }

    public function deleteFileNoteItinerary(int $fileId, int $note_id, array $params): bool
    {
        $fileNoteDelete = FileNoteEloquentModel::findOrFail($note_id);
        $fileNoteDelete->created_by         = $params['created_by'];
        $fileNoteDelete->created_by_code    = $params['created_by_code'];
        $fileNoteDelete->created_by_name    = $params['created_by_name'];
        $fileNoteDelete->update();

        $fileNoteDelete->delete();

        return true;
    }

    public function deleteFileNoteItineraryService(int $itinerary_id, array $params): bool
    {
        FileNoteEloquentModel::where('file_itinerary_id', $itinerary_id)->update([
            'created_by'        => $params['created_by'],
            'created_by_code'   => $params['created_by_code'],
            'created_by_name'   => $params['created_by_name'],
            'deleted_at'        => now()
        ]);

        return true;
    }
}
