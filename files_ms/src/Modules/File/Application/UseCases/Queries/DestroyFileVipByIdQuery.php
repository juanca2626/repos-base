<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class DestroyFileVipByIdQuery implements QueryInterface
{
    private FileVipRepositoryInterface $fileVipRepository;

    public function __construct(private readonly int $file_id, private readonly int $vip_id)
    {
        $this->fileVipRepository = app()->make(FileVipRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): bool
    {
        return $this->fileVipRepository->delete($this->file_id, $this->vip_id);
    }
}
