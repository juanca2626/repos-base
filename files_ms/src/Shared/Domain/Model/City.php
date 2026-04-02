<?php

namespace Src\Shared\Domain\Model;


use Src\Shared\Domain\ValueObjects\City\Name;
use Src\Shared\Domain\ValueObjects\City\Country_id;

use Src\Shared\Domain\Entity;

class City extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly Name $name,
        public readonly Country_id $country_id,
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country_id' => $this->country_id,
        ];
    }

}
