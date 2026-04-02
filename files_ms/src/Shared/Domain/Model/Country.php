<?php

namespace Src\Shared\Domain\Model;


use Src\Shared\Domain\ValueObjects\Country\Name;
use Src\Shared\Domain\ValueObjects\Country\Code;
use Src\Shared\Domain\Entity;

class Country extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly Code $code,
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
            'code' => $this->code,
        ];
    }

}
