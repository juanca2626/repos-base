<?php

namespace Src\Modules\Catalogs\Domain\Readers;

interface ExecutiveReader
{
    public function all(): array;
}