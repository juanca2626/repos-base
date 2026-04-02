<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class TicketSent extends BooleanValueObject
{
    public function __construct(bool $ticketSent)
    {
        parent::__construct($ticketSent);
    }
}
