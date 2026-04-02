<?php

namespace Src\Modules\Notes\Domain\Readers;

interface NoteClassificationReader
{
    public function all(): array;
}