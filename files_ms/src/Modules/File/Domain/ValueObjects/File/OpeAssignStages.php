<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

 
final class OpeAssignStages extends BooleanValueObject
{
    public function __construct(bool $opeAssignStages)
    {
        parent::__construct($opeAssignStages);
    }
}
