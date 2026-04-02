<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\CurrencyRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllCurrency implements QueryInterface
{
    private CurrencyRepositoryInterface $currencyRepository;

    public function __construct()
    {
        $this->currencyRepository = app()->make(CurrencyRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->currencyRepository->search();
    }
}
