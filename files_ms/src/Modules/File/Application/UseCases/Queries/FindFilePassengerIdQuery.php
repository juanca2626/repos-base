<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FilePassengerRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFilePassengerIdQuery implements QueryInterface
{
    private FilePassengerRepositoryInterface $filePassengerRepository;

    public function __construct(private readonly int $filePassengerId)
    {
        $this->filePassengerRepository = app()->make(FilePassengerRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->filePassengerRepository->findById($this->filePassengerId);
    }
}
