<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Carbon\Carbon;
use Src\Modules\File\Domain\Enums\FileNoteStatusEnum;
use Src\Modules\File\Domain\Enums\FileNoteStatusHistoryEnum;
use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteMaskEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileNoteStatusHistoryEloquentModel;

class FileNoteRepository implements FileNoteRepositoryInterface{

    public function getAllActive(int $fileId): array
    {
        $maskNotes = FileNoteMaskEloquentModel::where('file_id', $fileId)
            ->whereIn('status', [
                FileNoteStatusEnum::ACTIVE->value,
                FileNoteStatusEnum::APPROVED->value
            ])
            ->get()
            ->keyBy('id');

        // return $maskNotes->toArray();

        $listNotesForService = FileNoteEloquentModel::with(['itinerary:id,entity,name,date_in,city_in_name,city_in_iso'])
            ->where('file_id', $fileId)
            ->whereIn('status', [
                FileNoteStatusEnum::ACTIVE->value,
                FileNoteStatusEnum::APPROVED->value
            ])
            ->orderBy('created_at', 'ASC')
            ->get();

        // return $listNotesForService->toArray();

        // Procesamiento de grupos
        $groupedNotes = [];
        $individualNotes = [];

        // return $listNotesForService->toArray();
        foreach ($listNotesForService as $note) {
            $itinerary = $note->itinerary;
            $city = optional($itinerary)->city_in_name ?? optional($itinerary)->city_in_iso ?? 'Sin Ciudad';
            $date = $itinerary && $itinerary->date_in ? $this->formatDate($itinerary->date_in) : $note->created_at->format('Y-m-d');
            if($note->itinerary && in_array($note->itinerary->entity, ['service', 'hotel'])){
                if ($note->file_mascara_id != null && isset($maskNotes[$note->file_mascara_id])) {
                    $maskNote = $maskNotes[$note->file_mascara_id];
                    $groupKey = "{$city}|{$maskNote->id}";

                    if (!isset($groupedNotes[$groupKey])) {
                        $groupedNotes[$groupKey] = [
                            'id'                    => $maskNote->id,
                            'entity'                => 'mask',
                            'type'                  => 'group',
                            'type_note'             => $maskNote->type_note,
                            'record_type'           => $maskNote->record_type,
                            'assignment_mode'       => $maskNote->assignment_mode,
                            'description'           => $maskNote->description,
                            'dates'                 => $maskNote->dates === null ? $maskNote->dates : json_decode($maskNote->dates, true),
                            'classification_code'   => $maskNote->file_classification_code,
                            'classification_name'   => $maskNote->file_classification_name,
                            'file_mascara_id'       => $maskNote->id,
                            'city' => $city,
                            'date' => $date,
                            'services' => []
                        ];
                    }

                    $groupedNotes[$groupKey]['services'][] = $this->formatNoteItem($note);
                } else {
                    // $individualKey = "{$city}|{$date}";
                    $individualNotes[] = [
                        'id'                    => $note->id,
                        'entity'                => 'note',
                        'type'                  => 'individual',
                        'type_note'             => $note->type_note,
                        'assignment_mode'       => $note->assignment_mode,
                        'record_type'           => $note->record_type,
                        'file_mascara_id'       => $note->file_mascara_id,
                        'description'           => $note->description,
                        'dates'                 => $note->dates === null ? $note->dates : json_decode($note->dates, true),
                        'classification_name'   => $note->file_classification_name,
                        'classification_code'   => $note->file_classification_code,
                        'city'                  => $city,
                        'date'                  => $date,
                        'services'              => [
                            $this->formatNoteItem($note)
                        ]
                    ];
                }
            }
            // if($note->itinerary->entity === 'service' || $note->itinerary->entity === 'hotel'){
            // }
        }

        // return $individualNotes;
        // Combinar resultados
        $additional_for_service = [];

        foreach ($groupedNotes as $group) {
            $city = $group['city'];
            // $date = $group['date'];
            foreach($group['dates'] as $date){
                if (!isset($additional_for_service[$city])) {
                    $additional_for_service[$city] = [];
                }

                $additional_for_service[$city][$date][] = $group;
            }

        }

        // return [
        //     'si_es' =>  $individualNotes
        // ];

        foreach ($individualNotes as $note) {
            $city = $note['city'];
            $date = $note['date'];

            // return $note['city'];
            // break;

            if (!isset($additional_for_service[$city])) {
                $additional_for_service[$city] = [];
            }

            if (!isset($additional_for_service[$city][$date])) {
                $additional_for_service[$city][$date] = [];
            }

            $additional_for_service[$city][$date][] = $note;
        }

        // PARA EL ORDENAMIENTO
        $additional_for_service = $this->sortArrayByDates($additional_for_service);

        return [
            'additional_information' => FileNoteMaskEloquentModel::where('file_id', $fileId)
                ->where('status', '!=', '0')
                ->where('type_note', 'INFORMATIVE')
                ->where('record_type', 'FOR_FILE')
                ->orderBy('created_at', 'ASC')
                ->get()
                ->map(function($note) {
                    return [
                        'id' => $note->id,
                        'entity' => "mask",
                        'type_note' => $note->type_note,
                        'record_type'=> $note->record_type,
                        'assignment_mode' => $note->assignment_mode,
                        'description' => $note->description,
                        'classification_name' => $note->file_classification_name,
                        'classification_code' => $note->file_classification_code,
                        'dates' => $note->dates === null ? $note->dates : json_decode($note->dates, true),
                        'status'    => $note->status,
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                        'created_by' => $note->created_by,
                        'created_by_name' => $note->created_by_name,
                    ];
                }),
            'for_service' => $additional_for_service
        ];
    }

