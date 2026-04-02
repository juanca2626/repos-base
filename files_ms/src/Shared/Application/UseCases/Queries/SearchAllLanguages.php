<?php

namespace Src\Shared\Application\UseCases\Queries;

use Src\Shared\Domain\Repositories\LanguageRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchAllLanguages implements QueryInterface
{
    private LanguageRepositoryInterface $languageRepository;

    public function __construct()
    {
        $this->languageRepository = app()->make(LanguageRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->languageRepository->search();
    }
}
