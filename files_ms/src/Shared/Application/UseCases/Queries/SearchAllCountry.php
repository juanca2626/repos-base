<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\CountryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllCountry implements QueryInterface
{
    private CountryRepositoryInterface $countryRepository;

    public function __construct()
    {
        $this->countryRepository = app()->make(CountryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->countryRepository->search();
    }
}
