<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Domain\Repositories\SupplierRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\CategoryEloquentModel;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function searchAll(array $filters): array
    {
        

        return [
            [
                'code' => 'CUSAVT',
                'name' => 'AVALOS TOURS E.I.R.LTDA.',
            ],
            [
                'code' => 'LIMCBS',
                'name' => 'CAF BUSINESS S.A.C',
            ]
        ];
    }
}
