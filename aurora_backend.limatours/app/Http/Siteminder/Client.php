<?php

namespace App\Http\Siteminder;

use App\Http\Siteminder\Traits\Siteminder as SiteminderTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use SimpleXMLElement;

class Client
{
    use SiteminderTrait;

    public $SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    public $WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
    public $PASSWORD_TYPE_ENCODE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText";
    public $OTA = "http://www.opentravel.org/OTA/2003/05";
    public $RequestorType = "22";
    public $RequestorID = "LMT";
    public $UniqueIDType = "14";
    public $CompanyName = "Limatours";
    public $CompanyCode = "LMT";
    public $Currency = "USD";
    public $ResUserName = "LimaToursTest";
    public $ResPassword = "1bs5NmCKoXZOsP584wDEU3e2";
    public $ResUri = "https://cmtpi.siteminder.com/siteconnect/services";

    /** @var SimpleXMLElement */
    public $XMLSoapResponse;
    /**@var SimpleXMLElement */
    private $request_method;
    /** @var SimpleXMLElement */
    private $XMLSoapRequest;
    /** @var SimpleXMLElement */
    private $response_method;

    /** @var array */
    public $data_handlle;

    /** @var Request */
    public $request;
    /** @var Response */
    public $response;
    /** @var Response */
    public $method;

    /**
     * Server constructor.
     * @param string $methodName
     */
    public function __construct(string $methodName)
    {
        $this->method = $methodName;
        $this->setChannel($this->CONECTOR_CODE);


        if (App::environment('production') === true) {
            // Europa, Oriente Medio y África
           $this->ResUri = 'https://ws.siteminder.com/siteconnect/services/';

            // Asia-Pacífico
//            $this->ResUri = 'https://ws-apac.siteminder.com/siteconnect/services/';

            $this->ResUserName = 'Limatours';
            $this->ResPassword = 'ZKwBZy2UIXXtZXVpnwsCeUnB';
        }

        $this->setXMLSoapRequest($methodName);
    }

    /**
     * @param string $methodName
     * @return SimpleXMLElement
     */
    private function setXMLSoapRequest($methodName)
    {
        $this->XMLSoapRequest = new SimpleXMLElement('<SOAP-ENV:Envelope/>', LIBXML_NOERROR);
        $this->XMLSoapRequest->addAttribute('xmlns:xmlns:SOAP-ENV', $this->SOAPENV);

        /* HEADER */
        $this->Header = $this->XMLSoapRequest->addChild('SOAP-ENV:SOAP-ENV:Header');

        $Security = $this->Header->addChild('wsse:wsse:Security');
        $Security->addAttribute('soap:soap:mustUnderstand', '1');
        $Security->addAttribute('xmlns:xmlns:wsse', $this->WSSE);
        $Security->addAttribute('xmlns:xmlns:soap', $this->SOAPENV);

        $UsernameToken = $Security->addChild('wsse:wsse:UsernameToken');
        $UsernameToken->addChild('wsse:wsse:Username', $this->ResUserName);

        $Password = $UsernameToken->addChild('wsse:wsse:Password', $this->ResPassword);
        $Password->addAttribute('Type', $this->PASSWORD_TYPE_ENCODE);

        /* BODY */
        $this->Body = $this->XMLSoapRequest->addChild('SOAP-ENV:SOAP-ENV:Body');

        $this->request_method = $this->Body->addChild($methodName);
        $this->request_method->addAttribute('xmlns', $this->OTA);
        $this->request_method->addAttribute('Version', '1.0');
        $this->request_method->addAttribute('TimeStamp', date('c'));

        $this->createEchoToken();
        $this->request_method->addAttribute('EchoToken', $this->getEchoToken());
    }

    /**
     * @return SimpleXMLElement
     */
    public function getRequestMethod()
    {
        return $this->request_method;
    }

    public function getEchoToken()
    {
        return $this->EchoToken;
    }

    private function createEchoToken()
    {
        $this->EchoToken = $this->createToken();
    }

    private function createToken($str = null)
    {
        return md5($str ?? uniqid(rand(), true));
    }

    /**
     * Server constructor.
     * @param string $methodName
     * @return array
     * @throws Exception
     */
    public function push()
    {
//        header("Content-Type:text/xml");
//        die($this->XMLSoapRequest->asXML());
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->ResUri,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $this->XMLSoapRequest->asXML(),
        ]);

        $responseString = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $status = $info['http_code'];
        $requestHeaders = $info['request_header'];
        $headerSize = $info['header_size'];

        // extract headers
        $responseHeaders = substr($responseString, 0, $headerSize);
        // extract body
        $bodyResponse = substr($responseString, $headerSize);

        // set the request done
        $this->request = new Request([], [], [], [], [], $_SERVER, $this->XMLSoapRequest->asXML());
        $this->request->headers->replace($this->getHeaders($requestHeaders));
        $this->request->server->add(['REMOTE_ADDR', $_SERVER['SERVER_ADDR']]);// como somos quien hace el request somo el REMOTE_ADDR de la ip

        // set response returnet from  service
        $this->response = new Response($bodyResponse, $status, $this->getHeaders($responseHeaders));
        if ($status !== 200) {
            throw new \Exception($bodyResponse);
        }
    }

    /**
     * @param $xml
     */
    public function setXMLSoapResponse($xml)
    {
        $this->XMLSoapResponse = new SimpleXMLElement($xml);
    }

    /**
     * @return SimpleXMLElement
     */
    public function xmlSoapResponseBody()
    {
        return $this->XMLSoapResponse->children($this->SOAPENV)->Body;
    }

    /**
     * @return SimpleXMLElement
     */
    public function xmlSoapResponseBodyContent()
    {
        return $this->xmlSoapResponseBody()->children($this->OTA);
    }

    /**
     * @param $headerText
     * @return array
     */
    private function getHeaders(string $headerText = '')
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
}
