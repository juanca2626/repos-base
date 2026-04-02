<?php

namespace Src\Modules\FileV2\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Src\Modules\FileV2\Domain\Models\File;
use Src\Modules\FileV2\Domain\Models\FileCategory;
use Src\Modules\FileV2\Domain\DTOs\FileSearchParams;
use Src\Modules\FileV2\Application\Transformers\FileListTransformer;
use Illuminate\Pagination\LengthAwarePaginator;

class FileRepository
{
    public function create(array $data): File
    {    
        return File::create($data);
    }

    public function createCategory(int $fileId, array $data): FileCategory
    {
        return FileCategory::create([
            'file_id' => $fileId,
            'category_id' => $data['category_id']
        ]);
    }

    public function find(int $id)
    {
        return File::find($id);
    }

    public function search(FileSearchParams $filters): LengthAwarePaginator
    {
        $query = File::query()
            ->select(
                'files.id',
                'files.file_number',
                'files.client_code',
                'files.status',
                'files.date_in',
                'files.date_out',

                DB::raw("(select 1 from file_vips where file_id=files.id and deleted_at is null limit 1) as vip"),
                DB::raw("(select 1 from file_itineraries where file_id=files.id and deleted_at is null limit 1) as itinerary"),
                DB::raw("IF(files.revision_stages is null, 1, files.revision_stages) as revision_stages")
            );

        // FILTER
        if ($filters->filter) {
            $filter = $filters->filter;

            $query->where(function ($q) use ($filter) {
                $q->where('files.file_number', 'like', "%$filter%");

                if (strlen($filter) !== 6) {
                    $q->orWhere('files.order_number', 'like', "%$filter%")
                      ->orWhere('files.reservation_number', 'like', "%$filter%")
                      ->orWhere('files.description', 'like', "%$filter%")
                      ->orWhereExists(function ($sub) use ($filter) {
                          $sub->select(DB::raw(1))
                              ->from('file_passengers')
                              ->whereColumn('file_id', 'files.id')
                              ->whereRaw("CONCAT(name,' ',surnames,' ',document_number) LIKE ?", ["%$filter%"]);
                      });
                }
            });
        }

        // EXECUTIVE
        if ($filters->executiveCode) {
            $query->where('files.executive_code', $filters->executiveCode);
        }

        // CLIENT
        if ($filters->clientId) {
            $query->where('files.client_id', $filters->clientId);
        }

        // DATE RANGE
        if ($filters->dateRange) {
            $query->whereBetween('files.date_in', $filters->dateRange);
        }

        // COMPLETE
        if (!is_null($filters->complete)) {
            if ($filters->complete) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('file_itineraries')
                      ->whereColumn('file_id', 'files.id')
                      ->whereNull('deleted_at');
                });
            } else {
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('file_itineraries')
                      ->whereColumn('file_id', 'files.id')
                      ->whereNull('deleted_at');
                });
            }
        }

        // NEXT DAYS / REVISION
        if ($filters->filterNextDays || $filters->revisionStages) {

            $days = $filters->revisionStages ? 45 : $filters->filterNextDays;
            $start = now();

            $end = match ((int)$days) {
                7 => now()->addDays(7),
                15 => now()->addDays(15),
                30 => now()->addMonth(),
                default => now()->addDays(45),
            };

            $query->whereBetween('files.date_in', [$start, $end]);
        }

        // REVISION STAGES
        if ($filters->revisionStages) {
            if ($filters->revisionStages == 2) {
                $query->where('files.revision_stages', 2);
            } else {
                $query->where(function ($q) {
                    $q->where('files.revision_stages', 1)
                      ->orWhereNull('files.revision_stages');
                });
            }
        }

        // ORDER
        match ($filters->filterBy) {
            'vips' => $query->orderBy('vip', $filters->filterByType),
            'status' => $query->orderBy('files.status', $filters->filterByType),
            'revision_stages' => $query->orderBy('revision_stages', $filters->filterByType),
            default => $query->orderBy('itinerary', 'asc'),
        };

        $paginator = $query->paginate(
            $filters->perPage,
            ['*'],
            'page',
            $filters->page
        );

        // TRANSFORMACIÓN FINAL
        $paginator->getCollection()->transform(function ($file) {
            return FileListTransformer::transform($file);
        });

        return $paginator;
    }
    
}