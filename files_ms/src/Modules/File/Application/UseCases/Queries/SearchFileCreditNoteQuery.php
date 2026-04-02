<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileCreditNoteRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileCreditNoteQuery implements QueryInterface
{
    private FileCreditNoteRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly int $file_id, private readonly array|null $filters)
    {
        $this->fileAmountReasonRepository = app()->make(FileCreditNoteRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->searchAll($this->file_id, $this->filters);
    }
}
