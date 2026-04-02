<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateAmountFromAuroraCommand implements CommandInterface
{
    private FileRepositoryInterface $repository;

    public function __construct(private readonly array $params)
    {
        $this->repository = app()->make(FileRepositoryInterface::class);
    }

    public function execute(): array
    {
        return $this->repository->updateAmountFromAurora($this->params);
    }
}
