<?php
 
namespace Src\Modules\File\Domain\ValueObjects\FileCategory;


use Src\Shared\Domain\ValueObject;

final class Category extends ValueObject
{
    public readonly object $category;

    public function __construct(object $category)
    { 
        $this->category = $category;
    }

    public function getFilePassenger(): Category
    {
        return new Category($this->category);
    }

    public function toArray(): object
    {
        return $this->category;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->category;
    }
}
