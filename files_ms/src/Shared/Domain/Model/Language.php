<?php

namespace Src\Shared\Domain\Model;


use Src\Shared\Domain\ValueObjects\Language\Name;
use Src\Shared\Domain\ValueObjects\Language\Active;
use Src\Shared\Domain\ValueObjects\Language\Iso;
use Src\Shared\Domain\Entity;

class Language extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly Iso $iso,
        public readonly Active $active
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso' => $this->iso,
            'active' => $this->active,
        ];
    }

}
