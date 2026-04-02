<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceUnitConfirmationCodeCommand implements CommandInterface
{
    private FileServiceUnitRepositoryInterface $FileServiceUnitRepository;

    public function __construct(private readonly int $fileServiceUnitId, private readonly string $code)
    {
        $this->FileServiceUnitRepository = app()->make(FileServiceUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->FileServiceUnitRepository->updateConfirmationCode($this->fileServiceUnitId, $this->code);
    }
}
