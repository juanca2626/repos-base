<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class SyncropaxSeeder extends Seeder
{
    public function run()
    {
        $path = public_path('paxs.csv');

        if (!file_exists($path)) {
            $this->command->error("El archivo CSV no existe en: $path");
            return;
        }

        if (($handle = fopen($path, 'r')) !== false) {
            // Cabecera del CSV
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $record = array_combine($header, $row);

                // solo status OK
                if (trim($record['status']) !== 'OK') {
                    continue;
                }

                // Buscar reservation_id
                $reservation = DB::table('reservations')
                    ->where('file_code', $record['nroref'])
                    ->first();

                if (!$reservation) {
                    $this->command->warn("No se encontró reservation con file_code = {$record['nroref']}");
                    continue;
                }

                $reservationId = $reservation->id;

                // Normalizar nombre/apellidos
                $fullName = trim($record['nombre']);
                // separar por coma
                $parts = array_map('trim', explode(',', $fullName, 2));
                $surnames = $parts[0] ?? null;   // antes de la coma
                $name     = $parts[1] ?? null;   // después de la coma

                // Procesar teléfono
                $phoneData = $this->fixPhoneNumber($record['celula']);

                // updateOrInsert para asegurar unicidad reservation_id + nrosec
                DB::table('reservation_passengers')->updateOrInsert(
                    [
                        'reservation_id'  => $reservationId,
                        'sequence_number' => (int) $record['nrosec'],
                    ],
                    [
                        'order_number'        => (int) $record['nroord'],
                        'frequent'            => trim($record['nropax']),
                        'doctype_iso'         => trim($record['tipdoc']),
                        'document_number'     => trim($record['nrodoc']),
                        'name'                => $name,
                        'surnames'            => $surnames,
                        'date_birth'          => $record['fecnac'] ?: null,
                        'type'                => $record['tipo'] ?: 'ADL',
                        'suggested_room_type' => $record['tiphab'] ?: null,
                        'genre'               => $record['sexo'] ?: null,
                        'email'               => $record['correo'] ?: null,
                        'phone'               => $phoneData['phone_number'],
                        'phone_code'          => $phoneData['phone_code'],
                        'country_iso'         => $record['nacion'] ?: null,
                        'city_iso'            => $record['ciunac'] ?: null,
                        'localizador'         => trim($record['nropax']),
                        'updated_at'          => Carbon::now(),
                        'created_at'          => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );

                $this->command->info("Pasajero {$fullName} cargado/actualizado en reservation_id={$reservationId}");

            }

            fclose($handle);
        }
    }

    private function fixPhoneNumber($rawPhone)
    {
        if (empty($rawPhone)) {
            return [
                'phone_code'   => null,
                'phone_number' => null,
                'full'         => null,
            ];
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $cleanPhone = preg_replace('/[^\d]/', '', $rawPhone);

        for ($i = 0; $i < strlen($cleanPhone) - 6; $i++) {
            $possiblePhone = substr($cleanPhone, $i);

            try {
                $parsed = $phoneUtil->parse('+' . $possiblePhone, 'ZZ');

                if ($phoneUtil->isValidNumber($parsed)) {
                    return [
                        'phone_code'   => $parsed->getCountryCode(),
                        'phone_number' => (string) $parsed->getNationalNumber(),
                        'full'         => $parsed->getCountryCode() . $parsed->getNationalNumber(),
                    ];
                }
            } catch (NumberParseException $e) {
                // seguir probando
            }
        }

        return [
            'phone_code'   => null,
            'phone_number' => null,
            'full'         => null,
        ];
    }
}
