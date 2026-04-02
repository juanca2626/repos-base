<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileClassificationId extends IntOrNullValueObject
{
    public function __construct(int|null $file_classification_id)
    {
        parent::__construct($file_classification_id);
    }
}
