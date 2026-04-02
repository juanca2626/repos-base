<?php

namespace Src\Shared\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Shared\Domain\Model\Notification;


interface NotificationRepositoryInterface
{
    public function create(Notification $notification): Notification;
}
