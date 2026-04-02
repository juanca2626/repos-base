<?php

namespace Src\Shared\Application\UseCases\Commands;

use Src\Shared\Domain\Model\Notification;
use Src\Shared\Domain\Repositories\NotificationRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateNotificationCommand implements CommandInterface
{
    private NotificationRepositoryInterface $repository;

    public function __construct(private readonly Notification $notification)
    {
        $this->repository = app()->make(NotificationRepositoryInterface::class);
    }

    public function execute(): Notification
    {
        return $this->repository->create($this->notification);
    }
}
