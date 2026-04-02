<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileStatementBlockedQuery implements QueryInterface
{
    private FileStatementRepositoryInterface $fileStatementRepository;

    public function __construct()
    {
        $this->fileStatementRepository = app()->make(FileStatementRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileStatementRepository->blocked();
    }

}
