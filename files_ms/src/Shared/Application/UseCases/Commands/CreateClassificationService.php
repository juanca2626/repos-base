<?php

namespace Src\Shared\Application\UseCases\Commands;

use Src\Shared\Domain\Model\ClassificationService;
use Src\Shared\Domain\Repositories\ServiceClassificationRepositoryInterface;
use Src\Shared\Domain\ValueObjects\ClassificationService\Name;
use Src\Shared\Domain\ValueObjects\ClassificationService\Code;
use Src\Shared\Domain\ValueObjects\ClassificationService\Status;

class CreateClassificationService
{
    protected ServiceClassificationRepositoryInterface $classificationServiceRepository;

    public function __construct(ServiceClassificationRepositoryInterface $classificationServiceRepository)
    {
        $this->classificationServiceRepository = $classificationServiceRepository;
    }

    /**
     * Maneja la creación de un nuevo servicio de clasificación.
     *
     * @param array $data
     * @return ClassificationService
     * @throws \DomainException
     */
    public function handle(array $data): ClassificationService
    {
        // Validar si ya existe un servicio de clasificación con el mismo nombre o código
        if ($this->classificationServiceRepository->existsByName($data['name'])) {
            throw new \DomainException('A classification service with this name already exists.');
        }

        // Asignar valor por defecto a status si no está presente
        $status = $data['status'] ?? '1';

        // Crear la entidad ClassificationService
        $classificationService = new ClassificationService(
            id: null, // El ID será generado automáticamente
            name: new Name($data['name']),
            code: new Code($data['code']),
            status: new Status($status)
        );

        // Guardar la entidad en el repositorio
        $this->classificationServiceRepository->save($classificationService);

        return $classificationService;
    }
}
