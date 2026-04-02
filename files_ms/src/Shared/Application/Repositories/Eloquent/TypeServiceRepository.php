<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\TypeServiceEloquentModel;
use Src\Shared\Domain\Repositories\TypeServiceRepositoryInterface;
use Src\Shared\Domain\Model\TypeService;

class TypeServiceRepository implements TypeServiceRepositoryInterface
{
    /**
     * @return array
     */
      public function search(): array
    {
        // Retorna todos los servicios de tipo en forma de array
        return TypeServiceEloquentModel::all()->toArray();
    }

     public function save(TypeService $typeService): void
    {
        // Convierte la entidad de dominio TypeService en un modelo Eloquent y la guarda
        $eloquentModel = new TypeServiceEloquentModel();
        $eloquentModel->name = $typeService->getName();
        $eloquentModel->code = $typeService->getCode(); // Asegúrate de tener un campo code en la entidad
        $eloquentModel->status = $typeService->getStatus();
        $eloquentModel->save();
    }

     public function existsByName(string $name): bool
    {
        // Verifica si existe un servicio de tipo con el mismo nombre
        return TypeServiceEloquentModel::where('name', $name)->exists();
    }
}
