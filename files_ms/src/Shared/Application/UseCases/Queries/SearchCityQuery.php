<?php

namespace Src\Shared\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Shared\Domain\Repositories\CityRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchCityQuery implements QueryInterface
{
    private CityRepositoryInterface $cityRepository;

    public function __construct(private readonly array $filters)
    {
        $this->cityRepository = app()->make(CityRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator
    {
        return $this->cityRepository->getAllCities($this->filters);
    }
}