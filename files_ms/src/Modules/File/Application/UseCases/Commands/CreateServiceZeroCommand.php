<?php
namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\ServiceZero;
use Src\Modules\File\Domain\Repositories\ServiceZeroRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateServiceZeroCommand implements CommandInterface
{
    private ServiceZeroRepositoryInterface $repository;

    public function __construct(private readonly ServiceZero $serviceZero)
    {
        $this->repository = app()->make(ServiceZeroRepositoryInterface::class);
    }

    public function execute(): ServiceZero
    {
        return $this->repository->create($this->serviceZero);
    }
}
