<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\CurrencyEloquentModel;
use Src\Shared\Domain\Repositories\CurrencyRepositoryInterface;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    /**
     * @return array
     */
    public function search(): array
    {
        $currency = CurrencyEloquentModel::query()->get();
        return $currency->toArray();
    }
}
