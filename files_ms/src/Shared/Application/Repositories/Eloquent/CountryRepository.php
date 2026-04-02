<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\CountryEloquentModel;
use Src\Shared\Domain\Repositories\CountryRepositoryInterface;

class CountryRepository implements CountryRepositoryInterface
{
    /**
     * @return array
     */
    public function search(): array
    {
        $countries = CountryEloquentModel::query()->get();
        return $countries->toArray();
    }
}
