<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Domain\Repositories\CategoryRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\CategoryEloquentModel;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function searchAll(array $filters): array
    {
        $categoryEloquentModel = CategoryEloquentModel::query(); 
        $file_categories = $categoryEloquentModel->get()->toArray();

        return $file_categories;
    }
}
