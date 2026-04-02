<?php

namespace App\Http\Siteminder\Controller;

use App\Http\Controllers\Controller;
use App\Http\Erevmax\OTA_HotelAvailGetRQ;
use App\Http\Siteminder\OTA_HotelAvailNotifRQ;
use App\Http\Siteminder\OTA_HotelAvailRQ;
use App\Http\Siteminder\OTA_HotelRateAmountNotifRQ;
use App\Http\Siteminder\Traits\Siteminder;
use App\Http\Traits\Channel;
use DateTime;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use SimpleXMLElement;

class SiteminderController extends Controller
{
    use Siteminder;
//    use Channel;

    const CONECTOR_CODE = 'SITEMINDER';
    const WSDL_PATH = __dir__ . '/../services/';
    const SCHEMAS_PATH = __dir__ . '/../schema/';
    const WSDL_URI = 'http://127.0.0.1:8000/api/siteminder/services/siteconnect_v1.1.0.wsdl';
    const WSDL = "http://schemas.xmlsoap.org/wsdl/";
    const SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    const WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
    const OTA = "http://www.opentravel.org/OTA/2003/05";
    const TNS = "http://127.0.0.1:8000/api/siteminder";
    const XS = "http://www.w3.org/2001/XMLSchema";
    const TARGET_NAMESPACE = "http://127.0.0.1:8000/api/siteminder";

    const AUTHENTICATION = 4;    // System currently unavailable
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

    /**
     * @param $version
     * @return ResponseFactory|Response
     * @throws Exception
     */
    public function soap(Request $request, $version)
    {
        try {
            $this->setChannel(self::CONECTOR_CODE);

            $this->setXMLSoapRequest($request->getContent());

            $this->setHeader();
            $this->setBody();

            $this->setMustUnderstand();
            $this->setUsername();
            $this->setPassword();

            $this->setXMLRequest($this->getBodyChld(self::OTA)->asXML());
            if (method_exists($this, $this->requestedMethodName())) {
                $this->setVersion();
                $this->setTimeStamp();
                $this->setEchoToken();

                $credentials = ['code' => $this->getUsername(), 'password' => $this->getPassword()];
                if (!$token = auth()->attempt($credentials)) {
                    throw new Exception('Unauthorized', self::AUTHENTICATION);
                }

                DB::beginTransaction();
                $xmlResponse = $this->responseRequest($this->{$this->requestedMethodName()}(), true);
                DB::commit();
                $success = true;
            } else {
                throw new Exception('Method Not Allow');
            }
        } catch (Exception $ex) {
            if ($this->requestedMethodName()) {
                $xmlResponse = $this->responseRequest(
                    $this->responseError(
                        $ex->getMessage(),
                        $ex->getCode()
                    ),
                    true
                );
                DB::rollBack();
            } else {
                $xmlResponse = $this->responseRequest($ex->getMessage(), false, $ex->getCode());
            }
            $success = false;
        }

        $response = response($xmlResponse, 200, [
            'Content-Type' => 'text/xml; charset=utf-8'
        ]);

        $this->putXmlLog($request, $response, $this->getChannel(), $this->requestedMethodName(), $this->getEchoToken(), $success);

        return $response;
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelAvailRQ()
    {
        $stmMehtod = new OTA_HotelAvailRQ($this->getBodyChld(self::OTA), $this->getChannel());
        $stmMehtod->process();
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        $this->xmlAppend($xml, $stmMehtod->getResponse());

        return $xml;
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelAvailNotifRQ()
    {
        $stmMehtod = new OTA_HotelAvailNotifRQ($this->getBodyChld(self::OTA), $this->getChannel());
        $stmMehtod->process();
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelRateAmountNotifRQ()
    {
        $stmMehtod = new OTA_HotelRateAmountNotifRQ($this->getBodyChld(self::OTA), $this->getChannel());
        $stmMehtod->process();
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }
}
