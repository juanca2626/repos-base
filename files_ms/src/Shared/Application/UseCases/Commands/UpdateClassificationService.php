<?php

namespace Src\Shared\Application\UseCases\Commands;

use Src\Shared\Domain\Model\ClassificationService;
use Src\Shared\Domain\Repositories\ServiceClassificationRepositoryInterface;

class UpdateClassificationService
{
    protected ServiceClassificationRepositoryInterface $serviceClassificationRepository;

    public function __construct(ServiceClassificationRepositoryInterface $serviceClassificationRepository)
    {
        $this->serviceClassificationRepository = $serviceClassificationRepository;
    }

    public function handle(int $id, array $data): ClassificationService
    {
        // Buscar el servicio por ID
        $serviceClassification = $this->serviceClassificationRepository->findById($id);

        if (!$serviceClassification) {
            throw new \DomainException('Service classification not found.');
        }

        // Actualizar los campos del servicio
        $serviceClassification->name = $data['name'];
        $serviceClassification->description = $data['description'] ?? $serviceClassification->description;
        $serviceClassification->status = $data['status'] ?? $serviceClassification->status;
        $serviceClassification->code = $data['code'] ?? $serviceClassification->code;

        // Guardar los cambios en el repositorio
        $this->serviceClassificationRepository->save($serviceClassification);

        return $serviceClassification;
    }
}