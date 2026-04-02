<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Domain\Repositories\FileAmountReasonRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountReasonEloquentModel;

class FileAmountReasonRepository implements FileAmountReasonRepositoryInterface
{
    public function search(array $filters): LengthAwarePaginator
    {
        $fileAmountReasonEloquentModel = FileAmountReasonEloquentModel::query();
        $perPage = $filters['per_page']; $page = $filters['page']; $count = $fileAmountReasonEloquentModel->count();

        $file_amount_reasons = [];
        foreach ($fileAmountReasonEloquentModel->paginate($perPage, ['*'], 'page', $page) as $fileAmountReason)
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
        $fileAmountReasonEloquentModel = FileAmountReasonEloquentModel::query();

        if($filters['area'] and $filters['area'] != "all"){
            $fileAmountReasonEloquentModel->where('area', $filters['area']);
        }

        if(isset($filters['visible']) and $filters['visible'] != "all"){
            
            $fileAmountReasonEloquentModel->where('visible', $filters['visible']);
        }        

        if(isset($filters['process'])){
            $fileAmountReasonEloquentModel->where('process', $filters['process']);
            if($filters['process'] == 'exonerar_penalidad')
            {
                $fileAmountReasonEloquentModel->whereNotIn('id', [12,11]);
            }
            
        }  

        $file_amount_reasons = $fileAmountReasonEloquentModel->get()->toArray();

        return $file_amount_reasons;
    }
}
