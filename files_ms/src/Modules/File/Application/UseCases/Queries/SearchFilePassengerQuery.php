<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FilePassengerRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Shared\Domain\QueryInterface;

class SearchFilePassengerQuery implements QueryInterface
{
    private FilePassengerRepositoryInterface $filePassengerRepository;

    public function __construct(private readonly int $fileId)
    {
        $this->filePassengerRepository = app()->make(FilePassengerRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->filePassengerRepository->searchAll($this->fileId);
    }
}
