<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileStatementForStellaQuery implements QueryInterface
{
    private FileStatementRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly int $file_id)
    {
        $this->fileAmountReasonRepository = app()->make(FileStatementRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->document_details_for_stella($this->file_id);
    }

}
