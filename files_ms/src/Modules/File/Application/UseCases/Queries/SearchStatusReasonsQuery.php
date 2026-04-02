<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\StatusReasonRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchStatusReasonsQuery implements QueryInterface
{
    private StatusReasonRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileAmountReasonRepository = app()->make(StatusReasonRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->searchAll($this->filters);
    }
}
