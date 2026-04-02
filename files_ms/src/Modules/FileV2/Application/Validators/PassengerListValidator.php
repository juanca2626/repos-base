<?php

namespace Src\Modules\FileV2\Application\Validators;

class PassengerListValidator
{
    public function validate(array $passengers): void
    {
        if (empty($passengers)) {
            throw new \Exception('Debe existir al menos un pasajero');
        }

        foreach ($passengers as $index => $p) {

            // SOLO EL PRIMERO ES OBLIGATORIO
            if ($index === 0) {
                if (empty($p['name'])) {
                    throw new \Exception('El primer pasajero debe tener nombre');
                }
            }

            // estructura mínima
            if (!is_array($p)) {
                throw new \Exception("Passenger[$index] formato inválido");
            }
        }
    }
}