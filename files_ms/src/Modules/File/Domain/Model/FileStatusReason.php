<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;   
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\FileId;
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\Status;
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\StatusReasonId;
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\StatusReason; 
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\CreatedAt;

use Src\Shared\Domain\Entity;

class FileStatusReason extends Entity
{
    public function __construct( 
        public readonly ?int $id,
        public readonly FileId $fileId,
        public readonly Status $status,   
        public readonly StatusReasonId $statusReasonId,                
        public readonly CreatedAt $createdAt, 
        public readonly StatusReason $statusReason,
 
    ) {
    }

    public function getDate(): string
    {
        return Carbon::parse($this->createdAt)->format('d/m/Y H:i:s');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_id' => $this->fileId->value(),
            'status' => $this->status->value(),
            'status_reason_id' => $this->statusReasonId->value(),             
            'created_at' => $this->createdAt->value()
        ];
    }

}
