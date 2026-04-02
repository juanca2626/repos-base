<?php


namespace App\Http\Stella;

use App\User;
use App\Hotel;
use SoapClient;
use App\Service;
use Carbon\Carbon;
use SimpleXMLElement;
use App\Http\Traits\ChannelLogs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Reservation as reservationAurora;
use GuzzleHttp\Exception\ServerException;
use App\ReservationsHotelsRatesPlansRooms;

class Reservation
{
    use ChannelLogs;

    /** @var string */
    public $WS_NuevoFile = "http://genero.limatours.com.pe:8199/WS_NuevoFile?wsdl";
    public $WS_ModificaFile = "http://genero.limatours.com.pe:8200/WS_ModificaFile?wsdl";
    public $WS_CancelaFile = "http://genero.limatours.com.pe:8205/WS_CancelFile?wsdl";

    private $SOAP_OrderLITO = null;
    public $SOAP_NuevoFile = null;
    public $SOAP_ModificaFile = null;
    public $SOAP_CancelaFile = null;
    public $SOAP_UpdateFileFromAurora = null;

    /** @var SoapClient */
    public $_stellaClient = null;


    /**
     * @return SoapClient
     * @throws \SoapFault
     */
    public function Client_NuevoFile()
    {
        if (!$this->SOAP_NuevoFile) {
            $this->SOAP_NuevoFile = new \SoapClient($this->WS_NuevoFile, ['trace' => 1]);
        }

        return $this->SOAP_NuevoFile;
    }

    /**
     * @return SoapClient
     * @throws \SoapFault
     */
    public function Client_CancelaFile()
    {
        if (!$this->SOAP_CancelaFile) {
            $this->SOAP_CancelaFile = new \SoapClient($this->WS_CancelaFile, ['trace' => 1]);
        }

        return $this->SOAP_CancelaFile;
    }

    /**
     * @param $params
     * @return mixed
     * @throws \SoapFault
     */
    public function creandoFile($params)
    {
        return $this->Client_NuevoFile()->__soapCall('CreandoFile', $params);
    }


    public function createFileApiNode($params)
    {
        try {
            $stellaService = new \App\Http\Stella\StellaService();
            $response = $stellaService->createFileStella($params);
            return $response;
        } catch (ServerException $exception) {
            $error = $exception->getResponse()->getBody()->getContents();
            return json_decode($error);
        }

    }

    /**
     * @param $params
     * @return mixed
     * @throws \SoapFault
     */
    public function cancelaFile($params)
    {
        return $this->Client_CancelaFile()->__soapCall('CancelaFile', $params);
    }

