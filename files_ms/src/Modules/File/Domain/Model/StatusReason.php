<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\StatusReason\Visible;
use Src\Modules\File\Domain\ValueObjects\StatusReason\Name;
use Src\Modules\File\Domain\ValueObjects\StatusReason\StatusIso;
use Src\Shared\Domain\Entity;  

class StatusReason extends Entity
{    
    public function __construct(
        public readonly ?int $id, 
        public readonly StatusIso $statusIso,
        public readonly Name $name,       
        public readonly Visible $visible,
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
            'status_iso' => $this->statusIso->value(),
            'visible' => $this->visible->value(),
        ];
    }
  

}
