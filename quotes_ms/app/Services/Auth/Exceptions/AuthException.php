<?php

namespace App\Services\Auth\Exceptions;

use Exception;
use Throwable;

class AuthException extends Exception
{
    /**
     * Contexto adicional del error
     * @var array
     */
    protected array $context;

    /**
     * @param string $message Mensaje descriptivo del error
     * @param int $code Código de estado HTTP (por defecto 500)
     * @param array $context Datos adicionales para debugging
     * @param Throwable|null $previous Excepción previa
     */
    public function __construct(
        string $message = 'Authentication failed',
        int $code = 500,
        array $context = [],
        Throwable $previous = null
    ) {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Obtiene el contexto adicional del error
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Representación string de la excepción
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n" .
               "Context: " . json_encode($this->context) . "\n";
    }
}
