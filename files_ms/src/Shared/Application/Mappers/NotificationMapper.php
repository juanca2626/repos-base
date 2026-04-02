<?php

namespace Src\Shared\Application\Mappers;


use Illuminate\Http\Request;
use Src\Shared\Domain\Model\Notification;
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
use Src\Shared\Infrastructure\EloquentModel\NotificationEloquentModel;

class NotificationMapper
{
    public static function fromRequestCreate(Request $request, ?int $notificationId = null): Notification
    {
        $subject = $request->input('subject', '');
        $module = $request->input('module', '');
        $submodule = $request->input('submodule', '');
        $object_id = $request->input('object_id', 0);
        $data = $request->input('data', []);
        $to = $request->input('to', []);
        $cc = $request->input('cc', []);
        $bcc = $request->input('bcc', []);
        $attachments = $request->input('attachments', []);
        $message_id = $request->input('message_id', '');
        $notification_id = $request->input('notification_id', '');
     
        return new Notification(
            id: $notificationId,
            subject: new Subject($subject),
            module: new Module($module),
            submodule: new Submodule($submodule),
            objectId: new ObjectId($object_id),
            data: new Data($data),
            to: new To($to),
            cc: new Cc($cc),
            bcc: new Bcc($bcc),
            attachments: new Attachments($attachments),
            messageId: new MessageId($message_id),
            notificationId: new NotificationId($notification_id),
        );
    }

    public static function fromEloquent(NotificationEloquentModel $notificationEloquentModel): Notification {
        return new Notification(
            id: $notificationEloquentModel->id,
            subject: new Subject($notificationEloquentModel->subject),
            module: new Module($notificationEloquentModel->module),
            submodule: new Submodule($notificationEloquentModel->submodule),
            objectId: new ObjectId($notificationEloquentModel->object_id),
            data: new Data($notificationEloquentModel->data),
            to: new To($notificationEloquentModel->to),
            cc: new Cc($notificationEloquentModel->cc),
            bcc: new Bcc($notificationEloquentModel->bcc),
            attachments: new Attachments($notificationEloquentModel->attachments),
            messageId: new MessageId($notificationEloquentModel->message_id),
            notificationId: new NotificationId($notificationEloquentModel->notification_id),
        );
    }

    public static function toEloquent(Notification $notification): NotificationEloquentModel
    {
        $notificationEloquent = new NotificationEloquentModel();

        if ($notification->id) {
            $notificationEloquent = NotificationEloquentModel::query()
                ->where('id', '=', $notification->id)->first();
        }

        $notificationEloquent->subject = $notification->subject->value();
        $notificationEloquent->module = $notification->module->value();
        $notificationEloquent->submodule = $notification->submodule->value();
        $notificationEloquent->object_id = $notification->objectId->value();
        $notificationEloquent->data = $notification->data->value();
        $notificationEloquent->to = $notification->to->value();
        $notificationEloquent->cc = $notification->cc->value();
        $notificationEloquent->bcc = $notification->bcc->value();
        $notificationEloquent->attachments = $notification->attachments->value();
        $notificationEloquent->message_id = $notification->messageId->value();
        $notificationEloquent->notification_id = $notification->notificationId->value();

        return $notificationEloquent;
    }

    public static function toArray(Notification $notification): array
    {
        $notificationEloquent = new NotificationEloquentModel();
        $notificationEloquent->id = $notification->id;
        $notificationEloquent->subject = $notification->subject->value();
        $notificationEloquent->module = $notification->module->value();
        $notificationEloquent->submodule = $notification->submodule->value();
        $notificationEloquent->object_id = $notification->objectId->value();
        $notificationEloquent->data = $notification->data->value();
        $notificationEloquent->to = $notification->to->value();
        $notificationEloquent->cc = $notification->cc->value();
        $notificationEloquent->bcc = $notification->bcc->value();
        $notificationEloquent->attachments = $notification->attachments->value();
        $notificationEloquent->message_id = $notification->messageId->value();
        $notificationEloquent->notification_id = $notification->notificationId->value();

        return $notificationEloquent->toArray();
    }

    public static function fromArray(array $notification): Notification
    {
        $notificationEloquentModel = new NotificationEloquentModel($notification);
        $notificationEloquentModel->id = $notification['id'] ?? null;
        return self::fromEloquent($notificationEloquentModel);
    }
}
