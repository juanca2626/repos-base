<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\ServiceTimeEloquentModel;
use Src\Shared\Domain\Repositories\ServiceTimeRepositoryInterface;

class ServiceTimeRepository implements ServiceTimeRepositoryInterface
{
    /**
     * @return array
     */
    public function search(): array
    {
        $data = ServiceTimeEloquentModel::query()->get();
        return $data->toArray();
    }
}
