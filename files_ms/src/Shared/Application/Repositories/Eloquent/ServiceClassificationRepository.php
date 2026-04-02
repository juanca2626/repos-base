<?php

namespace Src\Shared\Application\Repositories\Eloquent;

use Src\Shared\Infrastructure\EloquentModel\ServiceClassificationEloquentModel;
use Src\Shared\Domain\Repositories\ServiceClassificationRepositoryInterface;
use Src\Shared\Domain\Model\ClassificationService;

class ServiceClassificationRepository implements ServiceClassificationRepositoryInterface
{
    /**
     * @return array
     */
    public function search(): array
    {
        $currency = ServiceClassificationEloquentModel::query()->get();
        return $currency->toArray();
    }

     /**
     * Guarda una nueva clasificación de servicio.
     *
     * @param ClassificationService $classificationService
     * @return void
     */
    public function save(ClassificationService $classificationService): void
    {
        $eloquentModel = new ServiceClassificationEloquentModel();
        $eloquentModel->name = $classificationService->name->value();
        $eloquentModel->code = $classificationService->code->value();
        $eloquentModel->status = $classificationService->status->value();
        
        $eloquentModel->save();
    }

    /**
     * Verifica si ya existe una clasificación de servicio con el mismo nombre.
     *
     * @param string $name
     * @return bool
     */
    public function existsByName(string $name): bool
    {
        return ServiceClassificationEloquentModel::where('name', $name)->exists();
    }

    /**
     * Busca una clasificación de servicio por nombre.
     *
     * @param string $name
     * @return ClassificationService|null
     */
    public function findByName(string $name): ?ClassificationService
    {
        $eloquentModel = ServiceClassificationEloquentModel::where('name', $name)->first();

        if (!$eloquentModel) {
            return null; // No se encontró
        }

        return new ClassificationService(
            $eloquentModel->id,
            new Name($eloquentModel->name),
            new Code($eloquentModel->code),
            new Status($eloquentModel->status)
        );
    }

    /**
     * Obtiene todas las clasificaciones de servicio.
     *
     * @return array
     */
    public function getAll(): array
    {
        $serviceClassifications = ServiceClassificationEloquentModel::all();
        return $serviceClassifications->toArray();
    }

    public function findById(int $id): ?ClassificationService
{
    return ServiceClassificationEloquentModel::find($id);
}

    
}
