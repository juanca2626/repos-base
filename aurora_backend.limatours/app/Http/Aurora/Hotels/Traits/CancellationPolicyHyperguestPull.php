<?php

namespace App\Http\Aurora\Hotels\Traits;

use Carbon\Carbon;

trait CancellationPolicyHyperguestPull
{
    public function generarPoliticasCancelacion(
        float $totalReserva,
        $checkIn,
        $reservationDate,
        array $cancellationPolicies
    ): array {
        $checkIn = $checkIn instanceof Carbon ? $checkIn->copy() : Carbon::parse($checkIn);
        $resDate = $reservationDate instanceof Carbon ? $reservationDate->copy() : Carbon::parse($reservationDate);

        // Normalizar policies
        $normalized = [];
        foreach ($cancellationPolicies as $p) {
            if (isset($p['daysBefore'])) {
                $normalized[] = [
                    'timeSetting' => [
                        'timeFromCheckIn' => intval($p['daysBefore']),
                        'timeFromCheckInType' => 'days'
                    ],
                    'cancellationDeadlineHour' => '00:00',
                    'penaltyType' => $p['penaltyType'],
                    'amount' => $p['amount']
                ];
            } else {
                $normalized[] = $p;
            }
        }

        $positive = [];
        $zeroPolicy = null;
        foreach ($normalized as $p) {
            $t = intval($p['timeSetting']['timeFromCheckIn'] ?? 0);
            if ($t === 0) {
                $zeroPolicy = $p;
            } else {
                $positive[] = $p;
            }
        }

        usort($positive, function ($a, $b) {
            return intval($b['timeSetting']['timeFromCheckIn']) <=> intval($a['timeSetting']['timeFromCheckIn']);
        });

        $boundaries = [];
        foreach ($positive as $p) {
            $value = intval($p['timeSetting']['timeFromCheckIn']);
            $type = $p['timeSetting']['timeFromCheckInType'] ?? 'days';

            $b = $checkIn->copy();
            if ($type === 'days') {
                $b->subDays($value);
            } else {
                $b->subHours($value);
            }

            if (!empty($p['cancellationDeadlineHour'])) {
                $b->setTimeFromTimeString($p['cancellationDeadlineHour']);
            } else {
                $b->setTime(0, 0, 0);
            }

            $boundaries[] = $b->copy();
        }

        if ($zeroPolicy !== null) {
            $startOfDay = $checkIn->copy()->startOfDay();
            $exists = false;
            foreach ($boundaries as $b) {
                if ($b->eq($startOfDay)) { $exists = true; break; }
            }
            if (!$exists) $boundaries[] = $startOfDay;
        }

        // ✅ aquí cambiamos fn por function
        usort($boundaries, function($a, $b) {
            if ($a->lt($b)) {
                return -1;
            } elseif ($a->eq($b)) {
                return 0;
            } else {
                return 1;
            }
        });

        $unique = [];
        foreach ($boundaries as $b) {
            $unique[$b->toDateTimeString()] = $b;
        }
        $boundaries = array_values($unique);

        $points = array_merge([$resDate->copy()], $boundaries, [$checkIn->copy()]);

        $intervals = [];
        $countPositive = count($positive);
        $nPoints = count($points);

        for ($i = 0; $i < $nPoints - 1; $i++) {
            $start = $points[$i]->copy();
            $end = $points[$i+1]->copy();
            if ($end->lte($start)) continue;

            $penaltyValue = 0;
            $penaltyType = 'percent';
            if ($i == 0) {
                $penaltyValue = 0;
            } elseif ($i <= $countPositive) {
                $p = $positive[$i-1];
                $penaltyValue = $p['amount'] ?? 0;
                $penaltyType = $p['penaltyType'] ?? 'percent';
            } else {
                if ($zeroPolicy !== null) {
                    $penaltyValue = $zeroPolicy['amount'] ?? 0;
                    $penaltyType = $zeroPolicy['penaltyType'] ?? 'percent';
                } else {
                    $penaltyValue = 100;
                    $penaltyType = 'percent';
                }
            }

            if ($penaltyType === 'percent') {
                $usd = round($totalReserva * floatval($penaltyValue) / 100, 2);
            } elseif ($penaltyType === 'currency') {
                $usd = round(floatval($penaltyValue), 2);
            } elseif ($penaltyType === 'nights') {
                $usd = round(floatval($penaltyValue), 2);
            } else {
                $usd = round($totalReserva * floatval($penaltyValue) / 100, 2);
            }

            $intervals[] = [
                'desde' => $start->toDateTimeString(),
                'hasta' => $end->toDateTimeString(),
                'penalizacion_usd' => $usd,
                'penalizacion_tipo' => $penaltyType,
                'penalizacion_valor' => $penaltyValue,
            ];
        }

        if ($resDate->greaterThanOrEqualTo($checkIn->copy()->startOfDay())) {
            $last = end($intervals);
            return $last ? [$last] : [];
        }

        $filtered = [];
        foreach ($intervals as $int) {
            $intHasta = Carbon::parse($int['hasta']);
            if ($intHasta->gte($resDate)) {
                if (empty($filtered)) {
                    $from = Carbon::parse($int['desde']);
                    if ($from->lt($resDate)) {
                        $int['desde'] = $resDate->toDateTimeString();
                    }
                }
                $filtered[] = $int;
            }
        }

        return array_values($filtered);
    }
}
