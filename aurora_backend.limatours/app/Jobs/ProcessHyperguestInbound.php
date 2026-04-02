<?php

namespace App\Jobs;

use App\Http\Hyperguest\Traits\BulkInsertUpdateTrait;
use App\Http\Hyperguest\Traits\Hyperguest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ProcessHyperguestInbound implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    use Hyperguest, BulkInsertUpdateTrait;

    // === Constantes (copiadas del controlador) ===
    const LIMATOURS_ID = 'LIMATOURS';
    const CONECTOR_CODE = 'HYPERGUEST';
    const WSDL = "http://schemas.xmlsoap.org/wsdl/";
    const SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    const WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis200401-wss-wssecurity-secext-1.0.xsd";
    const WSA = "http://schemas.xmlsoap.org/ws/2004/08/addressing";
    const OTA = "http://www.opentravel.org/OTA/2003/05";
    const TNS = "http://127.0.0.1:8000/api/hyperguest";
    const XS = "http://www.w3.org/2001/XMLSchema";
    const XSI = "http://www.w3.org/2001/XMLSchema-instance";

    // Códigos de respuesta/errores
    const SYSTEM_CORRENTLY_UNAVAILABLE = 187;
    const INVALID_PROPERTY_CODE = 400;
    const SYSTEM_ERROR = 448;
    const UNABLE_TO_PROCESS = 450;
    const INVALID_RATE_CODE = 249;
    const REQUIRED_FIELD_MISSING = 321;
    const HOTEL_NOT_ACTIVE = 375;
    const INVALID_HOTEL_CODE = 392;
    const INVALID_ROOM_TYPE = 402;
    const RATE_DOES_NOT_EXIST = 436;
    const ROOM_OR_RATE_NOT_FOUND = 783;
    const RATE_NOT_LOADED = 842;

    /** Cola por defecto (puedes cambiarla con ->onQueue() al despachar) */

    /** Reintentos y timeout */
    public $tries = 3;
    public $timeout = 120;

    /** XML crudo */
    protected $inboundId;

    /** Método forzado opcional (si no, se detecta del XML) */
    protected $forcedMethod;

    public function __construct(int $inboundId, ?string $forcedMethod = null)
    {
        $this->inboundId = $inboundId;
        $this->forcedMethod = $forcedMethod;
        $this->onQueue('hyperguest_inbound');
    }

    public function displayName()
    {
        return 'ProcessHyperguestInbound';
    }

    public function handle(): void
    {

        $inbound = DB::transaction(function () {
            // Traer solo si está en queued (pendiente)
            $row = DB::table('hyperguest_inbounds')
                ->where('id', $this->inboundId)
                ->where('status', 'queued')
                ->lockForUpdate()
                ->first();

            // Si no existe o ya fue tomado, salir
            if (!$row) {
                return null;
            }

            // Actualizar a "processing" y sumar intento
            DB::table('hyperguest_inbounds')
                ->where('id', $this->inboundId)
                ->update([
                    'status' => 'processing',
                    'attempts' => DB::raw('attempts + 1'),
                    'updated_at' => now(),
                ]);

            return $row;
        });

        if (!$inbound) {
            // Nada que procesar
            return;
        }

        try {
            // Inicialización básica del Trait Hyperguest
            $this->setChannel(self::CONECTOR_CODE);
            $this->setXMLRequest($inbound->payload_xml ?? '');
            $this->setVersion();
            $this->setEchoToken();
            $this->setTimeStamp();

            // Detecta método si no fue forzado
            $method = $this->forcedMethod ?: $this->requestedMethodName();

            if (!method_exists($this, $method)) {
                return;
            }

            // Ejecuta el método correspondiente (tal cual en el controlador)
            $this->{$method}();

            // Al terminar correctamente
            DB::table('hyperguest_inbounds')
                ->where('id', $this->inboundId)
                ->update([
                    'status' => 'processed',
                    'updated_at' => now(),
                    'error' => null,
                ]);

        } catch (\Throwable $e) {
            $this->markFailed($e->getMessage());
            return;
        }

    }

    public function failed(\Throwable $e): void
    {
        $this->markFailed($e->getMessage());
    }

    protected function markFailed(string $message): void
    {
        try {
            DB::table('hyperguest_inbounds')
                ->where('id', $this->inboundId)
                ->update([
                    'status' => 'failed',
                    'error' => substr($message, 0, 65535),
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $ex) {
            $ex->getMessage();
        }
    }


    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function OTA_HotelAvailNotifRQ(): SimpleXMLElement
    {
        $this->setRequestedHotel($this->getXMLRequest()->AvailStatusMessages);
        $this->setAvailableRooms();

        $arrayUpdates = [
            "inventories" => [
                "inserts" => [],
                "updates" => [],
            ],
            "calendarys" => [
                "inserts" => [],
                "updates" => [],
            ],
        ];
        $arrayUpdates = $this->OTA_HotelAvailNotifRQ_GetUpdates($arrayUpdates);

        // Si hay cambios, encolamos el proceso
        if (
            count($arrayUpdates["inventories"]["inserts"]) > 0 ||
            count($arrayUpdates["inventories"]["updates"]) > 0 ||
            count($arrayUpdates["calendarys"]["inserts"]) > 0 ||
            count($arrayUpdates["calendarys"]["updates"]) > 0
        ) {
            dispatch(new ProcessInventoryPushHyperguest($arrayUpdates))->onQueue("inventory_hyperguest_push");
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }

    /**
     * @param array $arrayUpdates
     * @return array
     * @throws Exception
     */
    public function OTA_HotelAvailNotifRQ_GetUpdates(array $arrayUpdates): array
    {
        $AvailStatusMessagesList = $this->getXMLRequest()->AvailStatusMessages;

        $controlXMLHyperguest = [
            "control_xml_disponibilidad" => false,
            "control_xml_minimo_noches" => false,
            "control_xml_estado_booking" => false,
            "control_xml_estado_arrival" => false,
            "control_xml_estado_departure" => false,
        ];

        $messages = [];

        $maxIndex = count($AvailStatusMessagesList->AvailStatusMessage); // Contar el número de elementos en la lista

        for ($i = 0; $i < $maxIndex; $i++) {
            $AvailStatusMessage = $AvailStatusMessagesList->AvailStatusMessage[$i];

            $messages['inventories'] = [];

            $controlXMLHyperguest["control_xml_disponibilidad"] = false; // Reinicializar variable
            $controlXMLHyperguest["control_xml_minimo_noches"] = false; // Reinicializar variable
            $controlXMLHyperguest["control_xml_estado_booking"] = false; // Reinicializar variable
            $controlXMLHyperguest["control_xml_estado_arrival"] = false; // Reinicializar variable
            $controlXMLHyperguest["control_xml_estado_departure"] = false; // Reinicializar variable

            if (isset($AvailStatusMessage->StatusApplicationControl) && isset($AvailStatusMessage->StatusApplicationControl->attributes()->InvTypeCode) && isset($AvailStatusMessage->StatusApplicationControl->attributes()->RatePlanCode)) {

                $attrInvTypeCode = $AvailStatusMessage->StatusApplicationControl->attributes()->InvTypeCode->__toString();
                $attrRatePlanCode = $AvailStatusMessage->StatusApplicationControl->attributes()->RatePlanCode->__toString();

                $rooms = $this->getAvailableRooms()->filter(function ($query) use ($attrInvTypeCode) {
                    return $query->channels[0]->pivot->code == $attrInvTypeCode;
                });

                foreach ($rooms as $room) {
                    $arrayResultados = $this->OTA_HotelAvailNotifRQ_Inventory($room, $attrRatePlanCode, $AvailStatusMessage, $messages, $controlXMLHyperguest);
                    if (!$arrayResultados["isNotBreakContinue"])
                        continue;

                    // Agregar objeto a arreglo de updates o inserts, para actualizar o crear masivamente
                    if (count($arrayResultados["inventories"]["inserts"]) > 0)
                        $arrayUpdates["inventories"]["inserts"][] = $arrayResultados["inventories"]["inserts"][0];
                    if (count($arrayResultados["inventories"]["updates"]) > 0)
                        $arrayUpdates["inventories"]["updates"][] = $arrayResultados["inventories"]["updates"][0];
                    if (count($arrayResultados["calendarys"]["inserts"]) > 0)
                        $arrayUpdates["calendarys"]["inserts"][] = $arrayResultados["calendarys"]["inserts"][0];
                    if (count($arrayResultados["calendarys"]["updates"]) > 0)
                        $arrayUpdates["calendarys"]["updates"][] = $arrayResultados["calendarys"]["updates"][0];
                }
            }
        }

        return $arrayUpdates;
    }

    /**
     * @param $room
     * @param string $RatePlanCode
     * @param SimpleXMLElement $AvailStatusMessage
     * @param array $messages
     * @param array $controlXMLHyperguest
     * @return array
     * @throws Exception
     */
    public function OTA_HotelAvailNotifRQ_Inventory($room, string $RatePlanCode, SimpleXMLElement $AvailStatusMessage, array $messages, array $controlXMLHyperguest): array
    {
        $arrayResultados = [
            "isNotBreakContinue" => false,
            "inventories" => [
                "inserts" => [],
                "updates" => [],
            ],
            "calendarys" => [
                "inserts" => [],
                "updates" => [],
            ],
        ];
        $AllRatesPlanRoom = $room->rates_plan_room;

        if (empty($room) or empty($AllRatesPlanRoom)) {
            return $arrayResultados;// False
        }

        // Planes Tarifa x Habitación
        $ratesPlanRoom = $AllRatesPlanRoom->first(function ($query) use ($RatePlanCode) {
            return $query->rate_plan->code == $RatePlanCode;
        });

        if (empty($ratesPlanRoom)) {
            return $arrayResultados;// False
        }

        // RESTRICCION LLEGADAS Y SALIDAS
        // PARTE 01
        if (
            isset($AvailStatusMessage->attributes()->BookingLimit) &&
            isset($AvailStatusMessage->RestrictionStatus) &&
            isset($AvailStatusMessage->RestrictionStatus->attributes()->Status)
        ) {

            $controlXMLHyperguest["control_xml_disponibilidad"] = true;
            $controlXMLHyperguest["control_xml_estado_booking"] = true;

            // Regla Hyperguest
            // En este caso indica que la fecha se encuentra cerrada para los rateplans y habitaciones buscadas. En este caso, Limatours no debería mostrar disponibilidad a sus clientes ya que  la reserva fallará.
            $attrStatus = (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Status->__toString()));
            $cupo = (int)$AvailStatusMessage->attributes()->BookingLimit->__toString();

            $bookingIsClosed = ($attrStatus == 'closed') ? 1 : 0;
            $locked = ($cupo < 1) ? 1 : $bookingIsClosed;// $locked = True

            $messages['inventories']['locked'] = $locked;
            $messages["inventories"]["inventory_num"] = $cupo;

            $messages['restrictions']['restriction_status'] = !$bookingIsClosed;
            $messages['restrictions']['restriction_arrival'] = 0;
            $messages['restrictions']['restriction_departure'] = 0;
        }

        // RESTRICCION LLEGADAS Y SALIDAS
        // PARTE 02
        if (
            isset($AvailStatusMessage->RestrictionStatus) &&
            isset($AvailStatusMessage->RestrictionStatus->attributes()->Status) &&
            isset($AvailStatusMessage->RestrictionStatus->attributes()->Restriction)
        ) {

            $attrRestriction = (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Restriction->__toString()));
            $attrStatus = (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Status->__toString()));

            // Controlar restricción de llegada
            if ($attrRestriction == 'arrival') {
                $controlXMLHyperguest["control_xml_estado_arrival"] = true;

                // Controlar bloqueo de inventario si la restricción de llegada está cerrada
                $arrivalIsClosed = ($attrStatus == 'closed') ? 0 : 1;
                $messages['inventories']['locked'] = !$arrivalIsClosed; // 1 = True / 0 = False

                $messages['restrictions']['restriction_arrival'] = $arrivalIsClosed;
            }

            // Controlar restricción de salida
            if ($attrRestriction == 'departure') {
                $controlXMLHyperguest["control_xml_estado_departure"] = true;

                $departureIsClosed = ($attrStatus == 'closed') ? 0 : 1;

                $messages['restrictions']['restriction_departure'] = $departureIsClosed;
            }
        }

        // RESTRICCION LIMITE DE NOCHES
        if (isset($AvailStatusMessage->LengthsOfStay)) {
            foreach ($AvailStatusMessage->LengthsOfStay->LengthOfStay as $LengthOfStay) {
                if (!isset($LengthOfStay->attributes()->MinMaxMessageType) && !isset($LengthOfStay->attributes()->Time))
                    continue;

                $attrMinMaxMessageType = $LengthOfStay->attributes()->MinMaxMessageType->__toString();
                $attrTime = $LengthOfStay->attributes()->Time;

                switch ($attrMinMaxMessageType) {
                    case "SetMinLOS":
                        $messages['restrictions']['min_length_stay'] = empty($attrTime) ? 0 : (int)$attrTime;
                        $controlXMLHyperguest["control_xml_minimo_noches"] = true;
                        break;
                    case "SetMaxLOS":
                        $messages['restrictions']['max_length_stay'] = empty($attrTime) ? 99 : (int)$attrTime;
                        $controlXMLHyperguest["control_xml_minimo_noches"] = true;
                        break;
                    default:
                        continue 2;
                }
            }
        }

        if (count($messages) == 0) {
            return $arrayResultados;// False
        }

        $this->setStartEndDates($AvailStatusMessage->StatusApplicationControl);

        $RateRoomInventory = $this->getRatePlanRoomInventories($ratesPlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);
        $RateRoomCalendary = $this->getRatePlanRoomRates($ratesPlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);

        $insertInventary = [];
        $updateInventary = [];
        $insertCalendary = [];
        $updateCalendary = [];

        $tokenDate = null;

        foreach ($this->getDateRange() as $date) {
            $dateInput = $date->format("Y-m-d");

            // Identificador único para cada fecha y habitación
            $tokenDate = $dateInput . '|' . $ratesPlanRoom->id;

            if (isset($messages['inventories'])) {
                $rateRoomInventory = null;

                if (!empty($RateRoomInventory)) {
                    // Obtener inventario existente
                    $rateRoomInventory = $RateRoomInventory->first(function ($value) use ($dateInput) {
                        return $value['date'] == $dateInput;
                    });
                }

                // Crear inventario
                if (empty($rateRoomInventory)) {

                    if (isset($arrayResultados["restrictions"][$tokenDate])) {
                        $arrayResultados["restrictions"][$tokenDate] = [
                            'restriction_status' => $messages['inventories']['locked'] ?? 0, // Closed
                            'restriction_arrival' => $messages['inventories']['locked'] ?? 0, // Closed
                            'restriction_departure' => $messages['inventories']['locked'] ?? 0, // Closed
                        ];
                    }

                    $insertInventary[$tokenDate] = [
                        'rate_plan_rooms_id' => $ratesPlanRoom->id,
                        'date' => $dateInput,
                        'day' => substr($dateInput, -2),
                        'inventory_num' => empty($messages['inventories']['inventory_num']) ? 0 : $messages['inventories']['inventory_num'],
                        'locked' => empty($messages['inventories']['locked']) ? 0 : $messages['inventories']['locked'],
                    ];
                }

                // Actualizar inventario
                if (!empty($rateRoomInventory)) {
                    $updateInventary[$tokenDate] = $rateRoomInventory->toArray();

                    if (isset($messages['inventories']['inventory_num'])) {
                        $updateInventary[$tokenDate]['inventory_num'] = $messages['inventories']['inventory_num'];
                    }

                    if (isset($messages['inventories']['locked'])) {
                        $updateInventary[$tokenDate]['locked'] = $messages['inventories']['locked'];
                    }
                }
            }

            if (isset($messages['restrictions'])) {
                $rateRoomCalendary = null;

                if (!empty($RateRoomCalendary)) {
                    // Obtener calendario existente
                    $rateRoomCalendary = $RateRoomCalendary->first(function ($value) use ($dateInput) {
                        return $value->date == $dateInput;
                    });
                }

                // Crear calendario
                if (empty($rateRoomCalendary)) {
                    /*
                    // Recolectar inserciones para ejecutar masivamente fuera del bucle.
                    $insertCalendary[$tokenDate] = [
                        'rates_plans_room_id' => $ratesPlanRoom->id,
                        'date' => $dateInput,
                        'min_length_stay' => empty($messages['restrictions']['min_length_stay']) ? 1 : $messages['restrictions']['min_length_stay'],
                        'max_length_stay' => empty($messages['restrictions']['max_length_stay']) ? 99 : $messages['restrictions']['max_length_stay'],
                        'min_ab_offset' => empty($messages['restrictions']['min_ab_offset']) ? 1 : $messages['restrictions']['min_ab_offset'],
                        'max_ab_offset' => empty($messages['restrictions']['max_ab_offset']) ? 365 : $messages['restrictions']['max_ab_offset'],
                        'restriction_status' => empty($messages['restrictions']['restriction_status']) ? 0 : $messages['restrictions']['restriction_status'],
                        'restriction_arrival' => empty($messages['restrictions']['restriction_arrival']) ? 0 : $messages['restrictions']['restriction_arrival'],
                        'restriction_departure' => empty($messages['restrictions']['restriction_departure']) ? 0 : $messages['restrictions']['restriction_departure'],
                    ];
                    */


                    // Se modifico la logica para insertar en primera instancia todos los calendarios, para controlar las restricciones.
                    DB::table('rates_plans_calendarys')->insert([
                        'rates_plans_room_id' => $ratesPlanRoom->id,
                        'date' => $dateInput,
                        'min_length_stay' => empty($messages['restrictions']['min_length_stay']) ? 1 : $messages['restrictions']['min_length_stay'],
                        'max_length_stay' => empty($messages['restrictions']['max_length_stay']) ? 99 : $messages['restrictions']['max_length_stay'],
                        'min_ab_offset' => empty($messages['restrictions']['min_ab_offset']) ? 1 : $messages['restrictions']['min_ab_offset'],
                        'max_ab_offset' => empty($messages['restrictions']['max_ab_offset']) ? 365 : $messages['restrictions']['max_ab_offset'],
                        'restriction_status' => empty($messages['restrictions']['restriction_status']) ? 0 : $messages['restrictions']['restriction_status'],
                        'restriction_arrival' => empty($messages['restrictions']['restriction_arrival']) ? 0 : $messages['restrictions']['restriction_arrival'],
                        'restriction_departure' => empty($messages['restrictions']['restriction_departure']) ? 0 : $messages['restrictions']['restriction_departure'],
                        'created_at' => Carbon::now(),
                    ]);
                }

                // Actualizar calendario
                if (!empty($rateRoomCalendary)) {
                    $updateCalendary[$tokenDate] = $rateRoomCalendary->toArray();

                    unset($updateCalendary[$tokenDate]['rate_plan_rooms_id']); // Eliminar la clave rate_plan_rooms_id
                    unset($updateCalendary[$tokenDate]['rate']); // Eliminar la clave rate
                    unset($updateCalendary[$tokenDate]['restriction_status']); // Eliminar la clave
                    unset($updateCalendary[$tokenDate]['restriction_arrival']); // Eliminar la clave
                    unset($updateCalendary[$tokenDate]['restriction_departure']); // Eliminar la clave

                    $updateCalendary[$tokenDate]['rates_plans_room_id'] = $ratesPlanRoom->id;

                    if (!empty($messages['restrictions']['min_length_stay'])) {
                        $updateCalendary[$tokenDate]['min_length_stay'] = $messages['restrictions']['min_length_stay'];
                    }
                    if (!empty($messages['restrictions']['max_length_stay'])) {
                        $updateCalendary[$tokenDate]['max_length_stay'] = $messages['restrictions']['max_length_stay'];
                    }
                    if (!empty($messages['restrictions']['min_ab_offset'])) {
                        $updateCalendary[$tokenDate]['min_ab_offset'] = $messages['restrictions']['min_ab_offset'];
                    }
                    if (!empty($messages['restrictions']['max_ab_offset'])) {
                        $updateCalendary[$tokenDate]['max_ab_offset'] = $messages['restrictions']['max_ab_offset'];
                    }
                    if (!empty($messages['restrictions']['restriction_status'])) {
                        $updateCalendary[$tokenDate]['restriction_status'] = $messages['restrictions']['restriction_status'];
                    }
                    if (!empty($messages['restrictions']['restriction_arrival'])) {
                        $updateCalendary[$tokenDate]['restriction_arrival'] = $messages['restrictions']['restriction_arrival'];
                    }
                    if (!empty($messages['restrictions']['restriction_departure'])) {
                        $updateCalendary[$tokenDate]['restriction_departure'] = $messages['restrictions']['restriction_departure'];
                    }
                }
            }
        }

        // ----------------------------------------------
        // DESDE AQUI SOLO TRATAMOS LOS INSERTS Y UPDATES
        // ----------------------------------------------

        // INVENTARIO
        if (count($insertInventary) > 0 && $controlXMLHyperguest["control_xml_disponibilidad"]) {
            // Empujar todos los inserts de inventario deduplicando por tokenDate
            foreach ($insertInventary as $tkn => $insertData) {
                $arrayResultados["inventories"]["inserts"][$tkn] = $insertData; // clave tkn evita duplicados
            }
        }
        if (
            count($updateInventary) > 0 && (
                $controlXMLHyperguest["control_xml_disponibilidad"] ||
                $controlXMLHyperguest["control_xml_estado_booking"] ||
                $controlXMLHyperguest["control_xml_estado_arrival"] ||
                $controlXMLHyperguest["control_xml_estado_departure"]
            )
        ) {
            // Construir updates para todos los tokenDate y deduplicar
            foreach ($updateInventary as $tkn => $invData) {
                $updates = [
                    'id' => $invData['id'],
                ];

                if ($controlXMLHyperguest["control_xml_disponibilidad"] || $controlXMLHyperguest["control_xml_estado_arrival"]) {
                    $updates['locked'] = $invData['locked'];
                }
                if ($controlXMLHyperguest["control_xml_disponibilidad"]) {
                    $updates['inventory_num'] = $invData['inventory_num'];
                }

                if (count($updates) > 1) {
                    $arrayResultados["inventories"]["updates"][] = $updates;
                }
            }
        }

        // CALENDARIO
        /*if (count($insertCalendary) > 0) {
            // Empujar todos los inserts de calendario deduplicando por tokenDate
            foreach ($insertCalendary as $tkn => $insertData) {
                $arrayResultados["calendarys"]["inserts"][$tkn] = $insertData;
            }
        }*/
        if (count($updateCalendary) > 0 && (
                $controlXMLHyperguest["control_xml_disponibilidad"] ||
                $controlXMLHyperguest["control_xml_minimo_noches"] ||
                $controlXMLHyperguest["control_xml_estado_booking"] ||
                $controlXMLHyperguest["control_xml_estado_arrival"] ||
                $controlXMLHyperguest["control_xml_estado_departure"]
            )
        ) {
            // Construir updates para todos los tokenDate
            foreach ($updateCalendary as $tkn => $calData) {
                $updates = [
                    'id' => $calData['id'],
                ];

                if ($controlXMLHyperguest["control_xml_minimo_noches"]) {
                    $updates['min_length_stay'] = $calData['min_length_stay'];
                    $updates['max_length_stay'] = $calData['max_length_stay'];
                    $updates['min_ab_offset'] = $calData['min_ab_offset'];
                    $updates['max_ab_offset'] = $calData['max_ab_offset'];
                }
                if ($controlXMLHyperguest["control_xml_estado_booking"]) {
                    $updates['restriction_status'] = $calData['restriction_status'] ?? 0;
                }
                if ($controlXMLHyperguest["control_xml_estado_arrival"]) {
                    $updates['restriction_arrival'] = $calData['restriction_arrival'] ?? 0;
                }
                if ($controlXMLHyperguest["control_xml_estado_departure"]) {
                    $updates['restriction_departure'] = $calData['restriction_departure'] ?? 0;
                }

                if (count($updates) > 1) {
                    $arrayResultados["calendarys"]["updates"][] = $updates;
                }
            }
        }

        // Reindex inserts para mantener compatibilidad con los consumidores que esperan [0]
        if (!empty($arrayResultados["inventories"]["inserts"])) {
            $arrayResultados["inventories"]["inserts"] = array_values($arrayResultados["inventories"]["inserts"]);
        }
        if (!empty($arrayResultados["calendarys"]["inserts"])) {
            $arrayResultados["calendarys"]["inserts"] = array_values($arrayResultados["calendarys"]["inserts"]);
        }

        $arrayResultados["isNotBreakContinue"] = true; // True
        return $arrayResultados;
    }
    /* CARGAR TARIFAS */

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function OTA_HotelRateAmountNotifRQ(): SimpleXMLElement
    {
        $this->setRequestedHotel($this->getXMLRequest()->RateAmountMessages);
        $this->setAvailableRooms();

        $arrayUpdates = [
            "rates_plans_calendarys" => [
                "inserts" => [],
                "updates" => [],
            ],
            "rates" => [
                "inserts" => [],
                "updates" => [],
            ],
        ];
        $arrayUpdates = $this->OTA_HotelRateAmountNotifRQ_GetUpdates($arrayUpdates);

        // Si hay cambios, encolamos el proceso
        if (
            count($arrayUpdates["rates"]["inserts"]) > 0 ||
            count($arrayUpdates["rates"]["updates"]) > 0
        ) {
            dispatch(new ProcessRatePushHyperguest($arrayUpdates))->onQueue("rate_hyperguest_push");
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }

    /**
     * @throws Exception
     */
    public function OTA_HotelRateAmountNotifRQ_GetUpdates(array $arrayUpdates): array
    {
        $RateAmountMessages = $this->getXMLRequest()->RateAmountMessages->RateAmountMessage;

        /*
            Se valida si existen fecha inicio,fecha fin,codigo de tarifa,codigo de habitacion, codigo de hotel
            Se valida si los registros que existen de calendario poseen politica de cancelacion y politica de tarifa
            Si no poseen se actualizan los registros
            Se crean los registros de calendario nuevos en caso no existan
        */

        foreach ($RateAmountMessages as $RateAmountMessage) {
            if (!isset($RateAmountMessage->StatusApplicationControl))
                continue;

            $attrStart = $RateAmountMessage->StatusApplicationControl->attributes()->Start->__toString();
            $attrEnd = $RateAmountMessage->StatusApplicationControl->attributes()->End->__toString();
            $attrRatePlanCode = $RateAmountMessage->StatusApplicationControl->attributes()->RatePlanCode->__toString();
            $attrInvTypeCode = $RateAmountMessage->StatusApplicationControl->attributes()->InvTypeCode->__toString();

            if (!isset($attrStart) || !isset($attrEnd) || !isset($attrRatePlanCode) || !isset($attrInvTypeCode)) {
                continue;
            }

            $this->setDateRange($attrStart, $attrEnd);

            $rooms = $this->getAvailableRooms()->filter(function ($query) use ($attrInvTypeCode) {
                return $query->channels[0]->pivot->code == $attrInvTypeCode;
            });

            foreach ($rooms as $room) {
                $arrayResultados = $this->OTA_HotelRateAmountNotifRQ_Rates_RatesPlansCalendarys($room, null, null, $attrRatePlanCode, null);
                if (!$arrayResultados["isNotBreakContinue"])
                    continue;

                // Agregar objeto a arreglo de updates o inserts, para actualizar o crear masivamente
                if (count($arrayResultados["rates_plans_calendarys"]["inserts"]) > 0)
                    $arrayUpdates["rates_plans_calendarys"]["inserts"][] = $arrayResultados["rates_plans_calendarys"]["inserts"][0];
                if (count($arrayResultados["rates_plans_calendarys"]["updates"]) > 0)
                    $arrayUpdates["rates_plans_calendarys"]["updates"][] = $arrayResultados["rates_plans_calendarys"]["updates"][0];
            }

            if (count($arrayUpdates["rates_plans_calendarys"]["inserts"]) > 0) {
                $inserts = $arrayUpdates["rates_plans_calendarys"]["inserts"];
                $this->bulkInsertRaw(
                    'rates_plans_calendarys',
                    ['rates_plans_room_id', 'date', 'policies_rate_id', 'policies_cancelation_id'],
                    $inserts,
                    500
                );
            }
            if (count($arrayUpdates["rates_plans_calendarys"]["updates"]) > 0) {
                $updates = $arrayUpdates["rates_plans_calendarys"]["updates"];
                $this->bulkUpdateCase(
                    'rates_plans_calendarys',
                    $updates,
                    ['policies_rate_id', 'policies_cancelation_id'],
                    500
                );
            }

            $this->setAvailableRooms();
            $arrayUpdates["rates_plans_calendarys"]["inserts"] = [];
            $arrayUpdates["rates_plans_calendarys"]["updates"] = [];

            foreach ($rooms as $room) {
                $arrayResultados = $this->OTA_HotelRateAmountNotifRQ_Rates_RatesPlansCalendarys($room, $attrStart, $attrEnd, $attrRatePlanCode, $RateAmountMessage);
                if (!$arrayResultados["isNotBreakContinue"])
                    continue;

                // Agregar objeto a arreglo de updates o inserts, para actualizar o crear masivamente
                if (count($arrayResultados["rates"]["inserts"]) > 0)
                    $arrayUpdates["rates"]["inserts"] = array_merge($arrayUpdates["rates"]["inserts"], $arrayResultados["rates"]["inserts"]);
                if (count($arrayResultados["rates"]["updates"]) > 0)
                    $arrayUpdates["rates"]["updates"] = array_merge($arrayUpdates["rates"]["updates"], $arrayResultados["rates"]["updates"]);
            }
        }

        return $arrayUpdates;
    }

    /**
     * @param $room
     * @param $attrStart
     * @param $attrEnd
     * @param $attrRatePlanCode
     * @param $RateAmountMessage
     * @return array
     */
    public function OTA_HotelRateAmountNotifRQ_Rates_RatesPlansCalendarys($room, $attrStart, $attrEnd, $attrRatePlanCode, $RateAmountMessage): array
    {
        $arrayResultados = [
            "isNotBreakContinue" => false,
            "rates_plans_calendarys" => [
                "inserts" => [],
                "updates" => [],
            ],
            "rates" => [
                "inserts" => [],
                "updates" => [],
            ],
        ];
        $AllRatesPlanRoom = $room->rates_plan_room; // Todas las tarifas planes por habitación

        if (empty($room) or empty($AllRatesPlanRoom)) {
            return $arrayResultados; // False
        }

        // Obtener una tarifa plan por habitación
        $ratesPlanRoom = $AllRatesPlanRoom->first(function ($query) use ($attrRatePlanCode) {
            return $query->rate_plan->code == $attrRatePlanCode;
        });

        if (empty($ratesPlanRoom)) {
            return $arrayResultados; // False
        }

        // Alex 2
        $rates = [];
        if (isset($RateAmountMessage)) {
            if (isset($RateAmountMessage->StatusApplicationControl->Rates->Rate->BaseByGuestAmts)) {
                $BaseByGuestAmts = $RateAmountMessage->StatusApplicationControl->Rates->Rate->BaseByGuestAmts;

                // Obtener tarifas desde el XML
                if (isset($BaseByGuestAmts->BaseByGuestAmt)) {
                    // Tarifas Adulto o Niño
                    foreach ($BaseByGuestAmts->BaseByGuestAmt as $BaseByGuestAmt) {
                        $attrAgeQualifyingCode = $BaseByGuestAmt->attributes()->AgeQualifyingCode->__toString();
                        $attrNumberOfGuests = $BaseByGuestAmt->attributes()->NumberOfGuests->__toString();
                        $attrAmountAfterTax = $BaseByGuestAmt->attributes()->AmountAfterTax->__toString();

                        $edad = (int)$attrAgeQualifyingCode;

                        if ($edad >= 10) {
                            // Tarifa por adulto >= 10 años
                            $rates['by_adult'][] = [
                                'num_adult' => (int)$attrNumberOfGuests,
                                'price_adult' => (float)$attrAmountAfterTax,
                            ];
                        } else {
                            // Tarifa por niño o infante < 10 años
                            $rates['by_child'][] = [
                                'num_child' => (int)$attrNumberOfGuests,
                                'price_child' => (float)$attrAmountAfterTax,
                            ];
                        }
                    }
                } else {
                    // Tarifa por habitación
                    $attrAmountAfterTax = $BaseByGuestAmts->attributes()->AmountAfterTax->__toString();
                    $rates['by_room'][] = [
                        'price_total' => (float)$attrAmountAfterTax
                    ];
                }
            }
        }
        // Alex 2

        $ratePlanRoomId = $ratesPlanRoom->id;
        $insertsRatesPlansCalendarys = [];
        $updatesRatesPlansCalendarys = [];
        $insertsRates = [];
        $updatesRates = [];
        $tokenDate = null;

        // Alex 2
        if (isset($RateAmountMessage)) {
            $ratePlanRoomCalendarys = $this->getRatePlanRoomRates($ratesPlanRoom, ['Start' => $attrStart, 'End' => $attrEnd]);
        }
        // Alex 2

        foreach ($this->getDateRange() as $date) {
            $dateInput = $date->format("Y-m-d");
            $tokenDate = "$dateInput|$ratePlanRoomId";

            $cancellation_policy_id = DB::table('policies_cancelations_rates_plans_rooms')->where('rates_plans_rooms_id', $ratePlanRoomId)->first()->policies_cancelation_id;
            if (is_null($cancellation_policy_id)) {
                continue;
            }

            $rate_policy_id = DB::table('policies_cancelations_policies_rates')->where('policies_cancelation_id', $cancellation_policy_id)->first()->policies_rates_id;
            if (is_null($rate_policy_id)) {
                continue;
            }

            // Si esta vacio el calendario
            $inventoriDate = $ratesPlanRoom->calendarys->first(function ($query) use ($dateInput) {
                return $query->date == $dateInput;
            });

            if (!empty($ratesPlanRoom->calendarys)) {
                // Insertar o actualizar
                if (empty($inventoriDate)) {
                    // Insertar
                    $insertsRatesPlansCalendarys[$tokenDate] = [
                        'rates_plans_room_id' => $ratePlanRoomId,
                        'date' => $dateInput,
                        'policies_rate_id' => $rate_policy_id,
                        'policies_cancelation_id' => $cancellation_policy_id,
                    ];
                } else {
                    // Actualizar
                    $updatesRatesPlansCalendarys[$tokenDate] = [
                        'id' => $inventoriDate->id,
                        'policies_rate_id' => $rate_policy_id,
                        'policies_cancelation_id' => $cancellation_policy_id,
                    ];
                }
            } else {
                // Actualizar
                // Si las politicas son nulas
                if (is_null($inventoriDate->policies_rate_id) || is_null($inventoriDate->policies_cancelation_id)) {
                    $updatesRatesPlansCalendarys[$tokenDate] = [
                        'id' => $inventoriDate->id,
                        'policies_rate_id' => $rate_policy_id,
                        'policies_cancelation_id' => $cancellation_policy_id,
                    ];
                }
            }


            // Alex 2
            if (isset($RateAmountMessage)) {
                $inventoriDate = $ratePlanRoomCalendarys->first(function ($query) use ($dateInput) {
                    return $query->date == $dateInput;
                });
                $rateSelected = null;

                foreach ($rates as $rateType => $ratesType) {
                    $rateSelected = null;

                    foreach ($ratesType as $rate) {

                        if ($inventoriDate->rate->count() > 0) {
                            // Validar si existe tarifa
                            $rateSelected = $inventoriDate->rate->first(function ($value) use ($rateType, $rate) {
                                if ($rateType == 'by_adult') {
                                    return $value->num_adult == $rate['num_adult'];
                                } elseif ($rateType == 'by_child') {
                                    return $value->num_child == $rate['num_child'];
                                } elseif ($rateType == 'extra') { // No aplica para Hyperguest
                                    return !empty($value->price_extra) && $value->price_extra > 0;
                                } elseif ($rateType == 'by_room') {
                                    return !empty($value->price_total) && $value->price_total > 0;
                                } else {
                                    return false;
                                }
                            });
                        } else {
                            $rateSelected = null;
                        }

                        $tokenRate = "$rateType|$inventoriDate->id";

                        if (empty($rateSelected)) {

                            /* SI LA TARIFA, NO EXISTE */

                            switch ($rateType) {
                                case 'by_adult':
                                    // Insertar
                                    $insertsRates[$tokenRate] = [
                                        'rates_plans_calendarys_id' => $inventoriDate->id,
                                        'num_adult' => $rate['num_adult'],
                                        'num_child' => 0,
                                        'num_infant' => 0,
                                        'price_adult' => $rate['price_adult'],
                                        'price_child' => 0,
                                        'price_infant' => 0,
                                        'price_extra' => 0,
                                        'price_total' => 0,
                                    ];
                                    break;
                                case 'by_child':
                                    // Insertar
                                    $insertsRates[$tokenRate] = [
                                        'rates_plans_calendarys_id' => $inventoriDate->id,
                                        'num_adult' => 0,
                                        'num_child' => $rate['num_child'],
                                        'num_infant' => 0,
                                        'price_adult' => 0,
                                        'price_child' => $rate['price_child'],
                                        'price_infant' => 0,
                                        'price_extra' => 0,
                                        'price_total' => 0,
                                    ];
                                    break;
                                case 'extra':
                                    // Insertar
                                    $insertsRates[$tokenRate] = [
                                        'rates_plans_calendarys_id' => $inventoriDate->id,
                                        'num_adult' => 0,
                                        'num_child' => 0,
                                        'num_infant' => 0,
                                        'price_adult' => 0,
                                        'price_child' => 0,
                                        'price_infant' => 0,
                                        'price_extra' => $rate['price_extra'],
                                        'price_total' => 0,
                                    ];
                                    break;
                                case 'by_room':
                                    // Insertar
                                    $insertsRates[$tokenRate] = [
                                        'rates_plans_calendarys_id' => $inventoriDate->id,
                                        'num_adult' => 0,
                                        'num_child' => 0,
                                        'num_infant' => 0,
                                        'price_adult' => 0,
                                        'price_child' => 0,
                                        'price_infant' => 0,
                                        'price_extra' => 0,
                                        'price_total' => $rate['price_total'],
                                    ];
                                    break;
                            }

                            if (count($insertsRates[$tokenRate]) > 0)
                                $arrayResultados["rates"]["inserts"][] = $insertsRates[$tokenRate];
                        } else {

                            /* SI LA TARIFA, EXISTE */

                            switch ($rateType) {
                                case 'by_adult':
                                    // Actualizar
                                    $updatesRates[$tokenRate] = [
                                        'id' => $rateSelected->id,
                                        'num_adult' => $rate['num_adult'],
                                        'price_adult' => $rate['price_adult'],
                                    ];
                                    break;
                                case 'by_child':
                                    // Actualizar
                                    $updatesRates[$tokenRate] = [
                                        'id' => $rateSelected->id,
                                        'num_child' => $rate['num_child'],
                                        'price_child' => $rate['price_child'],
                                    ];
                                    break;
                                case 'extra':
                                    // Actualizar
                                    $updatesRates[$tokenRate] = [
                                        'id' => $rateSelected->id,
                                        'price_extra' => $rate['price_extra'],
                                    ];
                                    break;
                                case 'by_room':
                                    // Actualizar
                                    $updatesRates[$tokenRate] = [
                                        'id' => $rateSelected->id,
                                        'price_total' => $rate['price_total'],
                                    ];
                                    break;
                            }

                            if (count($updatesRates[$tokenRate]) > 0)
                                $arrayResultados["rates"]["updates"][] = $updatesRates[$tokenRate];
                        }
                    }
                }
            }
            // Alex 2
        }

        if (count($insertsRatesPlansCalendarys) > 0) {
            $arrayResultados["rates_plans_calendarys"]["inserts"][] = $insertsRatesPlansCalendarys[$tokenDate];
        }
        if (count($updatesRatesPlansCalendarys) > 0) {
            $arrayResultados["rates_plans_calendarys"]["updates"][] = $updatesRatesPlansCalendarys[$tokenDate];
        }

        $arrayResultados["isNotBreakContinue"] = true; // True
        return $arrayResultados;
    }
}
