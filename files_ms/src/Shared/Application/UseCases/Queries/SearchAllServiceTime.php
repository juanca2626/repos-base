<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\ServiceTimeRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllServiceTime implements QueryInterface
{
    private ServiceTimeRepositoryInterface $serviceTimeRepository;

    public function __construct()
    {
        $this->serviceTimeRepository = app()->make(ServiceTimeRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->serviceTimeRepository->search();
    }
}
