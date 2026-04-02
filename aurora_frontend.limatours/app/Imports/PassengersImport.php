<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PassengersImport implements ToCollection
{
    public $total;

    public function __construct($total)
    {
        $this->total = $total;
    }

    public function transformDate($value, $format = 'Y-m-d')
    {
        try
        {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        }
        catch (\ErrorException $e)
        {
            return '';
        }
    }

    public function collection(Collection $rows)
    {
        try
        {
            $this->wsdl = 'http://genero.limatours.com.pe:8203/WS_RegistroPasajero?wsdl';
            $this->client = new \SoapClient($this->wsdl, [
                'encoding' => 'UTF-8',
                'trace' => true
            ]);

            session()->put('data_import', '');

            $passengers = (array) session()->get('passengers'); $nroref = (int) session()->get('nrofile');
            $response = [];

            foreach ($rows as $i => $params)
            {
                if($i > 0 AND $i <= $this->total)
                {
                    $type = (isset($passengers[$i])) ? 'edit' : 'save';
                    $nrosec = (isset($passengers[$i])) ? $passengers[$i]['nrosec'] : 0;

                    $types = ['save' => 'OK', 'edit' => 'OK', 'delete' => 'OK'];
                    $fill = array(0 => 'nombres', 1 => 'apellidos', 9 => 'observ');

                    foreach($fill as $key => $value)
                    {
                        if(@$params[$key] == '')
                        {
                            $params[$key] = 'PENDIENTE';
                        }
                    }

                    $format = ['ADULTO' => 'ADL', 'NIÑO' => 'CHD', 'INFANTE' => 'INF'];

                    $fecha_hora = (array) $this->transformDate($params[4]);
                    $fecha = date("d/m/Y", strtotime($fecha_hora['date']));

                    $array = [
                        'secuencia' => $nrosec,
                        'nrofile' => $nroref,
                        'nombre' => $params[1] . ', ' . $params[0],
                        'tipo' => (isset($format[$params[2]])) ? $format[$params[2]] : $params[2],
                        'sexo' => $params[3],
                        'fecha' => $fecha,
                        'ciudad' => '',
                        'nacion' => '',
                        'tipodoc' => $params[5],
                        'nrodoc' => (string) $params[6],
                        'estado' => $types[$type],
                        'correo_electronico' => $params[7],
                        'telefono' => $params[8],
                        'observ' => $params[9]
                    ];

                    $array = implode("|", $array);
                    $data = array('nroref' => $nroref, 'datos' => ($array . '|'));
                    $response[] = $this->client->__call("registropasajero", $data);
                }
            }

            session()->put('data_import', $response);
        }
        catch(\Exception $ex)
        {
            session()->put('data_import', []);
        }
    }
}
