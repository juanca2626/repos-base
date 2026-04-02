<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileTemporaryServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail\LanguageId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail\Itinerary;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail\Skeleton; 
use Src\Shared\Domain\Entity;

class FileTemporaryServiceDetail extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly FileTemporaryServiceId $fileTemporaryServiceId,
        public readonly LanguageId $languageId,
        public readonly Itinerary $itinerary,
        public readonly Skeleton $skeleton
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_temporary_service_id' => $this->fileTemporaryServiceId->value(),
            'language_id' => $this->languageId->value(),
            'itinerary' => $this->itinerary->value(),
            'skeleton' => $this->skeleton->value() 
        ];
    }

}
