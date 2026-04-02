<?php

namespace App\Http\Hyperguest\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Hyperguest\Traits\Hyperguest;
use App\Jobs\ProcessHyperguestInbound;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HyperguestController extends Controller
{
    use Hyperguest;

    const LIMATOURS_ID = 'LIMATOURS';    // if supply by the conection
    const CONECTOR_TYPE = 1;    // 1 METHOD PUSH
    const WSDL = "http://schemas.xmlsoap.org/wsdl/";
    const SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    const WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis200401-wss-wssecurity-secext-1.0.xsd";
    const WSA = "http://schemas.xmlsoap.org/ws/2004/08/addressing";
    const OTA = "http://www.opentravel.org/OTA/2003/05";
    const TNS = "http://127.0.0.1:8000/api/hyperguest";
    const XS = "http://www.w3.org/2001/XMLSchema";
    const XSI = "http://www.w3.org/2001/XMLSchema-instance";
    const SYSTEM_CORRENTLY_UNAVAILABLE = 187;    // System currently unavailable
    const INVALID_PROPERTY_CODE = 400;    // Invalid property code Hotel code
    const SYSTEM_ERROR = 448;    // System error
    const UNABLE_TO_PROCESS = 450;    // Unable to process

    const INVALID_RATE_CODE = 249;    // Invalid rate code
    const REQUIRED_FIELD_MISSING = 321;    // Required field missing
    const HOTEL_NOT_ACTIVE = 375;    // Hotel not active
    const INVALID_HOTEL_CODE = 392;    // Invalid hotel code
    const INVALID_ROOM_TYPE = 402;    // Invalid room type
    const RATE_DOES_NOT_EXIST = 436;    // Rate does not exist
    const ROOM_OR_RATE_NOT_FOUND = 783;    // Room or rate not found
    const RATE_NOT_LOADED = 842;    // Rate not loaded
    const AUTHENTICATION = 4;
    const CONECTOR_CODE = 'HYPERGUEST';

    /**
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function index(Request $request)
    {
        $rawXml = $request->getContent();
        $this->setXMLRequest($rawXml);
        try {
            $method = $this->requestedMethodName();
        } catch (\Throwable $e) {
        }
        try {
            $echoToken = $this->extractEchoToken($rawXml);
            $messageId = $this->extractMessageId($rawXml);
            $hotelCode = $this->extractHotelCode($rawXml);
            $idempotencyKey = sha1($method . '|' . $hotelCode . '|' . $echoToken . '|' . sha1($rawXml));

            $inboundId = DB::table('hyperguest_inbounds')->insertGetId([
                'method'          => $method,
                'hotel_code'      => $hotelCode,
                'echo_token'      => $echoToken,
                'message_id'      => $messageId,
                'idempotency_key' => $idempotencyKey,
                'status'          => 'queued',
                'payload_xml'     => $rawXml,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            dispatch((new ProcessHyperguestInbound($inboundId))->onQueue('hyperguest_inbound'));
        } catch (\Exception $e) {
            // Si es duplicado, ignorar (idempotencia)
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
            }
        }

        return response(
            $this->successXml()->asXML(),
            200,
            ['Content-Type' => 'application/xml']
        );
    }
}
