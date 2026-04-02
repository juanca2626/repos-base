<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\SupplierRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchSupplierQuery implements QueryInterface
{
    private SupplierRepositoryInterface $supplierRepository;

    public function __construct(private readonly array $filters)
    {
        $this->supplierRepository = app()->make(SupplierRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->supplierRepository->searchAll($this->filters);
    }
}
