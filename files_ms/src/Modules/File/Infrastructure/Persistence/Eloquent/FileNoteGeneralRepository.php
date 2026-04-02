<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Src\Modules\File\Domain\Repositories\FileNoteGeneralRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteGeneralEloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FileNoteGeneralRepository implements FileNoteGeneralRepositoryInterface{

    public function index(int $file_id) :array {
        try {
            return  FileNoteGeneralEloquentModel::where('file_id', $file_id)
                                                ->firstOrFail()
                                                ->toArray();
        } catch (ModelNotFoundException $e) {
            return [];
        }
    }

    public function create(int $fileId, array $params): array
    {
        if(!$this->ifExistsNoteGeneral($fileId)){
            $fileNoteGeneralEloquent = new FileNoteGeneralEloquentModel();
            $fileNoteGeneralEloquent->date_event        = $params['date_event'];
            $fileNoteGeneralEloquent->type_event        = $params['type_event'];
            $fileNoteGeneralEloquent->description_event = $params['description_event'];
            $fileNoteGeneralEloquent->image_logo        = $params['image_logo'] == "" ? NULL : $params['image_logo'];
            $fileNoteGeneralEloquent->created_by        = $params['created_by'];
            $fileNoteGeneralEloquent->created_by_code   = $params['created_by_code'];
            $fileNoteGeneralEloquent->created_by_name   = $params['created_by_name'];
            $fileNoteGeneralEloquent->file_id           = $fileId;
            $fileNoteGeneralEloquent->save();

            return [
                'id' => $fileNoteGeneralEloquent->id
            ];
        }

        return [];
    }

    public function update(int $generalId, int $fileId, array $params): array
    {
        $fileNoteGeneralEloquent = FileNoteGeneralEloquentModel::findOrFail($generalId);

        $fileNoteGeneralEloquent->date_event        = $this->determineFieldValue($fileNoteGeneralEloquent->date_event,$params['date_event']);
        $fileNoteGeneralEloquent->type_event        = $this->determineFieldValue($fileNoteGeneralEloquent->type_event,$params['type_event']);
        $fileNoteGeneralEloquent->description_event = $this->determineFieldValue($fileNoteGeneralEloquent->description_event,$params['description_event']);
        $fileNoteGeneralEloquent->image_logo        = $this->determineFieldValue($fileNoteGeneralEloquent->image_logo, $params['image_logo']);
        $fileNoteGeneralEloquent->created_by        = $params['created_by'];
        $fileNoteGeneralEloquent->created_by_code   = $params['created_by_code'];
        $fileNoteGeneralEloquent->created_by_name   = $params['created_by_name'];
        $fileNoteGeneralEloquent->file_id           = $fileId;
        $fileNoteGeneralEloquent->updated_at        = now();
        $fileNoteGeneralEloquent->update();

        return [
            'id' => $fileNoteGeneralEloquent->id
        ];
    }

    protected function determineFieldValue(?string $current, ?string $newField): string
    {
        $field = $current === null ? $newField : $current;
        if($current !== null && $newField !== null){
            $field = $newField;
        }
        return $field;
    }

    public function ifExistsNoteGeneral($file_id): bool{
        try {
            $fileNoteGeneralEloquent = FileNoteGeneralEloquentModel::where('file_id', $file_id)
            ->firstOrFail()
            ->toArray();
            return !empty($fileNoteGeneralEloquent);
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }
}
