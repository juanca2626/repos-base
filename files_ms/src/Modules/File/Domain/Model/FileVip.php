<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FileVip\Vip;
use Src\Modules\File\Domain\ValueObjects\FileVip\Vips;
use Src\Modules\File\Domain\ValueObjects\Vip\VipId;
use Src\Shared\Domain\Entity;

class FileVip extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly FileId $fileId,
        public readonly VipId $vipId,
        public readonly Vip $vip,
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_id' => $this->fileId->value(),
            'vip_id' => $this->vipId->value(),
            'vip' => $this->vip->jsonSerialize(),
        ];
    }

}
