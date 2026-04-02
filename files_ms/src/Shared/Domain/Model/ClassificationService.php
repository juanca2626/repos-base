<?php

namespace Src\Shared\Domain\Model;


use Src\Shared\Domain\ValueObjects\ClassificationService\Name;
use Src\Shared\Domain\ValueObjects\ClassificationService\Code;
use Src\Shared\Domain\ValueObjects\ClassificationService\Status;
use Src\Shared\Domain\Entity;

class ClassificationService extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly Code $code,
        public readonly Status $status = new Status('1')
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
            'code' => $this->code->value(),
            'status' => $this->status->value(),
        ];
    }

}
