<?php

namespace App\Http\Hyperguest\Traits;

use App\IntegrationHyperguest;
use App\RatesPlansRooms;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use App\Http\Traits\Translations;
use App\Http\Traits\HyperguestGeneral;
use App\Http\Traits\ChannelLogs;

trait Hyperguest
{
    use Translations;
    use ChannelLogs;
    use HyperguestGeneral;

    public $OTA = "http://www.opentravel.org/OTA/2003/05";
    public $HYPERGUEST_RES_URI = '#';
    //https://pushres.ezyield.com/cgi-bin/EZYieldPushInterface.cgi?requestor=limatours-otac

    public $BookingChannelType = "7";
    public $CompanyName = "Limatours";
    public $CompanyCode = "LMT";

    /**
     * @return SimpleXMLElement
     */
    private function setXMLResponse()
    {
        if (!$this->responseMethodName()) {
            $xml = new SimpleXMLElement('<OTA_ErrorRS/>');
        } else {
            $xml = new SimpleXMLElement('<' . $this->responseMethodName() . '/>');
        }

        $xml->addAttribute('xmlns', self::OTA);
        $xml->addAttribute('xmlns:xmlns:xsi', self::XSI);
        $xml->addAttribute('Version', $this->getVersion());
        $xml->addAttribute('TimeStamp', date('c'));
        $xml->addAttribute('EchoToken', $this->getEchoToken());

        $this->XMLResponse = $xml;

        return $this->XMLResponse;
    }

    private function setAvailableRooms()
    {
        $this->availableRooms = $this->hotel()
            ->rooms()
            ->select(['id',
//                'max_capacity',
//                'min_adults',
//                'max_adults',
//                'max_child',
//                'max_infants',
//                'min_inventory',
                'state',
//                'see_in_rates',
                'hotel_id',
//                'room_type_id',
//                'order',
//                'inventory',
//                'bed_additional',
//                'ignore_rate_child',
            ])
            ->whereHas('channels', function ($query) {
                $query->where('channels.code', '=', self::CONECTOR_CODE);
            })
            ->with([
                'channels' => function ($query) {
                    $query->select(['channels.id', 'channels.name', 'channels.code']);
                    $query->where('channels.code', '=', self::CONECTOR_CODE);
                },
//                'translations' => function ($query) {
//                    $query->select(['object_id', 'value','slug', 'language_id']);
//                    $query->whereHas('language', function ($query) {
//                        $query->where('iso', '=', 'en');
//                    });
//                },
                'rates_plan_room' => function ($query) {
                    $query->select([
                        'id',
                        'rates_plans_id',
                        'room_id',
                        'status',
                        'bag',
                        'channel_id',
//                        'channel_infant_price',
//                        'channel_child_price'
                    ]);
                    $query->whereHas('channel', function ($query) {
                        $query->where('code', '=', self::CONECTOR_CODE);
                    });
                    $query->with([
                        'rate_plan' => function ($query) {
                            $query->select([
                                'id',
                                'code',
                                'name',
//                                'allotment',
//                                'taxes',
//                                'services',
//                                'timeshares',
//                                'promotions',
                                'status',
//                                'meal_id',
                                'rates_plans_type_id',
//                                'charge_type_id',
                                'hotel_id',
//                                'price_dynamic',
                                'rate_plan_code',
                                'channel_id',
//                                'promotion_from',
//                                'promotion_to',
//                                'rate',
//                                'no_show',
//                                'day_use',
//                                'flag_process_markup',
                            ]);
//                            $query->with([
//                                'translations' => function ($query) {
//                                    $query->select(['object_id', 'value','slug', 'language_id']);
//                                    $query->whereHas('language', function ($query) {
//                                        $query->where('iso', '=', 'en');
//                                    });
//                                },
//                            ]);
                        }
                    ]);
                },
            ])
            ->get();

//        dd($this->availableRooms->toArray());
    }

