<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileCategoryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileCategoryCommand implements CommandInterface
{
    private FileCategoryRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly array $params)
    {
        $this->fileRepository = app()->make(FileCategoryRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileRepository->create($this->fileId,$this->params);
    }
}
