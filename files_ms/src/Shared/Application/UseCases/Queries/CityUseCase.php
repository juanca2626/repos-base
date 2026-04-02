<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\CityRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class CityUseCase
{
    protected CityRepositoryInterface $cityRepository;

    public function __construct(CityRepositoryInterface $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    /**
     * List paginated cities.
     */
    public function listPaginatedCities(array $params)
    {
        // Delegate the paginated query to the repository
        return $this->cityRepository->paginate($params['per_page'], $params['page']);
    }
}