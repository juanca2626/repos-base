<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Domain\Repositories\FileStatementReasonsModificationRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatementReasonsModificationEloquentModel;

class FileStatementReasonsModificationRepository implements FileStatementReasonsModificationRepositoryInterface
{
    public function search(array $filters): LengthAwarePaginator
    {
        $FileStatementReasonsModificationEloquentModel = FileStatementReasonsModificationEloquentModel::query();
        $perPage = $filters['per_page']; $page = $filters['page']; $count = $FileStatementReasonsModificationEloquentModel->count();

        $file_amount_reasons = [];
        foreach ($FileStatementReasonsModificationEloquentModel->paginate($perPage, ['*'], 'page', $page) as $fileAmountReason)
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
        $fileStatementReasonsModificationEloquentModel = FileStatementReasonsModificationEloquentModel::query();
        $file_reasons = $fileStatementReasonsModificationEloquentModel->get()->toArray();

        return $file_reasons;
    }
}
