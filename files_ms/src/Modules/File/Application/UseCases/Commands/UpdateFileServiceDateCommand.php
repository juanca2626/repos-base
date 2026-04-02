<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\FileService\StartTime;
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceDateCommand implements CommandInterface
{
    private FileServiceRepositoryInterface $fileServiceRepository;

    public function __construct(private readonly int $fileServiceId, private readonly string $params)
    {
        $this->fileServiceRepository = app()->make(FileServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): array
    {
        return $this->fileServiceRepository->updateDate($this->fileServiceId, $this->params);
    }
}
