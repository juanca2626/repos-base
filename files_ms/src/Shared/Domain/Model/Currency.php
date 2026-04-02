<?php

namespace Src\Shared\Domain\Model;


use Src\Shared\Domain\ValueObjects\Currency\Name;
use Src\Shared\Domain\ValueObjects\Currency\Code;
use Src\Shared\Domain\ValueObjects\Currency\Status;
use Src\Shared\Domain\Entity;

class Currency extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly Code $code,
        public readonly Status $status
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
            'status' => $this->status,
        ];
    }

}