    public function cancelFileApiNode($params)
    {
        try {
            $stellaService = new \App\Http\Stella\StellaService();
            return $stellaService->cancelFileStella($params);
        } catch (ServerException $exception) {
            $error = $exception->getResponse()->getBody()->getContents();
            return json_decode($error);
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws \SoapFault
     */
    public function updateNumberConfirmationFile($params)
    {
        return $this->Client_UpdateFileFromAurora()->__soapCall('syncAddNroConfirmationByRoom',
            array('parameters' => $params));
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateRelationShipFileOrder($params)
    {
        return $this->Client_OrderLITO()->__soapCall('doRelaciOrder',
            array('parameters' => $params));
    }

    /**
     * @param $params
     * @return string
     * @throws \SoapFault
     */
    public function getNuevoFileLastRequest()
    {
        return $this->Client_NuevoFile()->__getLastRequest();
    }

    /**
     * @param $params
     * @return string
     * @throws \SoapFault
     */
    public function getNuevoFileLastResponse()
    {
        return $this->Client_NuevoFile()->__getLastResponse();
    }

    /**
     * @param $params
     * @return string
     * @throws \SoapFault
     */
    public function getCancelaFileLastRequest()
    {
        return $this->Client_CancelaFile()->__getLastRequest();
    }

    /**
     * @param $params
     * @return string
     * @throws \SoapFault
     */
    public function getCancelaFileLastResponse()
    {
        return $this->Client_CancelaFile()->__getLastResponse();
    }

    /**
     * @param reservationAurora $reservation
     * @return array
     * @throws \SoapFault
     */
    public function cancel_soap(reservationAurora $reservation, $idDetalleSvs = '')
    {
        $file_code = $reservation->file_code;
        if (!is_numeric($reservation->file_code)) {
            $file_code = '000000';
        }

        $erros = [];
        $groupedByExecutives = $reservation->reservationsHotel->groupBy('executive_code');
        foreach ($groupedByExecutives as $executive_code => $reservationsHotel) {
            $reservas_hotel_cancelar = [];
            foreach ($reservationsHotel as $reservationHotel) {

                // Filtramos las reservas de la siguiente manera:
                // Si es una reserva de channel (channel_id != 1) debe estar cancelada en el channel (status_in_channel == 0)
                // Si es una reserva de estela (channel_id == 1) debe estar activa en estela (status == 1)
                $groupedByRoomType = $reservationHotel->reservationsHotelRooms->filter(function ($item, $key) {
                    //    return true;
                    return ($item->channel_id != 1 and $item->status_in_channel == 0) or
                        ($item->channel_id == 1 and $item->status == 1);
                })->groupBy('room_type_id');

                foreach ($groupedByRoomType as $reservationHotelRooms) {

                    $rooms_cancelar = [];
                    foreach ($reservationHotelRooms as $reservationHotelRoom) {
                        $igv = [
                            'percent' => 0,
                            'total_amount' => 0,
                        ];
                        $extra_fees = json_decode($reservationHotelRoom['taxes_and_services'], true);
                        if (isset($extra_fees['apply_fees']) and isset($extra_fees['apply_fees']['t'])) {
                            foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                                if ($extra_fee['name'] == 'IGV') {
                                    $igv['percent'] = $extra_fee['value'];
                                    $igv['total_amount'] = $extra_fee['total_amount'];
                                }
                            }
                        }

                        $rooms_cancelar[] = [
                            'codigoReserva' => $reservationHotelRoom->id,
                            'totalCosto' => $reservationHotelRoom->total_amount_base,
                            'totalIgv' => $igv['total_amount'],
                            'totalVenta' => $reservationHotelRoom->total_amount,
                            'notasEjecutiva' => $reservationHotelRoom->guest_note,
                        ];
                    }


                    if (count($rooms_cancelar) > 0) {
                        $reservas_hotel_cancelar[] = [
                            'codigoHotel' => $reservationHotel->hotel_code,
                            'check_in' => $reservationHotel->check_in,
                            'rooms' => $rooms_cancelar
                        ];
                    }
                }
            }


            // si no hay hoteles viables para la cancelacion de esta cabecera no enviamos
            if (count($reservas_hotel_cancelar) == 0) {
                continue;
            }

            /* fechaRegistro = tenemos que enviar el check_id de la reserva*/
            $fechaRegistroCabFile = $reservation->created_at->format('d/m/Y');
            foreach ($reservas_hotel_cancelar as $reservationHotel) {
                $fechaRegistroCabFile = Carbon::make($reservationHotel['check_in'])->format('d/m/Y');
            }


            $dato = new SimpleXMLElement("<dato></dato>");
            $CabeceraFile = $dato->addChild('CabeceraFile');

            $CabeceraFile->addChild('cabFile')->addAttribute('numeroPedido', $reservation->id);
            $CabeceraFile->addChild('cabFile')->addAttribute('codigoCliente', $reservation->client_code);


            // codigoEjecutiva="1" Cliente
            // codigoEjecutiva="2" Ejecutivo
            $CabeceraFile->addChild('cabFile')->addAttribute('tipoCliente', '1');
            $CabeceraFile->addChild('cabFile')->addAttribute('codigoEjecutiva', strtoupper(trim($executive_code)));
            $CabeceraFile->addChild('cabFile')->addAttribute('fechaRegistro',
                $fechaRegistroCabFile);
            // Si el File es igual a 0 entonces se crea un nuevo file
            // Si el File es mayor a 0 entonces agrega servicios al file en mencion
            $CabeceraFile->addChild('cabFile')->addAttribute('file',
                $file_code == '000000' ? '0' : $file_code);// is zero "0" is send is a new file

            $DetalleHotel = $dato->addChild('DetalleHotel');
            foreach ($reservas_hotel_cancelar as $reserva_hotel_cancelar) {
                $Hotel = $DetalleHotel->addChild('Hotel');

                $Hotel->addChild('datosHotel')->addAttribute('codigoHotel', $reserva_hotel_cancelar['codigoHotel']);
                $Hotel->addChild('datosHotel')->addAttribute('channelUsado', 'BREDOW'); // $reservationHotel->status
                foreach ($reserva_hotel_cancelar['rooms'] as $room_cancelar) {
                    $reserva = $Hotel->addChild('reserva');

                    // codigoReserva Numero unico relacionado al File que agrupar a todas las reservas
                    $reserva->addAttribute('codigoReserva', $room_cancelar['codigoReserva']);
                    if ($idDetalleSvs == "") {
                        $reserva->addAttribute('idDetalleSvs', $room_cancelar['codigoReserva']);
                    } else {
                        $reserva->addAttribute('idDetalleSvs', $idDetalleSvs);
                    }
                    $reserva->addAttribute('totalCosto', $room_cancelar['totalCosto']);
                    $reserva->addAttribute('totalIgv', $room_cancelar['totalIgv']);
                    $reserva->addAttribute('totalVenta', $room_cancelar['totalVenta']);
                    $reserva->addAttribute('notasEjecutiva', $room_cancelar['notasEjecutiva']);
                    $reserva->addAttribute('tipoCancel', 'CANCEL');
                }
            }

            $soapvar = new \SoapVar('<dato xsi:type="xsd:string"><![CDATA[' . $dato->children()[0]->asXML() . $dato->children()[1]->asXML() . ']]></dato>',
                XSD_ANYXML);

            $params = [
                $soapvar,
                'nombrefile' => null,
                'subdirs' => null,
            ];

            try {
                $result = $this->cancelaFile($params);

                $this->putXmlLogAurora(
                    $this->WS_NuevoFile,
                    $this->getCancelaFileLastRequest(), $this->getCancelaFileLastResponse(),
                    '', '',
                    'CancelaFile', $reservation->id, $result['estado'] != '1' ? false : true);

                if ($result['estado'] == '1') {

                    foreach ($reservationsHotel as $reservationHotel) {
                        $fullCanceled = true;
                        foreach ($reservationHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                            if ($reservationsHotelRoom->channel_id != 1) {
                                if ($reservationsHotelRoom->status_in_channel != 0) {
                                    $fullCanceled = false;
                                }

                                $reservationsHotelRoom->status = 0;
                            } else {
                                $reservationsHotelRoom->status_in_channel = 0;
                                $reservationsHotelRoom->status = 0;
                            }

                            $reservationsHotelRoom->save();
                        }

                        if ($fullCanceled) {
                            $reservationHotel->status = 0;
                        }

                        $reservationHotel->save();
                    }
                } else {
                    if (isset($result['files']) and is_array($result['files'])) {
                        $erros[] = implode(' | ', $result['files']);
                    } else {
                        if (isset($result['datosfile']) and is_array($result['datosfile'])) {
                            foreach ($result['datosfile'] as $datosfile) {
                                $erros[] = $datosfile->informacion;
                            }
                        } else {
                            $erros[] = 'no_error_data_in_response';
                        }
                    }
                }
            } catch (\Exception $exception) {
                if (strpos($exception->getMessage(), 'Parsing WSDL: Couldn\'t load from') === false) {
                    $this->putXmlLogAurora(
                        $this->WS_NuevoFile,
                        $this->getCancelaFileLastRequest(),
                        $exception->getMessage() . $this->getCancelaFileLastResponse(),
                        '', $exception->getMessage(),
                        'CancelaFile', $reservation->id, false);
                } else {
                    $this->putXmlLogAurora(
                        $this->WS_NuevoFile,
                        $exception->getMessage(), $exception->getMessage(),
                        $exception->getMessage(), $exception->getMessage(),
                        'CancelaFile', $reservation->id, false);
                }

                $erros[] = 'Stella cancelation: ' . $exception->getMessage();
            }
        }

        return ['success' => count($erros) > 0 ? false : true, 'errors' => $erros, $reservation];
    }

    public function cancel(
        reservationAurora $reservation,
        $idDetalleSvs = '',
        $block_email_provider,
        $cancel_hotel_or_room
    ) {
        $file_code = $reservation->file_code;
        if (!is_numeric($reservation->file_code)) {
            $file_code = '000000';
        }

        if ($reservation->reservator_type === 'client') {
            // codigoEjecutiva="1" Cliente
            $tipoCliente = 1;
        } else {
            // codigoEjecutiva="2" Ejecutivo
            $tipoCliente = 2;
        }

        $erros = [];
        $groupedByExecutives = $reservation->reservationsHotel->groupBy('executive_code'); // MRS
        foreach ($groupedByExecutives as $executive_code => $reservationsHotel) {
            $reservas_hotel_cancelar = [];
            foreach ($reservationsHotel as $reservationHotel) {

                // Filtramos las reservas de la siguiente manera:
                // Si es una reserva de channel (channel_id != 1) debe estar cancelada en el channel (status_in_channel == 0)
                // Si es una reserva de estela (channel_id == 1) debe estar activa en estela (status == 1)
                $business_region_id = $reservationHotel->business_region_id;
                $groupedByRoomType = $reservationHotel->reservationsHotelRooms->filter(function ($item, $key) {
                    //    return true;
                    return ($item->channel_id != 1 and $item->status_in_channel == 0) or
                        ($item->channel_id == 1 and $item->status == 1);
                })->groupBy('room_type_id');

                foreach ($groupedByRoomType as $reservationHotelRooms) {

                    $rooms_cancelar = [];
                    foreach ($reservationHotelRooms as $reservationHotelRoom) {
                        $igv = [
                            'percent' => 0,
                            'total_amount' => 0,
                        ];
                        $extra_fees = json_decode($reservationHotelRoom['taxes_and_services'], true);
                        if (isset($extra_fees['apply_fees']) and isset($extra_fees['apply_fees']['t'])) {
                            foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                                if ($extra_fee['name'] == 'IGV') {
                                    $igv['percent'] = $extra_fee['value'];
                                    $igv['total_amount'] = $extra_fee['total_amount'];
                                }
                            }
                        }

                        $string = str_replace('–', '-', $reservationHotelRoom->guest_note);
                        $notasEjecutiva = preg_replace("[^A-Za-z0-9|\n|\r|\n\r]", " ", $string);

                        /* TODO: ALEX QUISPE */
                        $cancellation = false;

                        if (is_null($business_region_id) || $business_region_id == 1) { // PERU
                            $policies_cancellation = json_decode($reservationHotelRoom['policies_cancellation'], true);
                            $policy_cancellation = (object)$policies_cancellation[0];
                            $_apply_date = explode('-', $policy_cancellation->apply_date);
                            $apply_date = $_apply_date[2] . '-' . $_apply_date[1] . '-' . $_apply_date[0];
                            // 2021-08-05 < hoy (hoy quieres cancelar, cuando la fecha ya pasó) && ['onRequest'] != 0 (osea es OK) && no fue actualizado el ok desde stella
                            if ($apply_date < date("Y-m-d") and $reservationHotelRoom['onRequest'] != 0 and $reservationHotelRoom['stella_updated_at'] == null) {
                                $cancellation = true;
                                $reservationHotelRoom->total_amount_base = $policy_cancellation->penalty_price;

                                $penalty_price = str_replace(',', '',
                                    $policy_cancellation->penalty_price); // bug aqui "penalty_price" esta trayendo formateado 1,000.00 cuando son miles y generar errores con suma por es se elimino todas las ","
                                $reservationHotelRoom->total_amount = $igv['total_amount'] + $penalty_price;
                                $reservationHotelRoom->total_amount = number_format($reservationHotelRoom->total_amount, 2,
                                    '.', ',');

                            }

                            $rooms_cancelar[] = [
                                'codigoReserva' => $reservationHotelRoom->id,
                                'totalCosto' => $reservationHotelRoom->total_amount_base,
                                'totalIgv' => $igv['total_amount'],
                                'totalVenta' => $reservationHotelRoom->total_amount,
                                'notasEjecutiva' => $notasEjecutiva,
                                'tipoCancel' => (($cancellation) ? 'GASCAN' : 'CANCEL')
                            ];

                        }else{

                            $policies_cancellation = json_decode($reservationHotelRoom['policies_cancellation'], true);

                            $policies_cancellation_details = $policies_cancellation['details']; // extructura ordenada con rangos de fechas y penalidades.
                            $policies_cancellation_parameters = $policies_cancellation['cancellationPolicies']; // parametros de hyperguest
                            $fecha_now = date('Y-m-d H:i');
                            $fecha = Carbon::parse($fecha_now);

                            // 🔎 Buscar el registro cuyo rango contenga esa fecha
                            $select_cancellation = collect($policies_cancellation_details)->first(function ($item) use ($fecha) {
                                $desde = Carbon::parse($item['desde']);
                                $hasta = Carbon::parse($item['hasta']);
                                // between($start, $end, true) incluye ambos límites
                                return $fecha->between($desde, $hasta, true);
                            });

                            $penality_amount = 0;
                            if($select_cancellation){
                                $penality_amount = $select_cancellation['penalizacion_usd'];
                            }else{
                                //si no encuentra fecha entonces fecha actual es mayor a la fecha del chceckin y no lo encuentra entonces hagarramos el ultima parametro que es la penalidad mas alta
                                if(isset($policies_cancellation_details[count($policies_cancellation_details)-1]))
                                {
                                    $penality_amount = $policies_cancellation_details[count($policies_cancellation_details)-1]['penalizacion_usd'];
                                }else{
                                    // si no encuentra nada entonces cobramos cobramos el precio de la habitacion.
                                    $penality_amount = $reservationHotelRoom['total_amount'];
                                }
                            }

                            $cancellation = $penality_amount>0 ? true : false;

                            $rooms_cancelar[] = [
                                'codigoReserva' => $reservationHotelRoom->id,
                                'totalCosto' => $penality_amount,
                                'totalIgv' => $igv['total_amount'],
                                'totalVenta' => $penality_amount,
                                'notasEjecutiva' => $notasEjecutiva,
                                'tipoCancel' => (($cancellation) ? 'GASCAN' : 'CANCEL')
                            ];

                        }
                        /* TODO: ALEX QUISPE */
                    }


                    if (count($rooms_cancelar) > 0) {
                        $reservas_hotel_cancelar[] = [
                            'codigoHotel' => $reservationHotel->hotel_code,
                            'check_in' => $reservationHotel->check_in,
                            'rooms' => $rooms_cancelar
                        ];
                    }
                }
            }

            // si no hay hoteles viables para la cancelacion de esta cabecera no enviamos
            if (count($reservas_hotel_cancelar) == 0) {
                continue;
            }

            /* fechaRegistro = tenemos que enviar el check_id de la reserva*/
            $fechaRegistroCabFile = $reservation->created_at->format('Y-m-d');
            foreach ($reservas_hotel_cancelar as $reservationHotel) {
                $fechaRegistroCabFile = Carbon::make($reservationHotel['check_in'])->format('Y-m-d');
            }

            $datapla = collect([
                'numeroPedido' => $reservation->id,
                'codigoCliente' => $reservation->client_code,
                'tipoCliente' => $tipoCliente,
                'codigoEjecutiva' => strtoupper(trim($executive_code)),
                'fechaRegistro' => $fechaRegistroCabFile,
                'nroref' => $file_code == '000000' ? '0' : $file_code,
            ]);

            $datahtl = collect();
            foreach ($reservas_hotel_cancelar as $reserva_hotel_cancelar) {
                $reserveHtl = collect();
                foreach ($reserva_hotel_cancelar['rooms'] as $room_cancelar) {

                    if ($idDetalleSvs == "") {
                        $idDetalleSvs = $room_cancelar['codigoReserva']; // $reservationHotelRoom->id
                    } else {
                        $idDetalleSvs = $idDetalleSvs;
                    }

                    $reserveHtl->add([
                        'codigoReserva' => $room_cancelar['codigoReserva'],
                        'idDetalleSvs' => $room_cancelar['codigoReserva'],
                        'totalCosto' => $room_cancelar['totalCosto'],
                        'totalIgv' => $room_cancelar['totalIgv'],
                        'totalVenta' => $room_cancelar['totalVenta'],
                        'notasEjecutiva' => $room_cancelar['notasEjecutiva'],
                        'tipoCancel' => $room_cancelar['tipoCancel'],
                        'tipo' => 'OK',
                    ]);
                }
                $datahtl->add([
                    'codigoHotel' => $reserva_hotel_cancelar['codigoHotel'],
                    'channelUsado' => 'BREDOW',
                    'codigoConfirma' => $reserva_hotel_cancelar['codigoHotel'],
                    'reserva' => $reserveHtl
                ]);
            }


            $data = [
                'datapla' => $datapla,
                'datahtl' => $datahtl,
            ];

            try {

                $result = $this->cancelFileApiNode($data);

                $result_confirm = (isset($result->success) and $result->success and isset($result->process) and $result->process) ? true : false;
                $this->putXmlLogAurora(
                    config('services.stella.domain'),
                    json_encode($data),
                    json_encode($result),
                    '',
                    '',
                    'CancelaFile',
                    $reservation->id,
                    $result_confirm,
                    'json'
                );
                if ($result_confirm) {

                    foreach ($reservationsHotel as $reservationHotel) {
                        $fullCanceled = true;
                        foreach ($reservationHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                            if ($reservationsHotelRoom->channel_id != 1) {
                                if ($reservationsHotelRoom->status_in_channel != 0) {
                                    $fullCanceled = false;
                                }
                            } else {
                                $reservationsHotelRoom->status_in_channel = 0;
                            }

                            if ($reservationsHotelRoom->status == 1) {  // solo actualizamos de los registros que estan activos
                                $reservationsHotelRoom->status = 0;
                                $reservationsHotelRoom->cancel_block_email_provider = $block_email_provider;
                                $reservationsHotelRoom->cancel_hotel_or_room = $cancel_hotel_or_room;
                                $reservationsHotelRoom->cancel_user_id = Auth::user()->id;
                                $reservationsHotelRoom->cancel_updated_at = date('Y-m-d H:i:s');
                                $reservationsHotelRoom->save();
                            }
                        }

                        if ($fullCanceled) { // Puede llegar hasta aquí sólo con 1 de 2 rooms a cancelar
                            $reservationHotel->status = 0; // no necesariamente el status 0 indica todo el hotel cancelado
                        }

                        $reservationHotel->save();
                    }
                } else {
                    $erros[] = $result->error;
                }
            } catch (\Exception $exception) {
                $erros[] = $exception->getMessage();
            }
        }

        return ['success' => count($erros) > 0 ? false : true, 'errors' => $erros, $reservation, 'data_ws' => $data];
    }

    /**
     * codigoEjecutiva <<<<------ 6 CARACTERES
     * NUMEROFILE      <<<<------ 7 CARACTERES (COMPLETAR CON CEROS A LA IZQUIERDA)
     * FECSIS          <<<<------ 8 CARACTERES (YYYMMDD)
     * HORASIS         <<<<------ 4 CARACTERES (HHMM) HORA+MINUTOS
     * SEGSIS          <<<<------ 4 CARACTERES (SSMS) SERGUNDOS+MILISEGUNDOS
     * codigoEjecutiva <<<<------ CONCATENAS LA CANTIDAD DE CARACTERES QUE VIENE SIN ESPACIOS EN BLANCO A LA DERECHA O IZQUIERDA
     * .XML
     *
     * @param $codigoEjecutiva
     * @param $NUMEROFILE
     * @return string
     */
    public function createNombreFile($codigoEjecutiva, $NUMEROFILE)
    {
        $date = Carbon::now();
        $NUMEROFILE = str_pad($NUMEROFILE, 7, "0", STR_PAD_LEFT);
        $FECSIS = $date->format('Ymd');
        $HORASIS = $date->format('Hi');
        $SEGSIS = substr($date->format('su'), 4);

        return $codigoEjecutiva . $NUMEROFILE . $FECSIS . $HORASIS . $SEGSIS . $codigoEjecutiva . '.XML';
    }

    /**
     * @param reservationAurora $reservation
     * @return array
     * @throws \SoapFault
     */

    public function create_soap(reservationAurora $reservation)
    {
        $file_code = $reservation->file_code;
        if (!is_numeric($reservation->file_code)) {
            $file_code = '000000';
        }

        if ($reservation->reservator_type === 'client') {
            $tipoCliente = 1;
        } else {
            $tipoCliente = 2;
        }

        $erros = [];
        $cantidadAdulto = collect();
        $cantidadNino = collect();

        $groupedByExecutivesHotel = $reservation->reservationsHotel->groupBy('executive_code');
        $groupedByExecutivesService = $reservation->reservationsService->groupBy('executive_code');
        $reservationPassengers = $reservation->reservationsPassenger;

        $reservation_billing = $reservation->billing;
        $reservationsFlight = $reservation->reservationsFlight;
        $executiveCode = '';

        if (count($reservationsFlight) > 0) {
            foreach ($reservationsFlight as $reservationFlight) {
                $cantidadAdulto->add($reservationFlight->adult_num);
                $cantidadNino->add($reservationFlight->child_num);
            }
        }

        //Obtengo los adultos y niños de los hoteles
        foreach ($groupedByExecutivesHotel as $executive_code => $reservationsHotel) {
            $executiveCode = $executive_code;
            $cantidadAdulto->add($reservationsHotel->max(function ($item) {
                $adult = $item->reservationsHotelRooms->sum('adult_num');
                return $adult;
            }));

            $cantidadNino->add($reservationsHotel->max(function ($item) {
                return $item->reservationsHotelRooms->sum('child_num');
            }));
        }

        //Obtengo los adultos y niños de los servicios
        foreach ($groupedByExecutivesService as $executive_code => $reservationsService) {
            $executiveCode = $executive_code;
            foreach ($reservationsService as $index => $item) {
                $cantidadAdulto->add($item->adult_num);
                $cantidadNino->add($item->child_num);
            }
        }

        if ($reservation->executive_id != '') {
            $executiveCode = User::find($reservation->executive_id)->code;
        }

        /* fechaRegistro = tenemos que enviar el check_id de la reserva*/
        if ($reservation->date_init != null) {
            $fechaRegistroCabFile = Carbon::parse($reservation->date_init)->format('d/m/Y');
        } else {
            $fechaRegistroCabFile = $reservation->created_at->format('d/m/Y');
        }

        //Cabecera
        $dato = new SimpleXMLElement("<dato></dato>");
        $CabeceraFile = $dato->addChild('CabeceraFile');
        $CabeceraFile->addChild('cabFile')->addAttribute('numeroPedido', $reservation->id);

        if ($reservation_billing != null) {
            $CabeceraFile->addChild('cabFile')->addAttribute('codigoCliente',
                strtoupper($reservation_billing->client_stella_code));
        } else {
            $CabeceraFile->addChild('cabFile')->addAttribute('codigoCliente', strtoupper($reservation->client_code));
        }
        $CabeceraFile->addChild('cabFile')->addAttribute('nombreGrupo', $reservation->customer_name);
        // codigoEjecutiva="1" Cliente
        // codigoEjecutiva="2" Ejecutivo
        $CabeceraFile->addChild('cabFile')->addAttribute('tipoCliente', $tipoCliente);
        $CabeceraFile->addChild('cabFile')->addAttribute('codigoEjecutiva', strtoupper(trim($executiveCode)));
        $CabeceraFile->addChild('cabFile')->addAttribute('cantPax', $cantidadAdulto->max() + $cantidadNino->max());
        $CabeceraFile->addChild('cabFile')->addAttribute('cantidadAdulto', $cantidadAdulto->max());
        $CabeceraFile->addChild('cabFile')->addAttribute('cantidadNino', $cantidadNino->max());
        $CabeceraFile->addChild('cabFile')->addAttribute('cantidadInfante', '0');
        $CabeceraFile->addChild('cabFile')->addAttribute('fechaRegistro', $fechaRegistroCabFile);
        // Si el File es igual a 0 entonces se crea un nuevo file
        // Si el File es mayor a 0 entonces agrega servicios al file en mencion
        $CabeceraFile->addChild('cabFile')->addAttribute('file',
            $file_code == '000000' ? '0' : $file_code);// is zero "0" is send is a new file
        $CabeceraFile->addChild('cabFile')->addAttribute('idioma', 'EN');

        $CabeceraFile->addChild('cabFile')->addAttribute('descuento', $reservation->total_discounts);
        $CabeceraFile->addChild('cabFile')->addAttribute('subtotal', $reservation->subtotal_amount);
        $CabeceraFile->addChild('cabFile')->addAttribute('igv', $reservation->total_tax);
        $CabeceraFile->addChild('cabFile')->addAttribute('importe', $reservation->total_amount);
        $tipoFile = '';

        if ($file_code != '000000') {
            if ($groupedByExecutivesHotel->count() > 0) {
                $tipoFile = 'HOTEL';
            } else {
                $tipoFile = '';
            }

            if (count($reservationsFlight) > 0 and $tipoFile == '') {
                $tipoFile = 'VUELOS';
            } else {
                $tipoFile = '';
            }
        }

        $CabeceraFile->addChild('cabFile')->addAttribute('tipofile', $tipoFile);

        foreach ($groupedByExecutivesHotel as $executive_code => $reservationsHotel) {
            $DetalleHotel = $dato->addChild('DetalleHotel');
            foreach ($reservationsHotel as $reservationHotel) {
                $groupedByRoomType = $reservationHotel->reservationsHotelRooms->filter(function ($item, $key) {
                    return ($item->status_in_channel == 1 and $item->channel_id != 1) or
                        $item->status_in_channel != 1 and $item->channel_id == 1;
                })->groupBy('room_type_id');
                foreach ($groupedByRoomType as $reservationHotelRooms) {

                    $Hotel = $DetalleHotel->addChild('Hotel');

                    $Hotel->addChild('datosHotel')->addAttribute('codigoHotel', $reservationHotel->hotel_code);
                    $Hotel->addChild('datosHotel')->addAttribute('svsAdicional', 'NO');
                    $Hotel->addChild('datosHotel')->addAttribute('fechaInicio',
                        Carbon::make($reservationHotel->check_in)->format('d/m/Y'));
                    $Hotel->addChild('datosHotel')->addAttribute('fechaFin',
                        Carbon::make($reservationHotel->check_out)->format('d/m/Y'));
                    $Hotel->addChild('datosHotel')->addAttribute('cantidadNoches', $reservationHotel->nights);

                    $paxRoom = $reservationHotelRooms->sum(function ($item) {
                        return $item->adult_num + $item->child_num;
                    });

                    // tipoHabitacion="1" equivale a Simple
                    // tipoHabitacion="2" equivale a Doble
                    $Hotel->addChild('datosHotel')->addAttribute('tipoHabitacion', $paxRoom > 1 ? 2 : 1);
                    $Hotel->addChild('datosHotel')->addAttribute('cantHabitacion', $reservationHotelRooms->count());
                    // TODO: se debe mover el nodo datosHotel->channelUsado y datosHotel->estadoReserva a reserva[]->channelUsado y  reserva[]->estadoReserva ya que estos datos son por habitacion tarifa y no por hotel.
//                    $Hotel->addChild('datosHotel')->addAttribute('channelUsado', 'AURORA'); // $reservationHotel->status
                    $Hotel->addChild('datosHotel')->addAttribute('channelUsado', 'BREDOW'); // $reservationHotel->status
                    // svsAdicional= Puede ser OK o RQ
                    $estadoReservaParaEstela = $reservationHotelRooms[0]->onRequest == "1" ? "OK" : "RQ";
                    // $estadoReservaParaEstela = "OK";
                    $Hotel->addChild('datosHotel')->addAttribute('estadoReserva',
                        $estadoReservaParaEstela);// $reservationHotel->status
                    $Hotel->addChild('datosHotel')->addAttribute('codigoConfirma',
                        $reservationHotelRooms[0]->channel_reservation_code);

                    // echo "<pre>";
                    // print_r($reservationHotelRooms);
                    // die('..');


                    foreach ($reservationHotelRooms as $reservationHotelRoom) {

                        $igv = [
                            'percent' => 0,
                            'total_amount' => 0,
                        ];
                        $extra_fees = json_decode($reservationHotelRoom['taxes_and_services'], true);
                        if (isset($extra_fees['apply_fees']) and isset($extra_fees['apply_fees']['t'])) {
                            foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                                if ($extra_fee['name'] == 'IGV') {
                                    $igv['percent'] = $extra_fee['value'];
                                    $igv['total_amount'] = $extra_fee['total_amount'];
                                }
                            }
                        }

                        $reserva = $Hotel->addChild('reserva');

                        $cantidadAdulto = $reservationHotelRoom->adult_num;
                        $cantidadPax = $cantidadAdulto + $reservationHotelRoom->child_num;

                        // codigoReserva Numero unico relacionado al File que agrupar a todas las reservas
                        $reserva->addAttribute('codigoReserva', $reservationHotelRoom->id);
                        $reserva->addAttribute('idDetalleSvs', $reservationHotelRoom->id);
                        $reserva->addAttribute('codigoHabitacion', $reservationHotelRoom->room_code);
                        $reserva->addAttribute('nombreHabitacion', $reservationHotelRoom->room_name);
                        $reserva->addAttribute('codigoTarifa', $reservationHotelRoom->rate_plan_code);
                        $reserva->addAttribute('totalCosto', $reservationHotelRoom->total_amount_base);
                        $reserva->addAttribute('totalIgv', $igv['total_amount']);
                        $reserva->addAttribute('totalVenta', $reservationHotelRoom->total_amount);
                        $reserva->addAttribute('cantidadPax', $cantidadPax);
                        $reserva->addAttribute('cantidadAdulto', $cantidadAdulto);
                        $reserva->addAttribute('cantidadNino', $reservationHotelRoom->child_num);
                        $reserva->addAttribute('cantidadInfante', '0');
                        $reserva->addAttribute('notasEjecutiva', $reservationHotelRoom->guest_note);
                    }
                }
            }
        }

        foreach ($groupedByExecutivesService as $executive_code => $reservationsService) {
            $DetalleService = $dato->addChild('DetalleServicio');
            foreach ($reservationsService as $reservationService) {
                $service = $DetalleService->addChild('Service');
                $service->addChild('datosServ')->addAttribute('codigoReserva', $reservationService->id);
                $service->addChild('datosServ')->addAttribute('idDetalleSvs', '123456');
                $service->addChild('datosServ')->addAttribute('codigoService', $reservationService->service_code);
                $service->addChild('datosServ')->addAttribute('fechaInicio',
                    Carbon::make($reservationService->date)->format('d/m/Y'));
                $service->addChild('datosServ')->addAttribute('horaInicio',
                    Carbon::make($reservationService->time)->format('H:m'));
                $service->addChild('datosServ')->addAttribute('cantidadPax',
                    ($reservationService->adult_num + $reservationService->child_num));
                $service->addChild('datosServ')->addAttribute('channelUsado', 'BREDOW');
                $service->addChild('datosServ')->addAttribute('totalCosto',
                    $reservationService->total_amount_base);//preguntar
                $service->addChild('datosServ')->addAttribute('totalIgv',
                    $reservationService->total_amount);//preguntar
                $service->addChild('datosServ')->addAttribute('totalVenta', $reservationService->total_amount);
                $service->addChild('datosServ')->addAttribute('cantidadAdulto', $reservationService->adult_num);
                $service->addChild('datosServ')->addAttribute('cantidadNino', $reservationService->child_num);
                $service->addChild('datosServ')->addAttribute('cantidadInfante', $reservationService->infant_num);
                $service->addChild('datosServ')->addAttribute('notasEjecutiva', $reservationService->guest_note);
            }
        }

        if (count($reservationsFlight) > 0) {
            $DetalleFlight = $dato->addChild('DetalleVuelo');
            foreach ($reservationsFlight as $reservationFlight) {
                $flight = $DetalleFlight->addChild('Vuelo');
                $date = Carbon::make($reservationFlight->date)->format('d/m/Y');

                $flight->addChild('datosVue')->addAttribute('codsvs',
                    $reservationFlight->code_flight);
                $flight->addChild('datosVue')->addAttribute('ciudes',
                    $reservationFlight->origin);
                $flight->addChild('datosVue')->addAttribute('fecin',
                    $date);
                $flight->addChild('datosVue')->addAttribute('ciuhas',
                    $reservationFlight->destiny);
                $flight->addChild('datosVue')->addAttribute('horin',
                    '');
                $flight->addChild('datosVue')->addAttribute('fecout',
                    $date);
                $flight->addChild('datosVue')->addAttribute('horout',
                    '');
                $flight->addChild('datosVue')->addAttribute('ciavue',
                    '');
                $flight->addChild('datosVue')->addAttribute('nrovue',
                    '');
                // $flight->addChild('datosVue')->addAttribute('codrsv', '');
                $flight->addChild('datosVue')->addAttribute('clase',
                    '');
                $flight->addChild('datosVue')->addAttribute('canadl',
                    $reservationFlight->adult_num);
                $flight->addChild('datosVue')->addAttribute('canchd',
                    $reservationFlight->child_num);
                $flight->addChild('datosVue')->addAttribute('caninf',
                    $reservationFlight->inf_num);
            }
        }

        $detallePax = $dato->addChild('DetallePax');
        foreach ($reservationPassengers as $key => $passenger) {
            $pax = $detallePax->addChild('Pax');
            $pax->addChild('datosPax')->addAttribute('secuen', $key + 1);
            $pax->addChild('datosPax')->addAttribute('nombre', $passenger->name . ' ' . $passenger->surnames);
            $pax->addChild('datosPax')->addAttribute('tipo', $passenger->type);
            $pax->addChild('datosPax')->addAttribute('sexo', $passenger->genre);
            $pax->addChild('datosPax')->addAttribute('fecnac',
                (isset($passenger->date_birth)) ? Carbon::make($passenger->date_birth)->format('d/m/Y') : '');
            $pax->addChild('datosPax')->addAttribute('ciunac', 'LIM');
            $pax->addChild('datosPax')->addAttribute('nacion', 'PE');
            $pax->addChild('datosPax')->addAttribute('tipdoc',
                (isset($passenger->document_type->iso)) ? $passenger->document_type_iso : 'DNI');
            $pax->addChild('datosPax')->addAttribute('nrodoc',
                (isset($passenger->document_number)) ? $passenger->document_number : '');
            $pax->addChild('datosPax')->addAttribute('estado', 'OK');
            $pax->addChild('datosPax')->addAttribute('correo', '');
            $pax->addChild('datosPax')->addAttribute('celula', '');
            $pax->addChild('datosPax')->addAttribute('resmed', (isset($passenger->resmed)) ? $passenger->resmed : '');
            $pax->addChild('datosPax')->addAttribute('resali', (isset($passenger->resali)) ? $passenger->resali : '');
            $pax->addChild('datosPax')->addAttribute('observ', '');
        }

        if ($groupedByExecutivesHotel->count() > 0 and $groupedByExecutivesService->count() > 0 and count($reservationsFlight) > 0) {
            $soapvar = new \SoapVar('<dato xsi:type="xsd:string"><![CDATA[' . $dato->children()[0]->asXML() . $dato->children()[1]->asXML() . $dato->children()[2]->asXML() . $dato->children()[3]->asXML() . ']]></dato>',
                XSD_ANYXML);
        } else {
            if (($groupedByExecutivesHotel->count() > 0 and $groupedByExecutivesService->count() > 0) or ($groupedByExecutivesHotel->count() > 0 and count($reservationsFlight) > 0) or (count($reservationsFlight) > 0 and $groupedByExecutivesService->count() > 0)) {
                $soapvar = new \SoapVar('<dato xsi:type="xsd:string"><![CDATA[' . $dato->children()[0]->asXML() . $dato->children()[1]->asXML() . $dato->children()[2]->asXML() . ']]></dato>',
                    XSD_ANYXML);
            } else {
                $soapvar = new \SoapVar('<dato xsi:type="xsd:string"><![CDATA[' . $dato->children()[0]->asXML() . $dato->children()[1]->asXML() . ']]></dato>',
                    XSD_ANYXML);
            }
        }

        $params = [
            $soapvar,
            'nombrefile' => $this->createNombreFile(trim($executiveCode), $reservation->file_code),
            'subdirs' => 0,
        ];

        try {
            $result = $this->creandoFile($params);
            $this->putXmlLogAurora(
                $this->WS_NuevoFile,
                $this->getNuevoFileLastRequest(), $this->getNuevoFileLastResponse(),
                '', '',
                'CreandoFile', $reservation->id, $result['estado'] != '1' ? false : true);

            if ($result['estado'] == '1') {
                $reservation->file_code = $result['files'][0];
                $reservation->save();
                //Hoteles
                foreach ($groupedByExecutivesHotel as $executive_code => $reservationsHotel) {
                    foreach ($reservationsHotel as $reservationHotel) {
                        $fullConfirmed = true;
                        foreach ($reservationHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                            if ($reservationsHotelRoom->channel_id != 1) {
                                if ($reservationsHotelRoom->status_in_channel != 1) {
                                    $fullConfirmed = false;
                                }

                                $reservationsHotelRoom->status = 1;
                            } else {
                                $reservationsHotelRoom->status_in_channel = 1;
                                $reservationsHotelRoom->status = 1;
                            }

                            $reservationsHotelRoom->save();
                        }

                        if ($fullConfirmed) {
                            $reservationHotel->status = 1;
                        }
                        $reservationHotel->save();
                    }
                }
                //Servicios
                foreach ($groupedByExecutivesService as $executive_code => $reservationsService) {
                    foreach ($reservationsService as $index => $item) {
                        $item->status = 1;
                        foreach ($item->reservationsServiceRatesPlans as $reservationsServiceRatesPlan) {
                            $reservationsServiceRatesPlan->status = 1;
                            $reservationsServiceRatesPlan->save();
                        }
                        $item->save();
                    }
                }

            } else {
                if (isset($result['files']) and is_array($result['files'])) {
                    $erros[] = implode(' | ', $result['files']);
                } else {
                    $erros[] = 'no_error_data_in_response';
                }
            }
        } catch (\Exception $exception) {
            if (strpos($exception->getMessage(), 'Parsing WSDL: Couldn\'t load from') === false) {
                $this->putXmlLogAurora(
                    $this->WS_NuevoFile,
                    $this->getNuevoFileLastRequest(), $exception->getMessage() . $this->getNuevoFileLastResponse(),
                    '', $exception->getMessage(),
                    'CreandoFile', $reservation->id, false);
            } else {
                $this->putXmlLogAurora(
                    $this->WS_NuevoFile,
                    $exception->getMessage(), $exception->getMessage(),
                    $exception->getMessage(), $exception->getMessage(),
                    'CreandoFile', $reservation->id, false);
            }

            $erros[] = 'Stella: ' . $exception->getMessage();
        }

        return ['dato' => $dato, 'success' => count($erros) > 0 ? false : true, 'errors' => $erros, $reservation];
    }


