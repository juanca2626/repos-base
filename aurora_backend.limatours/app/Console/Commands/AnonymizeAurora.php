<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnonymizeAurora extends Command
{
    protected $signature = 'db:anonymize-aurora
        {--only= : Comma list de tablas a procesar}
        {--limit= : Limitar filas por tabla (debug)}
        {--dry : Simulación (no escribe)}
        {--force : Requerido para ejecutar en modo real}';

    protected $description = 'Anonimiza datos sensibles en MySQL/Aurora (solo entornos no productivos).';

    protected $faker;
    protected $salt;

    public function __construct()
    {
        parent::__construct();
        $this->faker = \Faker\Factory::create('es_PE');
        $this->salt  = config('app.key'); // clave para HMAC
    }

    public function handle()
    {
        // Salvaguardas
        if (App::environment('production')) {
            $this->error('❌ Prohibido ejecutarse en production.');
            return 1;
        }
        $dry = (bool)$this->option('dry');
        if (!$dry && !$this->option('force')) {
            $this->error('Agrega --force para ejecutar en modo real (o usa --dry para simulación).');
            return 1;
        }

        $map = config('anonymize', []);
        if (empty($map)) {
            $this->warn('No hay reglas en config/anonymize.php');
            return 0;
        }

        $only  = collect(explode(',', (string)$this->option('only')))->filter()->map('trim')->all();
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;

        foreach ($map as $table => $rules) {
            if ($only && !in_array($table, $only)) continue;

            $this->line(str_repeat('─', 70));
            $this->info("Tabla: {$table}");
            $this->comment('Columnas: '.implode(', ', array_keys($rules)));

            // Separa reglas copy_from (JOIN set-based) del resto (per-row)
            $copyFromRules = [];
            $perRowRules   = [];
            foreach ($rules as $col => $strategy) {
                if (($strategy['type'] ?? null) === 'copy_from') {
                    $copyFromRules[$col] = $strategy;
                } else {
                    $perRowRules[$col] = $strategy;
                }
            }

            // 1) Ejecutar primero las sincronizaciones via JOIN (copy_from)
            if (!empty($copyFromRules)) {
                foreach ($copyFromRules as $col => $s) {
                    $this->applyJoinCopy($table, $col, $s, $dry);
                }
            }

            // 2) Luego aplicar anonimización per-row para el resto
            if (!empty($perRowRules)) {
                DB::transaction(function () use ($table, $perRowRules, $dry, $limit) {
                    $q = DB::table($table)
                        ->select(array_unique(array_merge(['id'], array_keys($perRowRules))))
                        ->orderBy('id');
                    if ($limit) $q->limit($limit);

                    $total = 0;
                    $updated = 0;

                    $q->chunkById(1000, function ($rows) use ($table, $perRowRules, $dry, &$total, &$updated) {
                        $updates = [];
                        foreach ($rows as $r) {
                            $total++;
                            $row = (array)$r;
                            $payload = [];
                            foreach ($perRowRules as $col => $strategy) {
                                $payload[$col] = $this->applyStrategy($strategy, $row[$col] ?? null, $row);
                            }
                            if ($payload) {
                                $updates[] = ['id' => $row['id']] + $payload;
                            }
                        }
                        if (!$dry && $updates) {
                            // Nota: upsert no existe nativo en Laravel 5.8; si no tienes polyfill, reemplaza por updates por fila.
                            $affected = $this->upsertCompat($table, $updates, 'id');
                            $updated += $affected;
                        }
                    });

                    $this->info(($dry ? 'Simulado ' : 'Actualizado ')."{$updated}/{$total} filas en {$table}");
                });
            }
        }

        // Verificaciones rápidas
        $this->line(str_repeat('─', 70));
        $this->info('Checks de verificación (conteos sospechosos):');
        $checks = $this->verificationQueries();
        $hasIssues = false;
        foreach ($checks as $label => $sql) {
            try {
                $count = DB::selectOne($sql)->c;
                $msg = ($count > 0) ? "❌ {$label}: {$count}" : "✅ {$label}: 0";
                $this->line($msg);
                if ($count > 0) $hasIssues = true;
            } catch (\Throwable $e) {
                $this->warn("No se pudo ejecutar check: {$label} ({$e->getMessage()})");
            }
        }

        if ($hasIssues) {
            $this->error('Se detectaron hallazgos en los checks. Revisa antes de habilitar el entorno.');
            return 2;
        }

        $this->info('✔ Proceso finalizado sin hallazgos.');
        return 0;
    }

    protected function applyStrategy(array $strategy, $value, array $row)
    {
        $type = $strategy['type'] ?? null;
        switch ($type) {
            case 'hash':
                // Determinístico e irreversible (HMAC-SHA256)
                return hash_hmac('sha256', (string)$value, $this->salt);

            case 'token':
                return (string) Str::uuid();

            case 'mask':
                // Enmascara conservando últimos N
                $keep = $strategy['keep'] ?? 4;
                $digits = preg_replace('/\D+/', '', (string)$value);
                return str_repeat('X', max(strlen($digits) - $keep, 0)) . substr($digits, -$keep);

            case 'email':
                $domain = $strategy['domain'] ?? 'dev.local';
                $id     = $row['id'] ?? Str::random(6);
                $local  = $this->slugUser($this->faker->userName) . '.' . $id;
                return "{$local}@{$domain}";

            case 'phone':
                // Perú: 9 dígitos iniciando en 9 (sintético)
                return '9' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

            case 'name':
                return $this->faker->name;

            case 'first_name':
                return $this->faker->firstName;

            case 'last_name':
                return $this->faker->lastName;

            case 'company':
                return $this->faker->company;

            case 'address':
                return $this->faker->streetAddress;

            case 'nullable':
                return null;

            case 'static':
                return $strategy['value'] ?? null;

            case 'json_emails':
                // Espera un JSON con claves como to/cc/bcc/reply_to => array de emails
                $domain = $strategy['domain'] ?? 'dev.local';
                $keys   = $strategy['keys'] ?? ['to','cc','bcc','reply_to'];

                // Decodifica de forma segura
                $data = null;
                if (is_string($value) && strlen($value)) {
                    $data = json_decode($value, true);
                } elseif (is_array($value)) {
                    $data = $value;
                }
                if (!is_array($data)) {
                    // si está vacío o malformado, devuelve un JSON válido vacío con las keys
                    $data = [];
                }

                // Genera emails sintéticos determinísticos por fila
                $baseId = $row['id'] ?? \Illuminate\Support\Str::random(6);

                foreach ($keys as $k) {
                    if (!array_key_exists($k, $data) || !is_array($data[$k])) {
                        // normaliza a array vacío
                        $data[$k] = [];
                    }
                    $out = [];
                    $i = 0;
                    foreach ($data[$k] as $unused) {
                        // Genera un correo único y estable por elemento
                        $local = $this->slugUser($this->faker->userName) . '.' . $baseId . '.' . $i;
                        $out[] = "{$local}@{$domain}";
                        $i++;
                    }
                    $data[$k] = $out;
                }

                // Limpia claves extrañas que no quieres conservar PII en otras keys
                // (opcional) ejemplo: forzar a mantener solo keys declaradas
                $clean = [];
                foreach ($keys as $k) {
                    $clean[$k] = $data[$k] ?? [];
                }

                return json_encode($clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            case 'username':
                return $this->faker->userName;
            default:
                return $value;
        }
    }

    protected function applyJoinCopy(string $table, string $targetCol, array $s, bool $dry): void
    {
        // Espera: table, local_key, foreign_key, column
        $srcTable   = $s['table']       ?? null;
        $localKey   = $s['local_key']   ?? null;
        $foreignKey = $s['foreign_key'] ?? null;
        $srcColumn  = $s['column']      ?? null;

        if (!$srcTable || !$localKey || !$foreignKey || !$srcColumn) {
            $this->warn("Regla copy_from incompleta para {$table}.{$targetCol}");
            return;
        }

        $aliasT = 't';
        $aliasS = 's';

        $sql = sprintf(
            "UPDATE `%s` %s JOIN `%s` %s ON %s.`%s` = %s.`%s` SET %s.`%s` = %s.`%s`",
            $table, $aliasT,
            $srcTable, $aliasS,
            $aliasS, $foreignKey,
            $aliasT, $localKey,
            $aliasT, $targetCol,
            $aliasS, $srcColumn
        );

        if ($dry) {
            $this->comment("[DRY] JOIN COPY: {$table}.{$targetCol} <= {$srcTable}.{$srcColumn} (ON {$table}.{$localKey} = {$srcTable}.{$foreignKey})");
            return;
        }

        try {
            $affected = DB::affectingStatement($sql);
            $this->info("JOIN COPY aplicado en {$table}.{$targetCol} (filas afectadas: {$affected})");
        } catch (\Throwable $e) {
            $this->error("Error en JOIN COPY {$table}.{$targetCol}: ".$e->getMessage());
        }
    }

    /**
     * Compatibilidad para Laravel 5.8 (sin upsert nativo).
     * Usa updateOrInsert fila por fila basado en una PK/UK (por defecto 'id').
     * Retorna cantidad de filas afectadas (aprox. igual a count($rows)).
     */
    protected function upsertCompat(string $table, array $rows, string $uniqueKey = 'id'): int
    {
        $affected = 0;

        foreach ($rows as $row) {
            if (!array_key_exists($uniqueKey, $row)) {
                // si no hay clave única, no se puede hacer upsert; saltamos
                continue;
            }
            $attributes = [$uniqueKey => $row[$uniqueKey]];

            // valores a actualizar (todo menos la clave)
            $values = $row;
            unset($values[$uniqueKey]);

            // updateOrInsert devuelve bool; no dice cuántas filas, contamos nosotros
            DB::table($table)->updateOrInsert($attributes, $values);
            $affected++;
        }
        return $affected;
    }


    protected function slugUser($txt)
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $txt));
    }

    protected function verificationQueries(): array
    {
        // Devuelve conteos; si es >0, hay sospechas.
        return [
            'Emails públicos en clients' => "
                SELECT COUNT(*) AS c FROM clients
                WHERE email REGEXP '@(gmail|hotmail|yahoo)\\.com$';
            ",

            'Documentos visibles en client_representatives' => "
                SELECT COUNT(*) AS c FROM client_representatives
                WHERE document REGEXP '^[0-9]{8,11}$';
            ",

            // Ajusta el nombre si tu tabla real difiere
            'Teléfonos reales en reservation_passengers' => "
                SELECT COUNT(*) AS c FROM reservation_passengers
                WHERE phone REGEXP '^[9][0-9]{8}$'
                  AND phone NOT LIKE '9%%%%%%%%';
            ",

            'Emails fuera de dominio controlado en users' => "
                SELECT COUNT(*) AS c FROM users
                WHERE email NOT LIKE '%@dev.aurora';
            ",

            // EXECUTIVE_NAME alineado con users.name
            'Desalineación executive_name en reservations_services' => "
                SELECT COUNT(*) AS c
                FROM reservations_services t
                JOIN users u ON u.id = t.executive_id
                WHERE COALESCE(t.executive_name,'') <> COALESCE(u.name,'')
            ",
            'Desalineación executive_name en reservations_hotels' => "
                SELECT COUNT(*) AS c
                FROM reservations_hotels t
                JOIN users u ON u.id = t.executive_id
                WHERE COALESCE(t.executive_name,'') <> COALESCE(u.name,'')
            ",
            'Desalineación executive_name en reservations_hotels_rates_plans_rooms' => "
                SELECT COUNT(*) AS c
                FROM reservations_hotels_rates_plans_rooms t
                JOIN users u ON u.id = t.executive_id
                WHERE COALESCE(t.executive_name,'') <> COALESCE(u.name,'')
            ",

            // HOTEL_NAME alineado con hotels.name
            'Desalineación hotel_name en reservations_hotels' => "
                SELECT COUNT(*) AS c
                FROM reservations_hotels t
                JOIN hotels h ON h.id = t.hotel_id
                WHERE COALESCE(t.hotel_name,'') <> COALESCE(h.name,'')
            ",
            'Desalineación hotel_name en reservations_hotels_rates_plans_rooms' => "
                SELECT COUNT(*) AS c
                FROM reservations_hotels_rates_plans_rooms t
                JOIN hotels h ON h.id = t.hotel_id
                WHERE COALESCE(t.hotel_name,'') <> COALESCE(h.name,'')
            ",

            // Correos con dominios públicos dentro del JSON (to/cc/bcc/reply_to)
            'Emails públicos en JSON (reservations_emails_logs.emails)' => "
                SELECT COUNT(*) AS c
                FROM reservations_emails_logs
                WHERE JSON_TYPE(emails) = 'OBJECT' AND (
                    (JSON_EXTRACT(emails, '$.to')       IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.to'))       REGEXP '@(gmail|hotmail|yahoo)\\.com') OR
                    (JSON_EXTRACT(emails, '$.cc')       IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.cc'))       REGEXP '@(gmail|hotmail|yahoo)\\.com') OR
                    (JSON_EXTRACT(emails, '$.bcc')      IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.bcc'))      REGEXP '@(gmail|hotmail|yahoo)\\.com') OR
                    (JSON_EXTRACT(emails, '$.reply_to') IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.reply_to')) REGEXP '@(gmail|hotmail|yahoo)\\.com')
                )
            ",

            // Correos JSON fuera del dominio controlado (si hay valores y NO contienen @dev.reservations)
            'Emails fuera de dominio controlado en JSON (reservations_emails_logs.emails)' => "
                SELECT COUNT(*) AS c
                FROM reservations_emails_logs
                WHERE JSON_TYPE(emails) = 'OBJECT' AND (
                    (JSON_LENGTH(JSON_EXTRACT(emails, '$.to'))       > 0 AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.to'))       NOT REGEXP '@dev\\.reservations') OR
                    (JSON_LENGTH(JSON_EXTRACT(emails, '$.cc'))       > 0 AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.cc'))       NOT REGEXP '@dev\\.reservations') OR
                    (JSON_LENGTH(JSON_EXTRACT(emails, '$.bcc'))      > 0 AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.bcc'))      NOT REGEXP '@dev\\.reservations') OR
                    (JSON_LENGTH(JSON_EXTRACT(emails, '$.reply_to')) > 0 AND JSON_UNQUOTE(JSON_EXTRACT(emails, '$.reply_to')) NOT REGEXP '@dev\\.reservations')
                )
            ",
        ];
    }
}
