<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MultiRolePermissionsExport;
use jeremykenedy\LaravelRoles\Models\Role;

class ExportRolesPermissions extends Command
{
    protected $signature = 'report:roles-permissions
                            {--only= : Coma-separado de slugs o IDs de roles a exportar (opcional)}
                            {--filename= : Nombre del archivo a generar (opcional)}';

    protected $description = 'Genera un Excel con una pestaña por rol listando sus permisos.';

    public function handle()
    {
        // 1) Roles base
        $rolesQuery = Role::query()->orderBy('name');

        // 2) Filtro opcional --only (IDs y/o slugs/nombres)
        if ($only = $this->option('only')) {
            $tokens = collect(explode(',', $only))
                ->map(function ($v) { return trim($v); })
                ->filter()
                ->values();

            // separa IDs numéricos y términos string
            $ids   = $tokens->filter(function ($t) { return ctype_digit($t); })
                ->map(function ($t) { return (int) $t; })
                ->values()
                ->all();

            $terms = $tokens->reject(function ($t) { return ctype_digit($t); })
                ->values()
                ->all();

            $rolesQuery->where(function ($q) use ($ids, $terms) {
                if (!empty($ids)) {
                    $q->whereIn('id', $ids);
                }
                if (!empty($terms)) {
                    // (slug OR name) IN terms, agrupado
                    $q->orWhereIn('slug', $terms)
                        ->orWhereIn('name', $terms);
                }
            });
        }

        $roles = $rolesQuery->get();
        if ($roles->isEmpty()) {
            $this->error('No se encontraron roles para exportar.');
            return 1;
        }

        // 3) Nombre + ruta pública
        $name     = $this->option('filename') ?: ('roles_permissions_' . now()->format('Ymd_His') . '.xlsx');
        $relative = 'exports/' . $name;   // storage/app/public/exports/...
        $disk     = 'public';

        // 4) Export
        Excel::store(new MultiRolePermissionsExport($roles), $relative, $disk);

        // 5) URL pública (requiere storage:link)
        $publicUrl = url(Storage::disk($disk)->url($relative));

        $this->info('✅ Reporte generado correctamente.');
        $this->line('Archivo: ' . $relative);
        $this->line('URL: ' . $publicUrl);

        return 0;
    }
}