    public function create(reservationAurora $reservation)
    {
        $file_code = $reservation->file_code;
        if (!is_numeric($reservation->file_code)) {
            $file_code = '000000';
        }

        if ($reservation->reservator_type === 'client') {
            //Todo codigoEjecutiva="1" Cliente
            $tipoCliente = 1;
        } else {
            //Todo codigoEjecutiva="2" Ejecutivo
            $tipoCliente = 2;
        }

        $errors = [];
        $cantidadAdulto = collect();
        $cantidadNino = collect();
        $passengerDirect = '';

        $groupedByExecutivesHotel = $reservation->reservationsHotel->groupBy('executive_code');
        $groupedByExecutivesService = $reservation->reservationsService->groupBy('executive_code');
        $reservationPassengers = $reservation->reservationsPassenger;

        $reservation_billing = $reservation->billing;
        $reservationsFlight = $reservation->reservationsFlight;
        $executiveCode = '';

        if (count($reservationsFlight) > 0) {
            foreach ($reservationsFlight as $reservationFlight) {
                $cantidadAdulto->add($reservationFlight->adult_num);
                $cantidadNino->add($reservationFlight->child_num);
            }
        }

        //Todo Obtengo los adultos y niños de los hoteles
        foreach ($groupedByExecutivesHotel as $executive_code => $reservationsHotel) {
            $executiveCode = $executive_code;
            $cantidadAdulto->add($reservationsHotel->max(function ($item) {
                $adult = $item->reservationsHotelRooms->sum('adult_num');
                return $adult;
            }));

            $cantidadNino->add($reservationsHotel->max(function ($item) {
                return $item->reservationsHotelRooms->sum('child_num');
            }));
        }

        //Todo Obtengo los adultos y niños de los servicios
        foreach ($groupedByExecutivesService as $executive_code => $reservationsService) {
            $executiveCode = $executive_code;
            foreach ($reservationsService as $index => $item) {
                $cantidadAdulto->add($item->adult_num);
                $cantidadNino->add($item->child_num);
            }
        }

        if ($reservation->executive_id != '') {
            $executiveCode = User::find($reservation->executive_id)->code;
        }

        //Todo fechaRegistro = tenemos que enviar el check_id de la reserva
        if ($reservation->date_init != null) {
            $fechaRegistroCabFile = Carbon::parse($reservation->date_init)->format('Y-m-d');
        } else {
            $fechaRegistroCabFile = $reservation->created_at->format('Y-m-d');
        }

        //Todo Si hay datos de facturacion obtengo el codigo del cliente creado
        if (!empty($reservation_billing)) {
            $passengerDirect = strtoupper(trim($reservation_billing->client_stella_code));
        }

        if ($reservation->entity === reservationAurora::ENTITY_PACKAGE) {
            $tipofile = 'PAQUETE';
        } elseif ($reservation->entity === reservationAurora::ENTITY_QUOTE) {
            $tipofile = 'COTIZACION';
        } elseif ($reservation->entity === reservationAurora::ENTITY_CART) {
            $tipofile = 'CARRITO';
        } else {
            $tipofile = null;
        }

        //Todo Datos de la cabecera del file
        $datapla = collect([
            'numeroPedido' => $reservation->id,
            'codigoCliente' => strtoupper($reservation->client_code),
            'nombreGrupo' => clearSpecialCharacters($reservation->customer_name),
            'tipoCliente' => $tipoCliente,
            'codigoEjecutiva' => strtoupper(trim($executiveCode)),
            'cantPax' => $cantidadAdulto->max() + $cantidadNino->max(),
            'cantidadAdulto' => $cantidadAdulto->max(),
            'cantidadNino' => $cantidadNino->max(),
            'cantidadInfante' => 0,
            'fechaRegistro' => $fechaRegistroCabFile,
            'nroref' => $file_code == '000000' ? '0' : $file_code,
            'idioma' => 'EN',
            'descuento' => $reservation->total_discounts,
            'subtotal' => $reservation->subtotal_amount,
            'igv' => $reservation->total_tax,
            'importe' => $reservation->total_amount,
            'tipofile' => $tipofile,
            'ideTipofile' => $reservation->object_id,
            'numberOrder' => (empty($reservation->order_number)) ? '' : $reservation->order_number,
            'version' => $reservation->version, //Todo Sirve para saber cuantas veces han modificado la reserva
            'pasajeroDirecto' => $passengerDirect,
            'fechaCreacion' => Carbon::make($reservation->created_at)->format('Y-m-d'),
            'horaCreacion' => Carbon::make($reservation->created_at)->format('H:i:s'),
        ]);

        //Todo Datos de los hoteles
        $datahtl = collect();
        foreach ($groupedByExecutivesHotel as $executive_code => $reservationsHotel) {
            foreach ($reservationsHotel as $reservationHotel) {
                $groupedByRoomType = $reservationHotel->reservationsHotelRooms->filter(function ($item, $key) {
                    return ($item->status_in_channel == 1 and $item->channel_id != 1) or
                        $item->status_in_channel != 1 and $item->channel_id == 1;
                })->groupBy('room_type_id');

                //Todo Habitaciones simples
                $tipoHabSGL = collect();
                //Todo Habitaciones dobles
                $tipoHabDBL = collect();
                //Todo Habitaciones triples
                $tipoHabTRP = collect();

                foreach ($groupedByRoomType as $type_room => $reservationHotelRooms) {
                    foreach ($reservationHotelRooms as $reservationHotelRoom) {
                        /*
                         *Todo  @variable $tipoReserva => 1 = CHANNELL, 2 = ALLOTMENTS , 3 = ON REQUEST
                         */
                        $tipoReserva = '';
                        if ($reservationHotelRoom->channel_id == '1' and $reservationHotelRoom->onRequest == ReservationsHotelsRatesPlansRooms::NOT_ON_REQUEST) {
                            $estadoReservaParaEstela = 'OK';
                            $tipoReserva = 2;
                        } elseif ($reservationHotelRoom->channel_id == '1' and $reservationHotelRoom->onRequest == ReservationsHotelsRatesPlansRooms::ON_REQUEST) {
                            $estadoReservaParaEstela = 'RQ';
                            $tipoReserva = 3;
                        } elseif ($reservationHotelRoom->channel_id != '1') {
                            $estadoReservaParaEstela = ($reservationHotelRoom->onRequest == 1) ? 'OK' : 'RQ';
                            $tipoReserva = 1;
                        }

                        $igv = [
                            'percent' => 0,
                            'total_amount' => 0,
                        ];

                        $extra_fees = json_decode($reservationHotelRoom['taxes_and_services'], true);
                        if (isset($extra_fees['apply_fees']) and isset($extra_fees['apply_fees']['t'])) {
                            foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                                if ($extra_fee['name'] == 'IGV') {
                                    $igv['percent'] = $extra_fee['value'];
                                    $igv['total_amount'] = $extra_fee['total_amount'];
                                }
                            }
                        }

                        $cantidadAdulto = $reservationHotelRoom->adult_num;
                        $cantidadPax = $cantidadAdulto + $reservationHotelRoom->child_num;


                        $room = [
                            'codigoReserva' => $reservationHotelRoom->id,
                            'tipoReserva' => $tipoReserva,
                            'idDetalleSvs' => $reservationHotelRoom->id,
                            'codigoHabitacion' => $reservationHotelRoom->room_code,


                            'nombreHabitacion' => $reservationHotelRoom->room_name,


                            'codigoTarifa' => $reservationHotelRoom->rate_plan_code,
                            'nombreTarifa' => clearSpecialCharacters($reservationHotelRoom->rate_plan_name),
                            'totalCosto' => $reservationHotelRoom->total_amount_base,
                            'totalIgv' => $igv['total_amount'],
                            'totalVenta' => $reservationHotelRoom->total_amount,
                            'cantidadHab' => 1,
                            'cantidadPax' => $cantidadPax,
                            'cantidadAdulto' => $cantidadAdulto,
                            'cantidadNino' => $reservationHotelRoom->child_num,
                            'cantidadInfante' => '0',
                            'notasEjecutiva' => $reservationHotelRoom->guest_note,
                            'estadoReserva' => $estadoReservaParaEstela,
                        ];

                        //Todo Si la habitacion es simple
                        if ($reservationHotelRoom->room_type->occupation === 1) {

                            if ($cantidadPax > $reservationHotelRoom->room_type->occupation) {
                                $room['cantidadPax'] = $reservationHotelRoom->room_type->occupation;
                                $room['cantidadAdulto'] = $reservationHotelRoom->room_type->occupation;
                                $room['cantidadNino'] = 0;
                                $room['nombreHabitacion'] = $room['nombreHabitacion'] . ' + CHILD';
                            }
                            $tipoHabSGL->add($room);
                            //Todo Si la habitacion es doble
                        } elseif ($reservationHotelRoom->room_type->occupation === 2) {

                            if ($cantidadPax > $reservationHotelRoom->room_type->occupation) {
                                $room['cantidadPax'] = $reservationHotelRoom->room_type->occupation;
                                $room['cantidadAdulto'] = $reservationHotelRoom->room_type->occupation;
                                $room['cantidadNino'] = 0;
                                $room['nombreHabitacion'] = $room['nombreHabitacion'] . ' + CHILD';
                            }
                            $tipoHabDBL->add($room);
                            //Todo Si la habitacion es triple
                        } elseif ($reservationHotelRoom->room_type->occupation >= 3) {

                            if ($cantidadPax > $reservationHotelRoom->room_type->occupation) {
                                $room['cantidadPax'] = $reservationHotelRoom->room_type->occupation;
                                $room['cantidadAdulto'] = $reservationHotelRoom->room_type->occupation;
                                $room['cantidadNino'] = 0;
                                $room['nombreHabitacion'] = $room['nombreHabitacion'] . ' + CHILD';
                            }
                            $tipoHabTRP->add($room);
                        }
                    }
                }

                $datahtl->add([
                    'codigoHotel' => $reservationHotel->hotel_code,
                    'svsAdicional' => 'NO',
                    'fechaInicio' => Carbon::make($reservationHotel->check_in)->format('Y-m-d'),
                    'horaInicio' => $reservationHotel->check_in_time,
                    'fechaFin' => Carbon::make($reservationHotel->check_out)->format('Y-m-d'),
                    'horaFin' => $reservationHotel->check_out_time,
                    'cantidadNoches' => $reservationHotel->nights,
                    'channelUsado' => 'BREDOW',
                    'codigoConfirma' => (empty($reservationHotelRooms[0]->channel_reservation_code)) ? '' : $reservationHotelRooms[0]->channel_reservation_code,
                    'tipoHabSGL' => $tipoHabSGL,
                    'tipoHabDBL' => $tipoHabDBL,
                    'tipoHabTRP' => $tipoHabTRP,
                    'regionId' => $reservationHotel->business_region_id,
                ]);
            }
        }


