<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class RevisionStages extends IntOrNullValueObject
{
    public function __construct(int|null $revisionStages)
    {
        parent::__construct($revisionStages);
    }
}
