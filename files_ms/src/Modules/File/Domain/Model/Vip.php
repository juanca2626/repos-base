<?php

namespace Src\Modules\File\Domain\Model;


use Src\Modules\File\Domain\ValueObjects\Vip\EntityVip;
use Src\Modules\File\Domain\ValueObjects\Vip\IsoVip;
use Src\Modules\File\Domain\ValueObjects\Vip\Name;
use Src\Modules\File\Domain\ValueObjects\Vip\UserId;
use Src\Shared\Domain\Entity;

class Vip extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly UserId $userId,
        public readonly EntityVip $entityVip,
        public readonly Name $name,
        public readonly IsoVip $isoVip,

    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId->value(),
            'entity' => $this->entityVip->value(),
            'name' => $this->name->value(),
            'iso' => $this->isoVip->value(),
        ];
    }

}
