<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileCommand implements CommandInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly array $file)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): File
    {
        return $this->fileRepository->update($this->fileId, $this->file);
    }
}
