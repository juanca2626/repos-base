<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;
 
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class TypeRoomDescription extends StringValueObject
{
    public readonly string $typeRoomDescription;

    protected $typeRooms = [
        [
            'occupation' => 1,
            'description' => 'SGL'
        ], 
        [
            'occupation' => 2,
            'description' => 'DBL'
        ],  
        [
            'occupation' => 3,
            'description' => 'TPL'
        ]                  
    ];

    public function __construct(int|null $occupation)
    {
        $this->typeRoomDescription = $this->getTypeRoomDescription($occupation);

        parent::__construct($this->typeRoomDescription);
        
    }

    public function getTypeRoomDescription($occupation): string
    {
        $results = collect($this->typeRooms)->firstWhere('occupation', $occupation);

        return isset($results['description']) ? $results['description'] : '';
    } 

    public function toString(): string
    {
        return $this->typeRoomDescription;
    }

}
