<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Repositories\FileCategoryRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCategoryEloquentModel;

class FileCategoryRepository implements FileCategoryRepositoryInterface
{         
    protected string $dateIn;
    protected string $dateOut;

    public function create(int $file_id,array $params): bool
    {
        return DB::transaction(function () use ($file_id, $params) { 
        
            FileCategoryEloquentModel::where('file_id', $file_id)->delete();
            foreach($params as $category_id){
                FileCategoryEloquentModel::create([
                    'file_id' => $file_id,
                    'category_id' => $category_id,
                ]);
            }

            return true;
        });
    }

     

}
