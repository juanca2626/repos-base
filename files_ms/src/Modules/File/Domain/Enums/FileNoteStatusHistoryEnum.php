<?php

namespace Src\Modules\File\Domain\Enums;

enum FileNoteStatusHistoryEnum: string
{
    case PENDING = 'pending';
    case RECEIVED = 'received';
    case REJECTED = 'refused';
    case APPROVED = 'approved';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'pending',
            self::RECEIVED => 'received',
            self::REJECTED => 'refused',
            self::APPROVED => 'approved',
        };
    }
}
