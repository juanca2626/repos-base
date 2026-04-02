<?php


namespace App\Http\Erevmax;


use App\Http\Erevmax\Traits\Erevmax as ErevmaxTrait;
use Exception;
use SimpleXMLElement;

class Server
{
    use ErevmaxTrait;

    const WSDL = "http://schemas.xmlsoap.org/wsdl/";
    const SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    const WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
    const WSA = "http://schemas.xmlsoap.org/ws/2004/08/addressing";
    const OTA = "http://www.opentravel.org/OTA/2003/05";
    const TNS = "http://127.0.0.1:8000/api/siteminder";
    const XS = "http://www.w3.org/2001/XMLSchema";

    /** @var SimpleXMLElement */
    private $XMLSoapREquest;
    /** @var OTA_HotelAvailNotifRQ */
    private $method;

    /** @var SimpleXMLElement */
    private $response;
    /** @var SimpleXMLElement */
    private $response_method;

    /**
     * Server constructor.
     * @param string $xml
     * @throws Exception
     */
    public function __construct(string $xml)
    {
        try {
            $this->setXMLSoapRequest($xml);

            $this->autenticate();

            $this->setChannel($this->CONECTOR_CODE);
            $this->setMethodClass();

            $this->method->process();

            $this->setResponseSucces();
        } catch (Exception $ex) {
            if ($this->method) {
                $this->setResponseError($ex->getMessage(), $ex->getCode());
            } else {
                $this->setResponseFault($ex->getMessage());
            }
        }

        $this->setSoapResponse();
    }

    /**
     * @return mixed
     */
    public function responseXML()
    {
        return $this->response->asXML();
    }

    /**
     * @throws Exception
     */
    public function setMethodClass()
    {
        if (empty($this->getBodyContent()->attributes()->EchoToken)) {
            throw new Exception('EchoToken can not null or empty', $this->REQUIRED_FIELD_MISSING);
        }

        switch ($this->getAction()) {
            case 'OTA_HotelAvailGetRQ':
                $this->method = new OTA_HotelAvailGetRQ($this->getBodyContent(), $this->getChannel());
                break;
            case 'OTA_HotelAvailNotifRQ':
                $this->method = new OTA_HotelAvailNotifRQ($this->getBodyContent(), $this->getChannel());
                break;
            case 'OTA_HotelRatePlanRQ':
                $this->method = new OTA_HotelRatePlanRQ($this->getBodyContent(), $this->getChannel());
                break;
            case 'OTA_HotelRatePlanNotifRQ':
                $this->method = new OTA_HotelRatePlanNotifRQ($this->getBodyContent(), $this->getChannel());
                break;
            default:
                throw new Exception('Method Not Allow');
        }
    }

    /**
     * @return string
     */
    private function responseRootName()
    {
        return substr($this->getAction(), 0, -2) . "RS";
    }

    /**
     * @return SimpleXMLElement
     */
    private function setResponseRoot()
    {
        $this->response_method = new SimpleXMLElement('<' . $this->responseRootName() . '/>');
        $this->response_method->addAttribute('xmlns', $this->OTA);
        $this->response_method->addAttribute('Version', $this->getVersion());
        $this->response_method->addAttribute('TimeStamp', date('c'));
        $this->response_method->addAttribute('EchoToken', $this->getEchoToken());
        $this->response_method->addAttribute('PrimaryLangID', $this->getPrimaryLangID());
        $this->response_method->addAttribute('Target', $this->getTarget());

        return $this->response_method;
    }

    /**
     * @param $message
     * @param int $code
     */
    private function setResponseFault($message, $code = 300)
    {
        $this->response_method = new SimpleXMLElement('<xmlns:SOAP-ENV:Fault/>', LIBXML_NOERROR);
        $this->response_method->addAttribute('xmlns:xmlns:SOAP-ENV', $this->SOAPENV);
        $this->response_method->addChild('faultcode', $code);
        $this->response_method->addChild('faultstring', $message);
    }

    /**
     * @throws Exception
     */
    public function setResponseSucces()
    {
        $this->setResponseRoot()
            ->addChild('Success');

        if ($this->method->getResponse()){
//            dd($this->method->getResponse()->data);
            $this->xmlAppend($this->response_method, $this->method->getResponse());
        }
    }

    /**
     * @param $message
     * @param $code
     * @param string $type
     */
    public function setResponseError($message, $code, $type = '12')
    {
        $Error = $this->setResponseRoot()->addChild('Errors')
            ->addChild('Error', $message);
        $Error->addAttribute('Code', $code);
        $Error->addAttribute('Type', $type); // Type = 12 - Processing exception
    }

    /**
     *
     */
    private function setSoapResponse()
    {
        $this->response = new SimpleXMLElement('<SOAP-ENV:Envelope/>', LIBXML_NOERROR);
        $this->response->addAttribute('xmlns:xmlns:SOAP-ENV', $this->SOAPENV);

        /* BODY */
        $Body = $this->response->addChild('xmlns:SOAP-ENV:Body');

        $this->xmlAppend($Body, $this->response_method);
    }



    /**
     * @throws Exception
     */
    private function autenticate()
    {
        $Security = $this->getSecurity();

        if (empty($Security->UsernameToken) or empty($Security->UsernameToken->Username) or empty($Security->UsernameToken->Password)) {
            throw new Exception('Security headers cant by processed', $this->AUTHENTICATION_ERROR_CODE);
        }

        $credentials = [
            'code' => $Security->UsernameToken->Username,
            'password' => $Security->UsernameToken->Password
        ];

        if (!$token = auth()->attempt($credentials)) {
            throw new Exception('Unauthorized', $this->AUTHENTICATION_ERROR_CODE);
        }
    }

    /**
     * @param string $xml
     */
    private function setXMLSoapRequest($xml)
    {
        $this->XMLSoapREquest = new SimpleXMLElement($xml);
    }

    /**
     * @return SimpleXMLElement
     */
    private function getHeader()
    {
        return $this->XMLSoapREquest->children(self::SOAPENV)->Header;
    }

    /**
     * @param null $ns
     * @return SimpleXMLElement
     */
    private function getHeaderContent($ns = null)
    {
        return $this->getHeader()->children($ns);
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    private function getSecurity()
    {
        if (!isset($this->getHeaderContent(self::WSSE)->Security)) {
            throw new Exception('Security headers cant by processed', $this->AUTHENTICATION_ERROR_CODE);
        }

        return $this->getHeaderContent(self::WSSE)->Security;
    }

    /**
     * @return SimpleXMLElement
     */
    private function getBodyContent()
    {
        return $this->XMLSoapREquest->children(self::SOAPENV)->Body->children(self::OTA);
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getHeaderContent(self::WSA)->Action->__toString();
    }

    private function getVersion()
    {
        return $this->getBodyContent()->attributes()->Version;
    }

    public function getEchoToken()
    {
        return $this->getBodyContent()->attributes()->EchoToken;
    }
    private function getPrimaryLangID()
    {
        return $this->getBodyContent()->attributes()->PrimaryLangID ?? 'EN';
    }
    private function getTarget()
    {
        return $this->getBodyContent()->attributes()->Target;
    }

    private function createEchoToken()
    {
        $this->EchoToken = $this->createToken();
    }

    private function createToken($str = null)
    {
        return md5($str ?? uniqid(rand(), true));
    }
}
