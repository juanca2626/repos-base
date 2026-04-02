<?php

namespace Src\Shared\Domain\Auth;

class AuthUser
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $email,
        public string $user_type_id,
        public string $is_kam,
        public string $language_id,
        public string $rol,
        public string $status,
        public array $permissions = [],
        public array $department = []
    ) {}
}