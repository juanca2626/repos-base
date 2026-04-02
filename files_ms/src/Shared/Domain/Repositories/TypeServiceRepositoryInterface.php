<?php

namespace Src\Shared\Domain\Repositories;
use Src\Shared\Infrastructure\EloquentModel\TypeServiceEloquentModel;
use Src\Shared\Domain\Model\TypeService;

interface TypeServiceRepositoryInterface
{
    public function search(): array;

    // Método para guardar un nuevo servicio de tipo
    public function save(TypeService $typeService): void;

    public function existsByName(string $name): bool;
}
