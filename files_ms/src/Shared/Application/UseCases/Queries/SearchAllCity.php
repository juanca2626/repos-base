<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\CityRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllCity implements QueryInterface
{
    private CityRepositoryInterface $countryRepository;

    public function __construct()
    {
        $this->cityRepository = app()->make(CityRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->cityRepository->search();
    }
}
