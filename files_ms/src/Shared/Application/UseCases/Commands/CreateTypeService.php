<?php

namespace Src\Shared\Application\UseCases\Commands;

use Src\Shared\Domain\Repositories\TypeServiceRepository;
use Src\Shared\Domain\Models\TypeService;

class CreateTypeService
{
    protected TypeServiceRepository $typeServiceRepository;

    public function __construct(TypeServiceRepository $typeServiceRepository)
    {
        $this->typeServiceRepository = $typeServiceRepository;
    }

    /**
     * Maneja la lógica para crear un nuevo servicio de tipo.
     *
     * @param array $data
     * @return TypeService
     * @throws \DomainException
     */
    public function handle(array $data): TypeService
    {
        // Validar si el servicio de tipo ya existe (opcional)
        if ($this->typeServiceRepository->existsByName($data['name'])) {
            throw new \DomainException('A type service with this name already exists.');
        }

        // Crear un nuevo objeto TypeService con los datos proporcionados
        $typeService = new TypeService([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Guardar el nuevo servicio en el repositorio
        $this->typeServiceRepository->save($typeService);

        // Retornar el servicio creado
        return $typeService;
    }
}