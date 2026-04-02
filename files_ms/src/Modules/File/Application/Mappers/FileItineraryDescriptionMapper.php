<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileItineraryDescription;
use Src\Modules\File\Domain\ValueObjects\File\Description;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDescription\Code;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDescription\LanguageId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryDescriptionEloquentModel;

class FileItineraryDescriptionMapper
{
    public static function fromArray(array $itineraryFlights): FileItineraryDescription
    {
        $fileItineraryFlightEloquentModel = new FileItineraryDescriptionEloquentModel($itineraryFlights);
        $fileItineraryFlightEloquentModel->id = $itineraryFlights['id'] ?? null;
        return self::fromEloquent($fileItineraryFlightEloquentModel);
    }

    public static function fromEloquent(FileItineraryDescriptionEloquentModel $fileItineraryEloquent
    ): FileItineraryDescription {
        return new FileItineraryDescription(
            id: $fileItineraryEloquent->id,
            fileItineraryId: new FileItineraryId($fileItineraryEloquent->file_itinerary_id),
            languageId: new LanguageId($fileItineraryEloquent->language_id),
            code: new Code($fileItineraryEloquent->code),
            description: new Description($fileItineraryEloquent->description),
        );
    }
}
