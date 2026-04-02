<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\File\Description;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDescription\Code;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDescription\LanguageId;
use Src\Shared\Domain\Entity;


class FileItineraryDescription extends Entity
{


    public function __construct(
        public readonly ?int $id,
        public readonly FileItineraryId $fileItineraryId,
        public readonly LanguageId $languageId,
        public readonly Code $code,
        public readonly Description $description,
    ) {
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
