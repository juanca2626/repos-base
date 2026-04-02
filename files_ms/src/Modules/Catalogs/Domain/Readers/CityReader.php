<?php

namespace Src\Modules\Catalogs\Domain\Readers;

interface CityReader
{
    public function all(array $city_isos): array;
}