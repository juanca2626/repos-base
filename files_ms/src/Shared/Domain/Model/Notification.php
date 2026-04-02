<?php

namespace Src\Shared\Domain\Model;

use Src\Shared\Domain\Entity;
use Src\Shared\Domain\ValueObjects\Notification\Attachments;
use Src\Shared\Domain\ValueObjects\Notification\Bcc;
use Src\Shared\Domain\ValueObjects\Notification\Cc;
use Src\Shared\Domain\ValueObjects\Notification\Data;
use Src\Shared\Domain\ValueObjects\Notification\MessageId;
use Src\Shared\Domain\ValueObjects\Notification\Module;
use Src\Shared\Domain\ValueObjects\Notification\NotificationId;
use Src\Shared\Domain\ValueObjects\Notification\ObjectId;
use Src\Shared\Domain\ValueObjects\Notification\Subject;
use Src\Shared\Domain\ValueObjects\Notification\Submodule;
use Src\Shared\Domain\ValueObjects\Notification\To;

class Notification extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Subject $subject,
        public readonly Module $module,
        public readonly Submodule $submodule,
        public readonly ObjectId $objectId,
        public readonly Data $data,
        public readonly To $to,
        public readonly Cc $cc,
        public readonly Bcc $bcc,
        public readonly Attachments $attachments,
        public readonly MessageId $messageId,
        public readonly NotificationId $notificationId,
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'module' => $this->module,
            'submodule' => $this->submodule,
            'object_id' => $this->objectId,
            'data' => $this->data,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'attachments' => $this->attachments,
            'message_id' => $this->messageId,
            'notification_id' => $this->notificationId,
        ];
    }

}
