<?php

namespace Src\Modules\File\Domain\ValueObjects\FileCategory;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class CategoryId extends IntOrNullValueObject
{
    public function __construct(int|null $categoryId)
    {
        parent::__construct($categoryId);
    }
}
