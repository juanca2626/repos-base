<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use Src\Shared\Application\Mappers\NotificationMapper;
use Src\Shared\Domain\Model\Notification;
use Src\Shared\Domain\Repositories\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @return array
     */
    public function create(Notification $notification): Notification
    {
        return DB::transaction(function () use ($notification) {
            $notificationEloquent = NotificationMapper::toEloquent($notification);
            $notificationEloquent->save();
            return NotificationMapper::fromEloquent($notificationEloquent);
        });
    }
}
