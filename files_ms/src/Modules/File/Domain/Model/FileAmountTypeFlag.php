<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Icon;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Name;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Description;
use Src\Shared\Domain\Entity;

class FileAmountTypeFlag extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly Description $description,
        public readonly Icon $icon,
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name->value(),
            'description' => $this->description->value(),
            'icon' => $this->icon->value(),
        ];
    }

}
