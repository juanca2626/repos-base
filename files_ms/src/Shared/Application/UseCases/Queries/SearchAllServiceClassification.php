<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\ServiceClassificationRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllServiceClassification implements QueryInterface
{
    private ServiceClassificationRepositoryInterface $serviceClassificationRepository;

    public function __construct()
    {
        $this->serviceClassificationRepository = app()->make(ServiceClassificationRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->serviceClassificationRepository->search();
    }
}