    private function formatNoteItem($note): array
    {
        return [
            'id' => $note->id,
            'description' => $note->description,
            'type_note' => $note->type_note,
            'assignment_mode'=>$note->assignment_mode,
            'classification_name' => $note->file_classification_name,
            'classification_code' => $note->file_classification_code,
            'file_mascara_id' => $note->file_mascara_id,
            'created_at' => $note->created_at->format('Y-m-d H:i:s'),
            'service_id' => $note->itinerary->id ?? '',
            'service_name' => $note->itinerary->name ?? '',
            'service_date_in' => $note->itinerary->date_in ? $this->formatDate($note->itinerary->date_in) : null,
            'service_city_in_name' => $note->itinerary->city_in_name ?? '',
            'service_city_in_iso' => $note->itinerary->city_in_iso ?? '',
        ];
    }

    private function formatNoteItemRequirement($note, $entity = 'note'): array
    {
        if ($note->file_mascara_id) {
            $baseData = [
                'service_id' => $note->itinerary->id ?? '',
                'service_name' => $note->itinerary->name ?? '',
                'service_date_in' => $note->itinerary->date_in ? $this->formatDate($note->itinerary->date_in) : null,
                'service_city_in_name' => $note->itinerary->city_in_name ?? '',
                'service_city_in_iso' => $note->itinerary->city_in_iso ?? '',
            ];
        }else{
            $baseData = [
                'id' => $note->id,
                'entity' => $entity,
                'dates' => json_decode($note->dates,true),
                'description' => $note->description,
                'type_note' => $note->type_note,
                'record_type' => $note->record_type,
                'assignment_mode' => $note->assignment_mode,
                'classification_name' => $note->file_classification_name,
                'classification_code' => $note->file_classification_code,
                'date' => $this->formatDateTime($note->created_at),
                'date_human' => $this->formatHumanDate($note->created_at),
                'status_history' => $this->formatStatusHistoryCollection($note->statusHistory ?? []),
                'services' => [],
                'status' => $note->status->label(),
                'cities' => [],
                'created_by' => $note->created_by,
                'created_by_name' => $note->created_by_name,
            ];

            if($entity === 'note'){
                $baseData['cities'][] = [
                    'iso' => $note->itinerary->city_in_iso ?? '',
                    'name'=> $note->itinerary->city_in_name ?? ''
                ];
                $baseData['services'][] = [
                    'service_id' => $note->itinerary->id ?? '',
                    'service_name' => $note->itinerary->name ?? '',
                    'service_date_in' => $note->itinerary->date_in ? $this->formatDate($note->itinerary->date_in) : null,
                    'service_city_in_name' => $note->itinerary->city_in_name ?? '',
                    'service_city_in_iso' => $note->itinerary->city_in_iso ?? '',
                ];
            }
        }

        return $baseData;
    }