    private function setAvailableRoomsBK($date = null)
    {
        $this->availableRooms = $this->hotel()->rooms()
            ->whereHas('channels', function ($query) {
                $query->where('channels.code', '=', self::CONECTOR_CODE);
                $query->where('channel_room.type', '=', self::CONECTOR_TYPE);
            })
            ->with([
                'channels' => function ($query) {
                    $query->where('channels.code', '=', self::CONECTOR_CODE);
                },
                'translations' => function ($query) {
                    $query->whereHas('language', function ($query) {
                        $query->where('iso', '=', 'en');
                    });
                },
                'rates_plan_room' => function ($query) use ($date) {
                    $query->whereHas('channel', function ($query) {
                        $query->where('code', '=', self::CONECTOR_CODE);
                    });
                    $query->with([
                        'rate_plan' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->whereHas('language', function ($query) {
                                        $query->where('iso', '=', 'en');
                                    });
                                },
                            ]);
                        }
                    ]);
                },
            ])
            ->get();
    }

    /**
     * @param RatesPlansRooms $ratePlanRoom
     * @param null $dates
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRatePlanRoomRates(RatesPlansRooms $ratePlanRoom, $dates = null)
    {
        $select = $ratePlanRoom->calendarys();
        if (!empty($dates)) {
            if (is_array($dates)) {
                if (count($dates) == 2 and isset($dates['Start']) and isset($dates['End'])) {
                    $select->whereBetween('date', [$dates['Start'], $dates['End']]);
                } else {
                    $select->whereIn('date', $dates);
                }
            } else {
                $select->whereIn('date', '=', $dates);
            }
        }
        $select->with([
            'rate' => function ($query) {
            }
        ]);

        return $select->get();
    }

    /**
     * @param RatesPlansRooms $ratePlanRoom
     * @param null $dates
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRatePlanRoomInventories(RatesPlansRooms $ratePlanRoom, $dates = null)
    {
        $select = $ratePlanRoom->inventories();
        if ($dates) {
            if (is_array($dates)) {
                if (count($dates) == 2 and isset($dates['Start']) and isset($dates['End'])) {
                    $select->whereBetween('date', [$dates['Start'], $dates['End']]);
                } else {
                    $select->whereIn('date', $dates);
                }
            } else {
                $select->whereIn('date', '=', $dates);
            }
        }

        return $select->get();
    }

    /**
     * @return Collection
     */
    private function getAvailableRooms()
    {
        return $this->availableRooms;
    }

    private function setAvailableRatesPlans()
    {
        $this->availableRatesPlans = $this->hotel()->rates_plans()
            ->whereHas('rooms.channels', function ($query) {
                $query->where('channels.code', '=', self::CONECTOR_CODE);
                $query->whereNotNull('channel_room.code');
            })
            ->with([
                'translations' => function ($query) {
                    $query->whereHas('language', function ($query) {
                        $query->where('iso', '=', 'en');
                    });
                },
                'rooms' => function ($query) {
                    $query->whereHas('channels', function ($query) {
                        $query->where('channels.code', '=', self::CONECTOR_CODE);
                        $query->whereNotNull('channel_room.code');
                    });
                    $query->wherePivot('channel_id', $this->channelId());
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                        'channels' => function ($query) {
                            $query->where('channels.code', '=', self::CONECTOR_CODE);
                            $query->whereNotNull('channel_room.code');
                        },
                    ]);
                },
            ])
            ->get();
    }

    /**
     * @return Collection
     */
    private function getAvailableRatesPlans()
    {
        return $this->availableRatesPlans;
    }

    public function request(array $headers = [], $content = null)
    {
        $request = new \Illuminate\Http\Request([], [], [], [], [], $_SERVER, $content);
        $request->headers->replace($headers);
        // TODO arreglar que la variable $_SERVER no funciona
        // como somos quien hace el request somo el REMOTE_ADDR de la ip
        if (!isset($_SERVER["REMOTE_ADDR"])) {
            $address = "127.0.0.1";
        } else {
            $address = $_SERVER["REMOTE_ADDR"];
        }
        $request->server->add(['REMOTE_ADDR', $address]);

        return $request;
    }

    private function getHeaders($headerText)
    {
        $headers = array();
        foreach (explode("\r\n", $headerText) as $i => $line) {
            if (trim($line) == '') {
                continue;
            }

            if (strpos($line, "HTTP/") !== false) {
                $headers['http_code'] = trim($line);
            } else {
                //some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
                list ($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }

    /**
     * Create, modify and cancel of reservations.
     *
     * @param $hotelRes
     * @param bool $isCertificationTest
     * @return array
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelResNotifRQ(array $hotelRes)
    {
        $channel_reservation_code = $hotelRes['reservation_code'];
        $success = true;
        $error = "";
        return [
            'success' => $success,
            'error' => $error,
            'channel_reservation_code' => $channel_reservation_code
        ];
    }

// @codingStandardsIgnoreLine
    public function OTA_CancelRQ($hotelRes)
    {
        $this->setChannel('HYPERGUEST');

        $reservation_request = [
            "bookingId" => $hotelRes["channel_reservation_code"],
            "reason" => "Cancel Hyperguest",
            "simulation" => false
        ];

        $success   = true;
        $error     = "";
        $httpCode  = null;
        $alreadyCancelled = false;
        $data      = null;
        $error_msg = "";

        try {
            $integration = IntegrationHyperguest::first();
            if (!$integration || empty($integration->token)) {
                return [
                    "success" => false,
                    "error"   => "Hyperguest token no configurado.",
                    "channel_reservation_code" => $hotelRes["channel_reservation_code"],
                ];
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://book-api.hyperguest.com/2.0/booking/cancel');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reservation_request));
            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$integration->token}",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Timeouts para evitar cuelgues
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $raw = curl_exec($ch);

            if (curl_errno($ch)) {
                $success   = false;
                $error_msg = curl_error($ch);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($raw, true);

            // Si JSON inválido pero hubo HTTP 200/4xx/5xx, registra el cuerpo crudo
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                $success = false;
                $error   = "Respuesta no válida de Hyperguest: {$raw}";
            }

            // Manejo de error conocido: reserva ya cancelada
            if (isset($data['error'])) {
                $msg  = isset($data['error']) ? (string)$data['error'] : '';
                $code = isset($data['errorCode']) ? (string)$data['errorCode'] : '';

                if ($code === 'BN.500' && stripos($msg, 'only confirmed bookings can be cancelled') !== false) {
                    $success = true;
                    $error   = ""; // no lo tratamos como error
                    $alreadyCancelled = true;
                } else {
                    $success = false;
                    $error   = json_encode($data["error"]) . ' ' .
                        json_encode($data["errorDetails"] ?? []) . ' ' .
                        $error_msg;
                }
            }

            // Log técnico (request/response)
            $this->putXmlLogHyperguest(
                json_encode($reservation_request),
                json_encode($data),
                $this->getChannel(),
                'OTA_HotelResCancelNotifRQ',
                $hotelRes["channel_reservation_code"],
                $success
            );

            return [
                "success" => $success,
                "error"   => $error,
                "already_cancelled" => $alreadyCancelled,
                "http_code" => $httpCode,
                "channel_reservation_code" => $hotelRes["channel_reservation_code"],
            ];

        } catch (Exception $e) {
            // Atrapamos cualquier excepción y la registramos
            try {
                $this->putXmlLogHyperguest(
                    json_encode($reservation_request),
                    isset($data) ? json_encode($data) : null,
                    $this->getChannel(),
                    'OTA_HotelResCancelNotifRQ',
                    $hotelRes["channel_reservation_code"] ?? null,
                    false
                );
            } catch (\Throwable $inner) {
                // evitar que el log generado también lance
            }

            return [
                "success" => false,
                "error"   => "Excepción en cancelación: " . $e->getMessage(),
                "channel_reservation_code" => $hotelRes["channel_reservation_code"] ?? null,
            ];
        }
    }
    public function OTA_CancelRQBackup($hotelRes)
    {
        $this->setChannel('HYPERGUEST');

        $reservation_request = [
            "bookingId" => $hotelRes["channel_reservation_code"],
            "reason" => "Cancel Hyperguest",
            "simulation" => false
        ];
        try {
            $success = true;
            $error = "";
            $integration = IntegrationHyperguest::first();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://book-api.hyperguest.com/2.0/booking/cancel');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reservation_request));
            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$integration->token}",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $data = curl_exec($ch);
            $error_msg = "";
            if (curl_errno($ch)) {
                $success = false;
                $error_msg = curl_error($ch);
            }
            curl_close($ch);

            $data = json_decode($data, true);

            if (isset($data['error'])) {
                $success = false;
                $error = json_encode($data["error"]) . ' ' . json_encode($data["errorDetails"]) . ' ' . $error_msg;
            }

            $this->putXmlLogHyperguest(json_encode($reservation_request), json_encode($data), $this->getChannel(),
                'OTA_HotelResCancelNotifRQ', $hotelRes["channel_reservation_code"], $success);

            if (!$success) {
                return [
                    "success" => $success,
                    "error" => $error,
                    'channel_reservation_code' => $hotelRes["channel_reservation_code"]
                ];
            } else {
                return [
                    "success" => $success,
                    "error" => $error,
                    'channel_reservation_code' => $hotelRes["channel_reservation_code"]
                ];
            }
        } catch (Exception $e) {
        }
    }

    /** MessageID (si existiera en el Header SOAP). */
    public function extractMessageId(string $xml): ?string
    {
        try {
            $obj = new \SimpleXMLElement($xml);
            $nodes = $obj->xpath('//*[local-name()="MessageID"]');
            return (!empty($nodes)) ? trim((string) $nodes[0]) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** EchoToken del RQ (atributo del root OTA dentro del Body). */
    public function extractEchoToken(string $xml): ?string
    {
        try {
            $obj = new \SimpleXMLElement($xml);
            // Buscar atributo EchoToken en cualquier nodo
            $nodes = $obj->xpath('//@EchoToken');
            return (!empty($nodes)) ? (string) $nodes[0] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Version del RQ (útil para setear en las respuestas). */
    public function extractVersion(string $xml): ?string
    {
        try {
            $obj = new \SimpleXMLElement($xml);
            $nodes = $obj->xpath('//@Version');
            return (!empty($nodes)) ? (string) $nodes[0] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** TimeStamp del RQ. */
    public function extractTimeStamp(string $xml): ?string
    {
        try {
            $obj = new \SimpleXMLElement($xml);
            $nodes = $obj->xpath('//@TimeStamp');
            return (!empty($nodes)) ? (string) $nodes[0] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** HotelCode (atributo sobre <AvailStatusMessages>). */
    public function extractHotelCode(string $xml): ?string
    {
        try {
            $obj = new \SimpleXMLElement($xml);
            // Caso específico del ejemplo: <AvailStatusMessages HotelCode="28985">
            $nodes = $obj->xpath('//*[local-name()="AvailStatusMessages"]/@HotelCode');
            if (!empty($nodes)) return (string) $nodes[0];

            // Alternativa: cualquier atributo HotelCode
            $nodes = $obj->xpath('//@HotelCode');
            if (!empty($nodes)) return (string) $nodes[0];

            // Último intento: nodo <HotelCode>
            $nodes = $obj->xpath('//*[local-name()="HotelCode"]');
            if (!empty($nodes)) return trim((string) $nodes[0]);

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Respuesta OTA de éxito (ACK). Deduce el RS desde el RQ.
     * Devuelve SimpleXMLElement listo para ->asXML()
     */
    public function successXml(): \SimpleXMLElement
    {
        $rq = $this->requestedMethodName($this->XMLRequest ?? '');
        $rs = $rq ? preg_replace('/RQ$/', 'RS', $rq) : 'OTA_SuccessRS';

        $xml = new \SimpleXMLElement('<' . $rs . '/>');
        $xml->addAttribute('xmlns', $this->OTA ?? 'http://www.opentravel.org/OTA/2003/05');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('Version', $this->extractVersion($this->XMLRequest ?? '') ?: '1.0');
        $xml->addAttribute('TimeStamp', gmdate('c'));

        // Si existe EchoToken en el RQ, lo reflejamos
        $et = $this->extractEchoToken($this->XMLRequest ?? '');
        if ($et) $xml->addAttribute('EchoToken', $et);

        $xml->addChild('Success');
        return $xml;
    }

    /**
     * Respuesta OTA de error genérica.
     */
    public function errorXml($code, string $message): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement('<OTA_ErrorRS/>');
        $xml->addAttribute('xmlns', $this->OTA ?? 'http://www.opentravel.org/OTA/2003/05');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('Version', $this->extractVersion($this->XMLRequest ?? '') ?: '1.0');
        $xml->addAttribute('TimeStamp', gmdate('c'));

        $et = $this->extractEchoToken($this->XMLRequest ?? '');
        if ($et) $xml->addAttribute('EchoToken', $et);

        $errors = $xml->addChild('Errors');
        $err = $errors->addChild('Error', htmlspecialchars($message, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        $err->addAttribute('Type', (string) $code);

        return $xml;
    }

}
