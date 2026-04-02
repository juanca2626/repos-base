<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\MasterServiceRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchMasterServiceQuery implements QueryInterface
{
    private MasterServiceRepositoryInterface $masterServiceRepository;

    public function __construct(private readonly array $filters)
    {
        $this->masterServiceRepository = app()->make(MasterServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator
    {
        return $this->masterServiceRepository->searchAll($this->filters);
    }
}
