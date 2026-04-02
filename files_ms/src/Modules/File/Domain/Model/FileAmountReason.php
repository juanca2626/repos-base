<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Name;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\InfluencesSale;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Area;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Process;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Visible;
use Src\Shared\Domain\Entity;

class FileAmountReason extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly InfluencesSale $influencesSale,
        public readonly Area $area,
        public readonly Visible $visible,
        public readonly Process $process,
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
            'influences_sale' => $this->influencesSale->value(),
            'area' => $this->area->value(),
            'visible' => $this->visible->value(),
            'process' => $this->process->value(),
        ];
    }

}
