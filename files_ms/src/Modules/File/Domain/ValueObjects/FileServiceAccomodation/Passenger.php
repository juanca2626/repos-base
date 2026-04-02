<?php
 
namespace Src\Modules\File\Domain\ValueObjects\FileServiceAccomodation;


use Src\Shared\Domain\ValueObject;

final class Passenger extends ValueObject
{
    public readonly object $passenger;

    public function __construct(object $passenger)
    { 
        $this->passenger = $passenger;
    }

    public function getFilePassenger(): Passenger
    {
        return new Passenger($this->passenger);
    }

    public function toArray(): object
    {
        return $this->passenger;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->passenger;
    }
}
