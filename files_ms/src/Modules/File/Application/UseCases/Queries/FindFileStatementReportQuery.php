<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileStatementReportQuery implements QueryInterface
{
    private FileStatementRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly int|null $file_id)
    {
        $this->fileAmountReasonRepository = app()->make(FileStatementRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->report($this->file_id);
    }

}
