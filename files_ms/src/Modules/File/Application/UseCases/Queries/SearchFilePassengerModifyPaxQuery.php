<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FilePassengerModifyPaxRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFilePassengerModifyPaxQuery implements QueryInterface
{
    private FilePassengerModifyPaxRepositoryInterface $filePassengerModifyPaxRepository;

    public function __construct(private readonly int $fileId)
    {
        $this->filePassengerModifyPaxRepository = app()->make(FilePassengerModifyPaxRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->filePassengerModifyPaxRepository->searchAll($this->fileId);
    }
}
