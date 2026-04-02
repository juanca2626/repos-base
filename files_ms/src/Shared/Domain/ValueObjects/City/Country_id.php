<?php

namespace Src\Shared\Domain\ValueObjects\City;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Country_id extends IntValueObject
{
    public function __construct(int $country_id)
    {
        parent::__construct($country_id);
    }
}