        //Todo Datos de los servicios
        $datasvs = collect();
        foreach ($groupedByExecutivesService as $executive_code => $reservationsService) {
            foreach ($reservationsService as $reservationService) {

                $datasvs->add([
                    'codigoReserva' => $reservationService->id,
                    'idDetalleSvs' => $reservationService->id,
                    'codigo' => $reservationService->service_code,
                    'fechaInicio' => Carbon::make($reservationService->date)->format('Y-m-d'),
                    'horaInicio' => (empty($reservationService->time)) ? '' : Carbon::make($reservationService->time)->format('H:i'),
                    'cantidadPax' => ($reservationService->adult_num + $reservationService->child_num),
                    'channelUsado' => 'BREDOW',
                    'totalCosto' => $reservationService->total_amount_base,
                    'totalIgv' => 0,
                    'totalVenta' => $reservationService->total_amount,
                    'cantidadAdulto' => $reservationService->adult_num,
                    'cantidadNino' => $reservationService->child_num,
                    'cantidadInfante' => $reservationService->infant_num,
                    'notasEjecutiva' => $reservationService->guest_note,
                    'asignacionPax' => !empty($reservationService->assigned_passengers) ? json_decode($reservationService->assigned_passengers) : [],
                    'regionId' => $reservationService->business_region_id, // ID DE LA REGION
                ]);
            }
        }
        //Todo Datos de los vuelos
        $datavue = collect();
        if (count($reservationsFlight) > 0) {
            foreach ($reservationsFlight as $reservationFlight) {
                $date = Carbon::make($reservationFlight->date)->format('Y-m-d');
                $datavue->add([
                    'codigoServicio' => $reservationFlight->code_flight,
                    'ciudadDesde' => empty($reservationFlight->origin) ? '' : $reservationFlight->origin,
                    'ciudadHasta' => empty($reservationFlight->destiny) ? '' : $reservationFlight->destiny,
                    'fechaInicio' => $date,
                    'horaInicio' => '',
                    'fechaFin' => $date,
                    'horaFin' => '',
                    'ciaVuelo' => '',
                    'nroVuelo' => '',
                    'codigoReserva' => '',
                    'cantidadAdulto' => $reservationFlight->adult_num,
                    'cantidadNino' => $reservationFlight->child_num,
                    'cantidadInfante' => $reservationFlight->inf_num,
                    'notasEjecutiva' => '',
                ]);
            }
        }

