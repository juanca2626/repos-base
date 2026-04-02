<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\TypeServiceRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllTypeService implements QueryInterface
{
    private TypeServiceRepositoryInterface $typeServiceRepository;

    public function __construct()
    {
        $this->typeServiceRepository = app()->make(TypeServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->typeServiceRepository->search();
    }
}
