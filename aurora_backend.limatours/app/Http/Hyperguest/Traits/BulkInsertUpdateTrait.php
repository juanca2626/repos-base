<?php

namespace App\Http\Hyperguest\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait BulkInsertUpdateTrait
{
    public function bulkInsertRaw(string $table, array $columns, array $rows, int $chunkSize = 500)
    {
        if (empty($rows))
            return;

        $rows = array_values($rows);

        $now = Carbon::now()->toDateTimeString();
        $columnsWithTimestamps = array_merge($columns, ['created_at', 'updated_at']);

        // Dividir las filas en chunks para evitar exceder los límites de la base de datos
        $chunks = array_chunk($rows, $chunkSize);

        foreach ($chunks as $chunk) {
            $placeholders = [];
            $bindings = [];

            foreach ($chunk as $row) {
                $placeholders[] = '(' . implode(', ', array_fill(0, count($columnsWithTimestamps), '?')) . ')';

                foreach ($columns as $col) {
                    $bindings[] = $row[$col] ?? null;
                }

                $bindings[] = $now; // created_at
                $bindings[] = $now; // updated_at
            }

            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $table,
                implode(', ', $columnsWithTimestamps),
                implode(', ', $placeholders)
            );

            DB::statement($sql, $bindings);
        }
    }

    public function bulkUpdateCase(string $table, array $rows, array $fields, int $chunkSize = 500)
    {
        if (empty($rows))
            return;

        $rows = array_values($rows);
        $chunks = array_chunk($rows, $chunkSize);

        foreach ($chunks as $chunk) {
            $ids = [];
            $cases = [];

            foreach ($fields as $f) {
                $cases[$f] = [];
            }

            foreach ($chunk as $row) {
                if (!isset($row['id'])) {
                    continue;
                }

                $id = (int)$row['id'];
                $ids[] = $id;

                foreach ($fields as $f) {
                    if (array_key_exists($f, $row)) {
                        $value = is_numeric($row[$f])
                            ? $row[$f]
                            : DB::getPdo()->quote($row[$f]);

                        $cases[$f][] = "WHEN {$id} THEN {$value}";
                    }
                }
            }

            $now = Carbon::now()->toDateTimeString();

            $setParts = [];
            foreach ($fields as $f) {
                if (!empty($cases[$f])) {
                    $setParts[] = "{$f} = CASE id " . implode(' ', $cases[$f]) . " END";
                }
            }

            // forzar updated_at
            $setParts[] = "updated_at = " . DB::getPdo()->quote($now);

            if (!empty($setParts) && !empty($ids)) {
                $sql = sprintf(
                    'UPDATE %s SET %s WHERE id IN (%s)',
                    $table,
                    implode(', ', $setParts),
                    implode(', ', array_unique($ids))
                );

                DB::statement($sql);
            }
        }
    }
}
