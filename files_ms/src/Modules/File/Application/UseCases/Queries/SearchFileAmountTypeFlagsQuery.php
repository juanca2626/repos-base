<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileAmountTypeFlagRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileAmountTypeFlagsQuery implements QueryInterface
{
    private FileAmountTypeFlagRepositoryInterface $FileAmountTypeFlagRepository;

    public function __construct(private readonly array $filters)
    {
        $this->FileAmountTypeFlagRepository = app()->make(FileAmountTypeFlagRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->FileAmountTypeFlagRepository->all();
    }
}
