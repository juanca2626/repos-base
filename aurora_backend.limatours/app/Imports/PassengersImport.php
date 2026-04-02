<?php

namespace App\Imports;

use App\File;
use App\Http\Stella\StellaService;
use App\ReservationPassenger;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PassengersImport implements ToCollection
{
    public $total, $nrofile, $stellaService;

    public function __construct($nrofile, $total)
    {
        $this->total = $total;
        $this->nrofile = $nrofile;
        $this->stellaService = new StellaService;
    }

    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        } catch (\ErrorException $e) {
            return '';
        }
    }

    public function collection(Collection $rows)
    {
        $fill = array('nombres', 'apellidos', 'observ');
        $items = [];

        session()->put('data_import', '');
        $response = [];

        $reservation = File::where('file_number', $this->nrofile)->first();

        foreach ($rows as $i => $params) {
            if ($i > 0 and $i <= $this->total) {
                foreach ($fill as $key => $value) {
                    if (@$params[$value] == '') {
                        $params[$value] = 'PENDIENTE';
                    }
                }

                $items[] = [
                    'secuencia' => $params['nrosec'] ?? $i,
                    'nrofile' => $this->nrofile,
                    'nombre' => $params['apellidos'] . ', ' . $params['nombres'],
                    'tipo' => $params['tipo'],
                    'sexo' => $params['sexo'],
                    'fecha' => $params['fecnac'],
                    'ciudad' => '',
                    'pais' => $params['nacion'],
                    'tipodoc' => $params['tipdoc'],
                    'nrodoc' => $params['nrodoc'],
                    'correo_electronico' => $params['correo'],
                    'telefono' => $params['celula'],
                    'resmed' => $params['resmed'],
                    'resali' => $params['resali'],
                    'observ' => $params['observ']
                ];

                if ($reservation) {
                    $reservation_id = $reservation->reservation_id;
                    if (!isset($params['id'])) {

                        $find_ = ReservationPassenger::where('reservation_id', $reservation_id)
                            ->where('sequence_number', $params['nrosec'])
                            ->first();
                        if ($find_) {
                            $pax_ =  $find_;
                        } else {
                            $pax_ = new ReservationPassenger();
                            $pax_->reservation_id = $reservation_id;
                            $pax_->sequence_number = $params['nrosec'];
                        }
                    } else {
                        $pax_ = ReservationPassenger::find($params['id']);
                    }

                    $pax_->name = $params['nombres'];
                    $pax_->surnames = $params['apellidos'];
                    $pax_->type = $params['tipo'];
                    $pax_->genre = $params['sexo'];
                    $pax_->date_birth = $params['fecnac'];
                    $pax_->country_iso = $params['nacion'];
                    $pax_->doctype_iso = $params['tipdoc'];
                    $pax_->document_number = $params['nrodoc'];
                    $pax_->email = $params['correo'];
                    $pax_->phone = $params['celula'];
                    $pax_->dietary_restrictions = $params['resali'];
                    $pax_->medical_restrictions = $params['resmed'];
                    $pax_->notes = $params['observ'];
                    //                        $pax_->suggested_room_type = $params['tiphab'];
                    $pax_->save();
                }
            }
        }

        $data = array('data' => $items);
        $response = (array) $this->stellaService->register_paxs($this->nrofile, $data);
        session()->put('data_import', $response);
    }
}