    private function formatStatusHistoryCollection($statusHistories): array
    {
        return $statusHistories->map(function ($status) {
            return $this->formatSingleStatusHistory($status);
        })->toArray();
    }

    private function formatSingleStatusHistory($statusHistory): array
    {
        if (!$statusHistory) {
            return [];
        }

        return [
            'id' => $statusHistory->id,
            'status' => $statusHistory->status,
            'date' => $this->formatDateTime($statusHistory->date),
            'date_human' => $this->formatHumanDate($statusHistory->date),
            'comment' => $statusHistory->comment,
            'user_id' => $statusHistory->user_id,
            'user_by_name' => $statusHistory->user_by_name,
            'created_at' => $this->formatDateTime($statusHistory->created_at),
            'updated_at' => $this->formatDateTime($statusHistory->updated_at),
            'created_by' => $statusHistory->user_id,
            'created_by_name' => $statusHistory->user_by_name,
        ];
    }

    private function formatDateTime($date): string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : '';
    }

    private function formatHumanDate($date): string
    {
        return $date ? Carbon::parse($date)->format('j M') : '';
    }

    private function formatDate($date): string
    {
        try {
            return $date instanceof \DateTimeInterface
                ? $date->format('Y-m-d')
                : Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }

    private function sortArrayByDates(array $data): array
    {
        // 1. Ordenar cada sub-array internamente por fecha ascendente
        foreach ($data as &$dateEntries) {
            uksort($dateEntries, function($a, $b) {
                return strtotime($a) - strtotime($b);
            });
        }
        unset($dateEntries); // Romper la referencia

        // 2. Ordenar el array principal por la fecha más antigua de cada sub-array
        uasort($data, function($a, $b) {
            $minDateA = min(array_keys($a));
            $minDateB = min(array_keys($b));
            return strtotime($minDateA) - strtotime($minDateB);
        });

        return $data;
    }

    public function getAllRequirement(int $fileId): array
    {
        $maskNotes = FileNoteMaskEloquentModel::with(['statusHistory' => function($query) {
                $query->orderBy('created_at', 'DESC');
            }])
            ->where('file_id', $fileId)
            ->where('status', '!=', FileNoteStatusEnum::ACTIVE->value)
            ->orderBy('created_at','ASC')
            ->get()
            ->map(function ($note) {
                return $this->formatNoteItemRequirement($note,'mask');
            });
        // Early return if no mask notes found
        if (!empty($maskNotes)) {
            $maskNoteIds = $maskNotes->pluck('id');

            $servicesByMaskNoteId = FileNoteEloquentModel::with(['itinerary'])
                ->whereIn('file_mascara_id', $maskNoteIds)
                ->orderBy('created_at','ASC')
                ->get()
                ->groupBy('file_mascara_id')
                ->map(function ($notes) {
                    return $notes->map(function ($note) {
                        return $this->formatNoteItemRequirement($note,'note');
                    });
                });

            // return $servicesByMaskNoteId->toArray();
            // Map services to their corresponding mask notes
            $maskNotes =  $maskNotes->map(function ($note) use ($servicesByMaskNoteId) {
                $services = $servicesByMaskNoteId->get($note['id'], [])->toArray();
                foreach($services as $service){
                    $note['cities'][] = [
                        'iso' => $service['service_city_in_iso'],
                        'name' => $service['service_city_in_name'],
                    ];
                }

                $serialized = array_map('serialize', $note['cities']);
                $uniqueSerialized = array_unique($serialized);
                $uniqueArray = array_map('unserialize', $uniqueSerialized);

                $note['cities'] = array_values($uniqueArray);
                $note['services'] = $services;
                return $note;
            })->toArray();
        }


        $listNotesForService = FileNoteEloquentModel::with([
            'itinerary',
            'statusHistory' => function($query) {
                $query->orderBy('created_at', 'DESC');  // Solo ordenamos statusHistory
            }])
            ->where('file_id', $fileId)
            ->where('status', '!=', FileNoteStatusEnum::ACTIVE->value)
            ->where('file_mascara_id',NULL)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->map(function ($note) {
                return $this->formatNoteItemRequirement($note,'note');
            });

        if (!empty($maskNotes)){
            $requirement = array_merge($maskNotes, $listNotesForService->toArray());
        }else{
            $requirement = $listNotesForService->toArray();
        }

        usort($requirement, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $requirement;
    }

    public function index(int $fileId): array
    {
        $listNotes = FileNoteMaskEloquentModel::where('file_id',$fileId)
        ->where('status','!=','0')
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
                'file_id' => $note->file_id,
                'status' => $note->status,
                'created_by' => $note->created_by,
                'created_by_name' => $note->created_by_name,
                'created_date' => $note->updated_at != null ? $note->updated_at->format('d/m/Y') : $note->created_at->format('d/m/Y'),
                'created_time' => $note->updated_at != null ? $note->updated_at->format('G:i:s') : $note->created_at->format('G:i:s'),
            ];
        });
        return $listNotes->toArray();
    }

    public function store(int $fileNoteId, array $params): array
    {
        $fileNoteMask = FileNoteMaskEloquentModel::create([
            'type_note'                 => $params['type_note'],
            'record_type'               => $params['record_type'],
            'assignment_mode'           => $params['assignment_mode'],
            'dates'                     => $params['dates'],
            'description'               => $params['description'],
            'file_classification_code'  => $params['classification_code'],
            'file_classification_name'  => $params['classification_name'],
            'created_by'                => $params['created_by'],
            'created_by_code'           => $params['created_by_code'],
            'created_by_name'           => $params['created_by_name'],
            'status'                    => ($params['type_note'] === "INFORMATIVE" ? 1 : 2),
            'file_id'                   => $fileNoteId,
            'created_at'                => now(),
        ]);

        if($params['service_ids'] != null){
            $service_ids = json_decode($params['service_ids'], true);

            if(!empty($service_ids) && in_array($params['assignment_mode'],["FOR_SERVICE","ALL_DAY"])){
                foreach($service_ids as $id){
                    FileNoteEloquentModel::create([
                        'type_note'                 => $params['type_note'],
                        'record_type'               => $params['record_type'],
                        'assignment_mode'           => $params['assignment_mode'],
                        'dates'                     => $params['dates'],
                        'description'               => $params['description'],
                        'file_classification_code'  => $params['classification_code'],
                        'file_classification_name'  => $params['classification_name'],
                        'created_by'                => $params['created_by'],
                        'created_by_code'           => $params['created_by_code'],
                        'created_by_name'           => $params['created_by_name'],
                        'status'                    => ($params['type_note'] === "INFORMATIVE" ? FileNoteStatusEnum::ACTIVE : FileNoteStatusEnum::PENDING),
                        'file_itinerary_id'         => $id,
                        'file_mascara_id'           => $fileNoteMask->id,
                        'file_id'                   => $fileNoteId,
                        'created_at'                => now(),
                    ]);
                }
            }
        }

        if($params['type_note'] === "REQUIREMENT"){
            FileNoteStatusHistoryEloquentModel::create([
                'status'                => FileNoteStatusHistoryEnum::PENDING,
                'date'                  => now(),
                'comment'               => 'NOTE CREATION',
                'file_note_mascara_id'  => $fileNoteMask->id,
                'user_id'               => $params['created_by'],
                'user_by_code'           => $params['created_by_code'],
                'user_by_name'          => $params['created_by_name'],
                'created_at'            => now(),
            ]);
        }

        return [
            "id" => $fileNoteMask->id,
            "type_note" => $fileNoteMask->type_note,
            "record_type" => $fileNoteMask->record_type
        ];
    }

    public function update(int $fileNoteId, int $note_id, array $params): array
    {
        $fileNoteItinerary = FileNoteMaskEloquentModel::findOrFail($note_id);

        $fileNoteItinerary->type_note                   = $params['type_note'];
        $fileNoteItinerary->record_type                 = $params['record_type'];
        $fileNoteItinerary->assignment_mode             = $params['assignment_mode'];
        $fileNoteItinerary->dates                       = $params['dates'];
        $fileNoteItinerary->description                 = $params['description'];
        $fileNoteItinerary->file_classification_code    = $params['classification_code'];
        $fileNoteItinerary->file_classification_name    = $params['classification_name'];
        $fileNoteItinerary->file_id                     = $fileNoteId;
        $fileNoteItinerary->created_by                  = $params['created_by'];
        $fileNoteItinerary->created_by_code             = $params['created_by_code'];
        $fileNoteItinerary->created_by_name             = $params['created_by_name'];
        $fileNoteItinerary->status                      = (($fileNoteItinerary->status === FileNoteStatusEnum::ACTIVE && $params['type_note'] == 'INFORMATIVE') ? FileNoteStatusEnum::ACTIVE : FileNoteStatusEnum::PENDING);
        $fileNoteItinerary->updated_at                  = now();

        $fileNoteItinerary->update();

        if($params['service_ids'] != null){
            $service_ids = json_decode($params['service_ids'], true);
            $service_ids = array_map('intval', $service_ids);
            if(!empty($service_ids) && in_array($params['assignment_mode'],["FOR_SERVICE","ALL_DAY"])){
                // BUSCAMOS TODOS LOS NOTE ITINERARIOS
                $fileNoteItineries = FileNoteEloquentModel::where('file_mascara_id',$note_id)->get();
                $itinerary_ids = $fileNoteItineries->pluck('file_itinerary_id')->toArray();

                $update_ids = array_values(array_intersect($service_ids, $itinerary_ids));
                if(!empty($update_ids)){
                    FileNoteEloquentModel::where('file_mascara_id', $note_id)->whereIn('file_itinerary_id',$update_ids)->update([
                        'type_note'                 => $params['type_note'],
                        'record_type'               => $params['record_type'],
                        'assignment_mode'           => $params['assignment_mode'],
                        'dates'                     => $params['dates'],
                        'description'               => $params['description'],
                        'file_classification_code'  => $params['classification_code'],
                        'file_classification_name'  => $params['classification_name'],
                        'file_id'                   => $fileNoteId,
                        'file_mascara_id'           => $note_id,
                        'status'                    => ($params['type_note'] === "INFORMATIVE" ? FileNoteStatusEnum::ACTIVE : FileNoteStatusEnum::PENDING),
                        'created_by'                => $params['created_by'],
                        'created_by_code'           => $params['created_by_code'],
                        'created_by_name'           => $params['created_by_name'],
                        'updated_at'                => now()
                    ]);
                }

                $delete_ids = array_values(array_diff($itinerary_ids, $service_ids));
                if(!empty($update_ids)){
                    FileNoteEloquentModel::where('file_mascara_id', $note_id)->whereIn('file_itinerary_id',$delete_ids)->delete();
                }

                $create_ids = array_values(array_diff($service_ids, $itinerary_ids));
                if(!empty($create_ids)){
                    $records = [];
                    $now = now();

                    foreach($create_ids as $id){
                        $records[] = [
                            'type_note'                 => $params['type_note'],
                            'record_type'               => $params['record_type'],
                            'assignment_mode'           => $params['assignment_mode'],
                            'dates'                     => $params['dates'],
                            'description'               => $params['description'],
                            'file_classification_code'  => $params['classification_code'],
                            'file_classification_name'  => $params['classification_name'],
                            'created_by'                => $params['created_by'],
                            'created_by_code'           => $params['created_by_code'],
                            'created_by_name'           => $params['created_by_name'],
                            'status'                    => ($params['type_note'] === "INFORMATIVE" ? FileNoteStatusEnum::ACTIVE : FileNoteStatusEnum::PENDING),
                            'file_itinerary_id'         => $id,
                            'file_mascara_id'           => $note_id,
                            'file_id'                   => $fileNoteId,
                            'created_at'                => $now
                        ];
                    }

                    FileNoteEloquentModel::insert($records);
                }
            }
        }

        if($params['type_note'] === "REQUIREMENT"){
            FileNoteStatusHistoryEloquentModel::create([
                'status'                => FileNoteStatusHistoryEnum::PENDING,
                'date'                  => now(),
                'comment'               => 'NOTE UPDATE',
                'file_note_mascara_id'  => $fileNoteItinerary->id,
                'user_id'               => $params['created_by'],
                'user_by_code'          => $params['created_by_code'],
                'user_by_name'          => $params['created_by_name']
            ]);
        }

        return [
            "id" => $fileNoteItinerary->id,
            "type_note" => $fileNoteItinerary->type_note,
            "record_type" => $fileNoteItinerary->record_type
        ];
    }

    public function delete(int $fileId, int $note_id, array $params): bool
    {
        $type_note = $params['type_note'] ?? 'mask';
        if($type_note === 'mask'){
            $fileNoteMask = FileNoteMaskEloquentModel::findOrFail($note_id);
            $fileNoteMask->created_by = $params['created_by'];
            $fileNoteMask->created_by_code = $params['created_by_code'];
            $fileNoteMask->created_by_name = $params['created_by_name'];
            $fileNoteMask->update();

            $fileNoteMask->delete();

            FileNoteEloquentModel::where('file_mascara_id', $note_id)->update([
                'created_by'        => $params['created_by'],
                'created_by_code'   => $params['created_by_code'],
                'created_by_name'   => $params['created_by_name'],
                'deleted_at'        => now(),
            ]);
        }else{
            $fileNoteMask = FileNoteEloquentModel::findOrFail($note_id);
            $fileNoteMask->created_by = $params['created_by'];
            $fileNoteMask->created_by_code = $params['created_by_code'];
            $fileNoteMask->created_by_name = $params['created_by_name'];
            $fileNoteMask->update();

            $fileNoteMask->delete();
        }

        return true;
    }

    public function updateStatus(int $id, array $params): bool{
        $status = FileNoteStatusEnum::fromLabel($params['status']);
        if($params['entity'] === "mask"){
            $fileNoteMaskEloquent = FileNoteMaskEloquentModel::findOrFail($id);
            $fileNoteMaskEloquent->status = $status;
            $fileNoteMaskEloquent->save();

            // ACTUALIZACION DE A SUS ITEM
            FileNoteEloquentModel::where('file_mascara_id', $fileNoteMaskEloquent->id)
            ->update([
                'status' => $status,
                'updated_at' => now() // Actualiza automáticamente la marca de tiempo
            ]);

            FileNoteStatusHistoryEloquentModel::create([
                'status'                => $params['status'],
                'date'                  => now(),
                'comment'               => 'CHANGE OF STATE',
                'file_note_mascara_id'  => $fileNoteMaskEloquent->id,
                'user_id'               => $params['created_by'],
                'user_by_code'          => $params['created_by_code'],
                'user_by_name'          => $params['created_by_name']
            ]);

        }else{
            $fileNoteEloquent = FileNoteEloquentModel::findOrFail($id);
            $fileNoteEloquent->status = $status;
            $fileNoteEloquent->save();

            FileNoteStatusHistoryEloquentModel::create([
                'status'                => $params['status'],
                'date'                  => now(),
                'comment'               => $params['comment'],
                'file_note_id'          => $fileNoteEloquent->id,
                'user_id'               => $params['created_by'],
                'user_by_code'          => $params['created_by_code'],
                'user_by_name'          => $params['created_by_name']
            ]);
        }

        return true;
    }
}
