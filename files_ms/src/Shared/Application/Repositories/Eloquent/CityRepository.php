<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\CityEloquentModel;
use Src\Shared\Domain\Repositories\CityRepositoryInterface;

class CityRepository implements CityRepositoryInterface
{
    /**
     * @return array
     */
    public function search(): array
    {
        $cities = CityEloquentModel::query()->get();
        return $cities->toArray();
    }
}
