<?php

namespace Src\Shared\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class NotificationResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject->value(),
            'module' => $this->module->value(),
            'submodule' => $this->submodule->value(),
            'object_id' => $this->objectId->value(),
            'data' => $this->data->value(),
            'to' => $this->to->value(),
            'cc' => $this->cc->value(),
            'bcc' => $this->bcc->value(),
            'attachments' => $this->attachments->value(),
            'message_id' => $this->messageId->value(),
            'notification_id' => $this->notificationId->value(),
        ];
    }
}