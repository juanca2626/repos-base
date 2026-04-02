<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Domain\Repositories\FileNoteOpeRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteMaskEloquentModel;

class FileNoteOpeRepository implements FileNoteOpeRepositoryInterface{
    public function listForFile(int $fileNumber): array {
        $number_file = FileEloquentModel::where('file_number', $fileNumber)->first();

        $fileNoteForFile = FileNoteMaskEloquentModel::withTrashed()
            ->where('file_id', $number_file->id)
            ->where('type_note', 'INFORMATIVE')
            ->where('record_type', 'FOR_FILE')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->map(function($note){
                    return [
                    'id' => $note->id,
                    'entity' => $note->file_mascara_id ? 'mask' : 'note',
                    'type_note' => $note->type_note,
                    // 'record_type' => $note->record_type,
                    // 'assignment_mode' => $note->assignment_mode,
                    // 'dates' => json_decode($note->dates,true),
                    // 'date' => $note->created_at ?? null,
                    'description' => $note->description,
                    'classification_code' => $note->file_classification_code,
                    'classification_name' => $note->file_classification_name,
                    'user_by' => $note->created_by,
                    'user_by_code' => $note->created_by_code,
                    'user_by_name' => $note->created_by_name,
                    'status' => $note->deleted_at
                        ? 'removed'
                        : $note->status->label(),
                    'created_at' => $note->created_at->format('Y-m-d G:i:s'),
                    'updated_at' => optional($note->updated_at)->format('Y-m-d G:i:s'),
                    'deleted_at' => optional($note->deleted_at)->format('Y-m-d G:i:s'),
                ];
            })
            ->toArray();
        return $fileNoteForFile;
    }

    public function listForService(int $fileNumber): array {
        $number_file = FileEloquentModel::where('file_number', $fileNumber)->first();

        $fileNoteForService = FileNoteEloquentModel::withTrashed()
            ->with(['itinerary:id,name,entity,category,object_code,country_in_iso,country_out_iso,status,date_in,date_out'])
            ->where('file_id', $number_file->id)
            ->orderBy('created_at','ASC')
            ->get()
            ->groupBy('file_itinerary_id')
            ->map(function ($notes, $itineraryId) {
                return [
                    'service_id' => $itineraryId,
                    'service_name' => $notes->first()->itinerary->name ?? null,
                    'service_entity' => $notes->first()->itinerary->entity ?? null,
                    'service_code' => $notes->first()->itinerary->object_code ?? null,
                    'service_category' => $notes->first()->itinerary->category ?? null,
                    'notes' => $notes->map(fn ($note) =>
                        [
                            'id' => $note->id,
                            'entity' => $note->file_mascara_id ? 'mask' : 'note',
                            'type_note' => $note->type_note,
                            // 'record_type' => $note->record_type,
                            // 'assignment_mode' => $note->assignment_mode,
                            // 'dates' => json_decode($note->dates,true),
                            'date' => $note->itinerary->date_in ?? null,
                            'description' => $note->description,
                            'classification_code' => $note->file_classification_code,
                            'classification_name' => $note->file_classification_name,
                            'user_by' => $note->created_by,
                            'user_by_code' => $note->created_by_code,
                            'user_by_name' => $note->created_by_name,
                            'status' => $note->trashed() ? 'removed' : $note->status->label(),
                            'created_at' => $note->created_at->format('Y-m-d G:i:s'),
                            'updated_at' => optional($note->updated_at)->format('Y-m-d G:i:s'),
                            'deleted_at' => optional($note->deleted_at)->format('Y-m-d G:i:s'),
                        ]
                    )
                ];
            })
            ->values()
            ->toArray();

        return $fileNoteForService;
    }
}
