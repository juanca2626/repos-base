<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\InvalidTypeException;
use Src\Shared\Domain\ValueObject;

class EnumValueObject extends ValueObject
{
    private string $type;

    private array $allowedTypes;

    public function __construct(string $type, array $allowedTypes = [])
    { 
        $this->setAllowedTypes($allowedTypes);
        $this->setType($type);
    }

    private function setType(string $type): void
    {
        $type = strtolower($type);

        if (!in_array($type, $this->allowedTypes, true)) {
            throw new InvalidTypeException("Invalid type: {$type}. Allowed types are " . implode(', ',
                    $this->allowedTypes), 400);
        }

        $this->type = $type;
    }

    private function setAllowedTypes(array $allowedTypes): void
    {
        if (empty($allowedTypes)) {
            // Si no se proporcionan tipos permitidos, utiliza un conjunto predeterminado
            $allowedTypes = ['service', 'service-mask', 'service-temporary', 'hotel', 'flight'];
        }

        $this->allowedTypes = array_map('strtolower', $allowedTypes);
    }



    public function __toString(): string
    {
        return $this->type;
    }

    public function value(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): string
    {
        return $this->type;
    }
}
