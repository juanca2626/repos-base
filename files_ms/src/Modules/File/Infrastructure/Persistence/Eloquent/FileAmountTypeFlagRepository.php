<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\Mappers\FileAmountTypeFlagMapper;
use Src\Modules\File\Domain\Repositories\FileAmountTypeFlagRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountTypeFlagEloquentModel;

class FileAmountTypeFlagRepository implements FileAmountTypeFlagRepositoryInterface
{
    public function search(array $filters): LengthAwarePaginator
    {
        $fileAmountTypeFlagEloquent = FileAmountTypeFlagEloquentModel::query();
        $perPage = $filters['per_page']; $page = $filters['page']; $count = $fileAmountTypeFlagEloquent->count();

        $file_amount_type_flags = [];
        foreach ($fileAmountTypeFlagEloquent->paginate($perPage, ['*'], 'page', $page) as $fileAmountTypeFlag)
        {
            $file_amount_type_flag = FileAmountTypeFlagMapper::fromEloquent($fileAmountTypeFlag);
            $file_amount_type_flags[] = $file_amount_type_flag;
        }

        return new LengthAwarePaginator(
            $file_amount_type_flags,
            $count,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function all(): array
    {
        $fileAmountTypeFlagEloquent = FileAmountTypeFlagEloquentModel::query();
        $file_amount_type_flags = $fileAmountTypeFlagEloquent->get()->toArray();

        return $file_amount_type_flags;
    }

    public function locked(): array
    {
        $fileAmountTypeFlagEloquent = FileAmountTypeFlagEloquentModel::query()->where('name', 'Bloqueado')->first()->toArray();    
        return $fileAmountTypeFlagEloquent;
    }    
}
