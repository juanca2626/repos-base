<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Src\Shared\Application\Mappers\NotificationMapper;
use Src\Shared\Application\UseCases\Commands\CreateNotificationCommand;
use Src\Shared\Presentation\Http\Resources\NotificationResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class NotificationController extends Controller
{
    use ApiResponse;

    public function store(Request $request): NotificationResource
    {
        try {
            $newNotification = NotificationMapper::fromRequestCreate($request);
            $notification = (new CreateNotificationCommand($newNotification))->execute();
            return new NotificationResource($notification);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
