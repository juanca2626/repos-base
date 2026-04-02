<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class UpdateFileVipByIdQuery implements QueryInterface
{
    private FileVipRepositoryInterface $fileVipRepository;

    public function __construct(private readonly int $fileVipId, private readonly FileVip $params)
    {
        $this->fileVipRepository = app()->make(FileVipRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): bool
    {
        return $this->fileVipRepository->update($this->fileVipId, $this->params);
    }
}
