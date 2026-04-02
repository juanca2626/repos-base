<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Domain\Repositories\FileReasonStatementRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileReasonStatementEloquentModel;

class FileReasonStatementRepository implements FileReasonStatementRepositoryInterface
{
    public function search(array $filters): LengthAwarePaginator
    {
        $FileReasonStatementEloquentModel = FileReasonStatementEloquentModel::query();
        $perPage = $filters['per_page']; $page = $filters['page']; $count = $FileReasonStatementEloquentModel->count();

        $file_amount_reasons = [];
        foreach ($FileReasonStatementEloquentModel->paginate($perPage, ['*'], 'page', $page) as $fileAmountReason)
        {
            $file_amount_reason = FileAmountReasonMapper::fromEloquent($fileAmountReason);
            $file_amount_reasons[] = $file_amount_reason;
        }

        return new LengthAwarePaginator(
            $file_amount_reasons,
            $count,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function searchAll(array $filters): array
    {
        $fileReasonStatementEloquentModel = FileReasonStatementEloquentModel::query();
        $file_reasons = $fileReasonStatementEloquentModel->get()->toArray();

        return $file_reasons;
    }
}
