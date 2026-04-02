<?php

namespace App\Http\Controllers\Pentagrama;

use App\Http\Traits\CalculateCancellationPolicies as TraitCalculateCancellationPolicies;
use App\Http\Traits\Package as TraitPackage;
use App\Http\Traits\QuoteDetailsPricePassengers;
use App\Http\Traits\QuoteDetailsPriceRange;
use App\Http\Traits\QuoteHistories;
use App\Http\Traits\Quotes;
use Psr\Log\LoggerInterface;

/**
 * Clase de servicio básica para Pentagrama.
 * Provee métodos CRUD mínimos; sustituir la lógica interna
 * por llamadas reales a repositorios o modelos según sea necesario.
 */
class PentagramaService
{
    use Quotes;
    use TraitPackage;
    use QuoteHistories;
    use TraitCalculateCancellationPolicies;
    use QuoteDetailsPricePassengers;
    use QuoteDetailsPriceRange;

    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Crear un recurso (stub).
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $this->logger->info('PentagramaService:create', $data);

        // Simular persistencia devolviendo los datos creados.
        return $data;
    }
}
