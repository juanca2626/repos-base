<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDetail\LanguageId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDetail\Itinerary;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDetail\Skeleton; 
use Src\Shared\Domain\Entity;

class FileItineraryDetail extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly FileItineraryId $fileItineraryId,
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

        ];
        // TODO: Implement toArray() method.
    }

}