        //Todo Datos de los pasajeros
        $datapax = collect();
        foreach ($reservationPassengers as $key => $passenger) {
            $name = '';
            if (!empty($passenger->name) and !empty($passenger->surnames)) {
                $name = clearSpecialCharacters($passenger->surnames) . ',' . clearSpecialCharacters($passenger->name);
            } else {
                if (!empty($passenger->name)) {
                    $name = ',' . clearSpecialCharacters($passenger->name);
                }

                if (!empty($passenger->surnames)) {
                    $name = clearSpecialCharacters($passenger->surnames) . ',';
                }
            }
            $datapax->add([
                'secuen' => $key + 1,
                'nombre' => $name,
                'tipo' => (empty($passenger->type)) ? '' : $passenger->type,
                'sexo' => (empty($passenger->genre)) ? '' : $passenger->genre,
                'fecnac' => (isset($passenger->date_birth)) ? Carbon::make($passenger->date_birth)->format('Y-m-d') : '',
                'ciunac' => (empty($passenger->city_iso) or $passenger->city_iso == '-1') ? '' : $passenger->city_iso,
                'nacion' => (empty($passenger->country_iso) or $passenger->country_iso == '-1') ? '' : $passenger->country_iso,
                'tipdoc' => (isset($passenger->document_type->iso)) ? $passenger->document_type_iso : 'PAS',
                'nrodoc' => (isset($passenger->document_number)) ? trim($passenger->document_number) : '',
                'estado' => 'OK',
                'correo' => '',
                'celula' => (empty($passenger->phone)) ? '' : $passenger->phone,
                'resmed' => (empty($passenger->medical_restrictions)) ? '' : clearSpecialCharacters($passenger->medical_restrictions),
                'resali' => (empty($passenger->dietary_restrictions)) ? '' : clearSpecialCharacters($passenger->dietary_restrictions),
                'observ' => (empty($passenger->notes)) ? '' : clearSpecialCharacters($passenger->notes),
            ]);
        }

