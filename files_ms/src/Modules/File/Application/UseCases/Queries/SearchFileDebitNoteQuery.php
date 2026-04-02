<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileDebitNoteRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileDebitNoteQuery implements QueryInterface
{
    private FileDebitNoteRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly int $file_id, private readonly array $filters)
    {
        $this->fileAmountReasonRepository = app()->make(FileDebitNoteRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->searchAll($this->file_id, $this->filters);
    }
}
