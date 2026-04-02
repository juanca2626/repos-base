<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\Vip;
use Src\Modules\File\Domain\Repositories\VipRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateVipCommand implements CommandInterface
{
    private VipRepositoryInterface $repository;

    public function __construct(private readonly Vip $vip)
    {
        $this->repository = app()->make(VipRepositoryInterface::class);
    }

    public function execute(): Vip
    {
        return $this->repository->create($this->vip);
    }
}
