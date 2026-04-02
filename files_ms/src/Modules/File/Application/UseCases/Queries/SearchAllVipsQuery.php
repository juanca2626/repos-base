<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\VipRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllVipsQuery implements QueryInterface
{
    private VipRepositoryInterface $vipRepository;

    public function __construct(private readonly array $filters)
    {
        $this->vipRepository = app()->make(VipRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): int
    {
        return $this->vipRepository->searchAllVipsQuery($this->filters);
    }
}
