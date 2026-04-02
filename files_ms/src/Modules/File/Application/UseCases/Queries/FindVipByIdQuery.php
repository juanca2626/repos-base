<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\Vip;
use Src\Modules\File\Domain\Repositories\VipRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindVipByIdQuery implements QueryInterface
{
    private VipRepositoryInterface $vipRepository;

    public function __construct(private readonly int $id)
    {
        $this->vipRepository = app()->make(VipRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): Vip
    {
        return $this->vipRepository->findById($this->id);
    }
}
