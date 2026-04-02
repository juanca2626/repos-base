<?php

namespace App\Http\Pentagrama\Traits;

use App\ChannelHotel;
use App\Service;
use Illuminate\Support\Arr;

trait PentagramaTrait
{
    public function sanitizeServiceMozart(array $data): array
    {
        return [
            'passenger' => Arr::get($data, 'passenger', ''),
            'header' => Arr::get($data, 'header', []),
        ];
    }

    public function sanitizeServiceDetailMozartBck(array $data): array
    {
        $items = [];

        $body = Arr::get($data, 'body', []);

        foreach ($body as $value) {
            $originalExternalServiceId = Arr::get($value, 'codoperador');

            if (empty($originalExternalServiceId)) {
                continue;
            }

            // Detectar tipo de servicio
            $detectedType = $this->detectTypeService(Arr::get($value, 'servicio', ''), Arr::get($value, 'tipo', ''));

            // Resolver el external_service_id correcto basado en la descripción
            $externalServiceId = $this->resolveExternalServiceId($originalExternalServiceId, Arr::get($value, 'servicio'));

            $item = [
                'city' => Arr::get($value, 'ciudad'),
                'single_date' => $this->parseDate(Arr::get($value, 'dia')) ?? '',
                'single_hour' => Arr::get($value, 'hora') ?? '',
//                'type_service' => $detectedType,
                'original_service_id' => $originalExternalServiceId, // TRNE94+MPI515
                'external_service_id' => $externalServiceId, // MPI515
                'external_service_description' => Arr::get($value, 'servicio'),
                'status_service' => 0, // RQ=0 OK=1
                'status_selected' => true,
            ];

            if ($detectedType === 'HOTEL') {
                $item['type_service'] = 'hotel';
                $item['status_service'] = $this->isHotelMapped($externalServiceId);
            } else {
                $item['type_service'] = 'service';
                $item['status_service'] = $this->isServiceMapped($externalServiceId);
            }

            $items[] = $item;
        }

        return $items;
    }

    public function sanitizeServiceDetailMozart(array $data): array
    {
        $items = [];

        $body = Arr::get($data, 'body', []);

        foreach ($body as $value) {
            $originalExternalServiceId = Arr::get($value, 'codoperador');

            if (empty($originalExternalServiceId)) {
                continue;
            }

            // Primero, intentamos verificar si es un hotel
            if ($this->isHotelMapped($originalExternalServiceId)) {
                $item = $this->buildItem($value, 'hotel');
                $item['status_service'] = 1; // RQ=0 OK=1
            } // Si no es hotel, intentamos verificar si es un servicio
            elseif ($this->isServiceMapped($originalExternalServiceId)) {
                $item = $this->buildItem($value, 'service');
                $item['status_service'] = 1; // RQ=0 OK=1
            } else {
                // Si no es ni hotel ni servicio, asignamos 'type_service' a null
                $item = $this->buildItem($value, 'other');
                $item['status_service'] = 0; // RQ=0, no asignamos 'status_service' a 1 si no es un hotel ni un servicio
            }

            $items[] = $item;
        }

        return $items;
    }

    protected function buildItem(array $value, ?string $type): array
    {
        $originalExternalServiceId = Arr::get($value, 'codoperador');
        $externalServiceId = $this->resolveExternalServiceId($originalExternalServiceId, Arr::get($value, 'servicio'));

        return [
            'city' => Arr::get($value, 'ciudad'),
            'single_date' => $this->parseDate(Arr::get($value, 'dia')) ?? '',
            'single_hour' => Arr::get($value, 'hora') ?? '',
            'original_service_id' => $originalExternalServiceId, // TRNE94+MPI515
            'external_service_id' => $externalServiceId, // MPI515
            'external_service_description' => Arr::get($value, 'servicio'),
            'status_service' => 0, // RQ=0 OK=1
            'status_selected' => true,
            'type_service' => $type // Puede ser 'hotel', 'service', o 'other
        ];
    }

    /**
     * Check if an external code corresponds to a mapped hotel (ChannelHotel -> Hotel)
     */
    public function isHotelMapped(string $code): bool
    {
        // use inner join to ensure hotel exists for channel mapping
        $id = ChannelHotel::where('channel_hotel.code', $code)
            ->join('hotels', 'channel_hotel.hotel_id', '=', 'hotels.id')
            ->pluck('hotels.id')
            ->first();

        return (bool)$id;
    }

    /**
     * Check if an external code corresponds to a mapped general service
     */
    public function isServiceMapped(?string $code): bool
    {
        if (empty($code)) {
            return false;
        }

        $id = Service::where('aurora_code', $code)->pluck('id')->first();
        return (bool)$id;
    }

    protected function detectTypeService(?string $descripcion, ?string $tipo): string
    {
        if (empty($descripcion)) {
            return 'other';
        }

        $lower = strtolower($descripcion . ' ' . ($tipo ?? ''));

        if (stripos($lower, 'trf') !== false || stripos($lower, 'tren') !== false) {
            return 'train';
        }

        if (stripos($lower, 'hotel') !== false || stripos($lower, 'superior') !== false) {
            return 'hotel';
        }

        if (stripos($lower, 'btc') !== false) {
            return 'transfer';
        }

        if (stripos($lower, 'h/d') !== false || stripos($lower, 'f/d') !== false || stripos($lower, 'tour') !== false) {
            return 'tour';
        }

        return 'other';
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Accept common formats. Try d-m-Y first (e.g. 19-12-2025), then ISO.
        try {
            // If already a DateTime/Carbon instance, format it
            if ($value instanceof \DateTime) {
                return \Carbon\Carbon::instance($value)->toDateString();
            }

            $trim = trim((string)$value);

            // Try d-m-Y
            $d = \Carbon\Carbon::createFromFormat('d-m-Y', $trim);
            if ($d) {
                return $d->toDateString();
            }
        } catch (\Exception $e) {
            // ignore and try other formats
        }

        try {
            $d2 = \Carbon\Carbon::parse($value);
            return $d2->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCrossed(array $data): array
    {
        $services = $this->sanitizeServiceMozart($data);
        $detail_Services = $this->sanitizeServiceDetailMozart($data);

        $dataCrossed = [
            'service' => $services,
            'service_details' => $detail_Services,
        ];

        return $dataCrossed;
    }

    /**
     * Obtiene el código externo válido comparando contra una descripción.
     *
     * @param string $externalCode Código compuesto (ej: TRNE94+MPI515)
     * @param string $description Texto donde buscar coincidencias
     * @param string $separator Separador de códigos (default '+')
     * @return string|null
     */
    function resolveExternalServiceId(
        string $externalCode,
        string $description,
        string $separator = '+'
    ): ?string
    {
        $codes = array_filter(
            array_map('trim', explode($separator, $externalCode))
        );

        foreach ($codes as $code) {
            // búsqueda segura, case-insensitive
            if (stripos($description, $code) !== false) {
                return $code;
            }
        }

        // fallback: si no hay match, retorna el último código
        return end($codes) ?: null;
    }
}
