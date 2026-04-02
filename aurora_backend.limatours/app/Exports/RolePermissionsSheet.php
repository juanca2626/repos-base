<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
class RolePermissionsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    protected $roleId;
    protected $roleName;

    public function __construct(int $roleId, string $roleName)
    {
        $this->roleId   = $roleId;
        $this->roleName = $roleName;
    }

    public function title(): string
    {
        return Str::limit($this->roleName, 31, '');
    }

    public function headings(): array
    {
        return [
            'Role ID',
            'Role',
            'Module',
            'Permission ID',
            'Permission Slug',
            'Group',
            'Action',
            'Description',
        ];
    }

    public function collection()
    {
        // Trae permisos del rol (permission_role) y sus módulos (left joins)
        $rows = DB::table('permission_role as pr')
            ->join('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->leftJoin('permission_details as d', 'd.permission_id', '=', 'p.id')
            ->leftJoin('permission_modules as m', 'm.id', '=', 'd.permission_module_id')
            ->where('pr.role_id', $this->roleId)
            ->select([
                'pr.role_id',
                'p.id as permission_id',
                'p.slug as permission_slug',
                'p.description as permission_description',
                'm.name as module_name',
                'm.sort_order as module_sort',
            ])
            ->orderBy('m.sort_order')
            ->orderBy('m.name')
            ->orderBy('p.slug')
            ->get();

        if ($rows->isEmpty()) {
            // Devuelve al menos una fila “vacía” para que sea claro en la hoja
            return new Collection();
        }

        // Mapear a columnas finales
        $out = $rows->map(function ($r) {
            [$group, $action] = $this->splitSlug($r->permission_slug);

            return [
                (int) $r->role_id,
                $this->roleName,
                $r->module_name ?: '-',      // puede no tener detalle
                (int) $r->permission_id,
                $r->permission_slug,
                strtolower($group),
                strtolower($action),
                $r->permission_description ?: '',
            ];
        });

        return $out;
    }

    private function splitSlug(string $slug): array
    {
        if (strpos($slug, '.') === false) {
            return [$slug, $slug];
        }
        [$group, $rest] = explode('.', $slug, 2);
        $parts  = explode('.', $rest);
        $action = end($parts);
        return [$group, $action];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Rango de la cabecera dinámico (A1:H1, etc.)
                $highestColumn = $sheet->getHighestColumn();
                $headerRange   = "A1:{$highestColumn}1";

                // Filtro y freeze
                $sheet->setAutoFilter($headerRange);
                $sheet->freezePane('A2');

                // Alto de la fila de cabecera
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Estilos para la cabecera
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'], // blanco
                        'size'  => 11,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBD0D12'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => false,
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFFFFFFF'],
                        ],
                    ],
                ]);
            },
        ];
    }}
