<?php

namespace Src\Modules\File\Domain\Enums;

enum FileNoteStatusEnum: int
{
    case ACTIVE = 1;
    case PENDING = 2;
    case RECEIVED = 3;
    case REFUSED = 4;
    case APPROVED = 5;

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'active',
            self::PENDING => 'pending',
            self::RECEIVED => 'received',
            self::REFUSED => 'refused',
            self::APPROVED => 'approved',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match(strtolower($label)) {
            'active' => self::ACTIVE,
            'pending' => self::PENDING,
            'received' => self::RECEIVED,
            'refused' => self::REFUSED,
            'approved' => self::APPROVED,
            default => throw new \InvalidArgumentException("Estado no válido: $label"),
        };
    }
}
