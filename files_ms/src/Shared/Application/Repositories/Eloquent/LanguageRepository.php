<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\LanguageEloquentModel;
use Src\Shared\Domain\Repositories\LanguageRepositoryInterface;

class LanguageRepository implements LanguageRepositoryInterface
{
    /**
     * @return array
     */
    public function search(): array
    {
        $languages = LanguageEloquentModel::query()->get();
        return $languages->toArray();
    }
}
