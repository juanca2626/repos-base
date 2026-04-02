<?php

namespace App\Exports;

use App\User;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Str;

class UsersByRoleExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
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
        return Str::limit($this->roleName ?: 'N/A', 31, '');
    }

    public function collection()
    {
        $role = Role::find($this->roleId);
        $roleName = $role ? $role->name : 'N/A';

        // 👉 Eager load la relación userType
        $users = User::with('userType')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', $this->roleId);
            })
            ->get(['id', 'name', 'email', 'code', 'user_type_id']);

        // 👉 Mapear los datos incluyendo el tipo de usuario
        return $users->map(function ($user) use ($roleName) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'code'       => $user->code,
                'role'       => $roleName,
                'user_type'  => optional($user->userType)->description ?? '-', // descripción del tipo
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Name user', 'Email', 'Code user', 'Role', 'User Type'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Rango de encabezado
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $headRange  = "A1:{$lastColumn}1";

                // Filtro y freeze
                $sheet->setAutoFilter($headRange);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Estilos cabecera
                $event->sheet->getStyle($headRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBD0D12'],
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF1F2937'],
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF1F2937'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