        $data = [
            'datapla' => $datapla,
            'datasvs' => $datasvs,
            'datahtl' => $datahtl,
            'datavue' => $datavue,
            'datapax' => $datapax,
        ];
        try {
            $result = $this->createFileApiNode($data);
            $result_confirm = (isset($result->success) and $result->success and isset($result->process) and $result->process) ? true : false;
            // $result = true;
            // $result_confirm = true;
            $result_message = (isset($result->message) and $result->message != '') ? $result->message : '';
            $result_message = 'success';
            $this->putXmlLogAurora(
                config('services.stella.domain'),
                json_encode($data), json_encode($result),
                '', '',
                'CreandoFile', $reservation->id, $result_confirm, 'json');
            if ($result_confirm) {
                //Hoteles
                foreach ($groupedByExecutivesHotel as $executive_code => $reservationsHotel) {
                    foreach ($reservationsHotel as $reservationHotel) {
                        $fullConfirmed = true;
                        foreach ($reservationHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                            if ($reservationsHotelRoom->channel_id != 1) {
                                if ($reservationsHotelRoom->status_in_channel != 1) {
                                    $fullConfirmed = false;
                                }

                                $reservationsHotelRoom->status = 1;
                            } else {
                                $reservationsHotelRoom->status_in_channel = 1;
                                $reservationsHotelRoom->status = 1;
                            }

                            $reservationsHotelRoom->save();
                        }

                        if ($fullConfirmed) {
                            $reservationHotel->status = 1;
                        }
                        $reservationHotel->save();
                    }
                }
                //Servicios
                foreach ($groupedByExecutivesService as $executive_code => $reservationsService) {
                    foreach ($reservationsService as $index => $item) {
                        $item->status = 1;
                        foreach ($item->reservationsServiceRatesPlans as $reservationsServiceRatesPlan) {
                            $reservationsServiceRatesPlan->status = 1;
                            $reservationsServiceRatesPlan->save();
                        }
                        $item->save();
                    }
                }

            } else {
                // $find_version = 'Version JSON';
                // $pos_version = strpos($result_message, $find_version);
                // if ($pos_version !== false) {
                //     $errors = [];
                // } else {
                //     $errors[] = $result_message;
                // }

                $errors[] = $result_message;
            }
        } catch (\Exception $exception) {
            if (strpos($exception->getMessage(), 'Parsing WSDL: Couldn\'t load from') === false) {
                $this->putXmlLogAurora(
                    $this->WS_NuevoFile,
                    $this->getNuevoFileLastRequest(), $exception->getMessage() . $this->getNuevoFileLastResponse(),
                    '', $exception->getMessage(),
                    'CreandoFile', $reservation->id, false);
            } else {
                $this->putXmlLogAurora(
                    $this->WS_NuevoFile,
                    $exception->getMessage(), $exception->getMessage(),
                    $exception->getMessage(), $exception->getMessage(),
                    'CreandoFile', $reservation->id, false);
            }

            $errors[] = 'Stella: ' . $exception->getMessage();
        }

        return ['dato' => $data, 'success' => count($errors) > 0 ? false : true, 'errors' => $errors, $reservation];
    }


}
