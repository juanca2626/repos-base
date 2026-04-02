<?php

namespace Src\Modules\File\Application\UseCases\Queries; 
use Src\Modules\File\Domain\Repositories\OpeRepositoryInterface;
use Src\Shared\Domain\QueryInterface; 

class SearchFilesPassToOpe implements QueryInterface
{
    private OpeRepositoryInterface $opeRepository;

    public function __construct(private readonly array $params)
    {
        $this->opeRepository = app()->make(OpeRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): bool
    {
        return $this->opeRepository->searchFilesPassToOpe($this->params);
    }
}
