<?php

namespace Src\Shared\Domain\Repositories;
use Src\Shared\Domain\Model\ClassificationService;

interface ServiceClassificationRepositoryInterface
{
   public function save(ClassificationService $classificationService): void;

    public function existsByName(string $name): bool;

    public function findByName(string $name): ?ClassificationService;

    public function getAll(): array; 
}
