<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\OpeRepositoryInterface;
use Src\Shared\Domain\QueryInterface; 

class SearchHistoryPassToOpe implements QueryInterface
{
    private OpeRepositoryInterface $opeRepository;

    public function __construct(private readonly array $params)
    {
        $this->opeRepository = app()->make(OpeRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator
    {
        return $this->opeRepository->searchHistoryPassToOpe($this->params);
    }
}
