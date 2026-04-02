<?php
namespace Src\Shared\Domain\Auth;

interface TokenValidatorInterface_DELETE
{
    public function validate(string $jwt): array;
}
