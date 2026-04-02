<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryArray extends ValueObjectArray
{
    public readonly array $fileItinerary;

    public function __construct(FileItinerary $fileItinerary, array $params)
    {
        parent::__construct($fileItinerary);    
        $this->fileItinerary = $this->parser($fileItinerary);
    }

    /**
     * @return array
     */
    public function parser($fileItinerary): array
    {
        $data = $fileItinerary->toArray();
  
        $rooms = [];
        foreach( $data['rooms']->toArray() as $id => $room){

            $units = [];

            foreach( $room['units']->toArray() as $idx => $unit){
                array_push($units, $unit );
            }

            $room['units'] = $units;

            array_push($rooms, $room );
        }

        $data['rooms'] = $rooms;

        return $data;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItinerary;
    }
}
