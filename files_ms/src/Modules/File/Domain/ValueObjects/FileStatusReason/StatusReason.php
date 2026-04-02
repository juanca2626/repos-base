<?php
 
namespace Src\Modules\File\Domain\ValueObjects\FileStatusReason;


use Src\Shared\Domain\ValueObject;

final class StatusReason extends ValueObject
{
    public readonly object $statusReason;

    public function __construct(object $statusReason)
    { 
        $this->statusReason = $statusReason;
    }

    public function getFilePassenger(): StatusReason
    {
        return new StatusReason($this->statusReason);
    }

    public function toArray(): object
    {
        return $this->statusReason;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->statusReason;
    }
}
