<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdatePassengersCommand implements CommandInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly array $passengers)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->updatePassengers($this->fileId, $this->passengers);
    }
}
