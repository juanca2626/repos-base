<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileStatementDetailsQuery implements QueryInterface
{
    private FileStatementRepositoryInterface $fileStatementRepository;

    public function __construct(private readonly int $file_id)
    {
        $this->fileStatementRepository = app()->make(fileStatementRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileStatementRepository->details($this->file_id);
    }
}
