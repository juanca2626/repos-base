<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileServicesStelaCommand implements CommandInterface
{
    private FileRepositoryInterface $repository;

    public function __construct(private readonly int $file ,private readonly array $params)
    {
        $this->repository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->create_file_services_stela($this->file, $this->params);
    }
}
