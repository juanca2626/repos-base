<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileVipByIdQuery implements QueryInterface
{
    private FileVipRepositoryInterface $fileVipRepository;

    public function __construct(private readonly int $id)
    {
        $this->fileVipRepository = app()->make(FileVipRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): FileVip
    {
        return $this->fileVipRepository->findById($this->id);
    }
}
