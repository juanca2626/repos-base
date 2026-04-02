<?php

namespace App\Http\Traits;

use App\Channel as ChannelModel;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use SimpleXMLElement;
use Illuminate\Support\Collection;

trait HyperguestGeneral
{
    /** @var SimpleXMLElement */
    private $XMLSoapREquest;
    /** @var SimpleXMLElement */
    private $XMLREquest;
    /** @var SimpleXMLElement */
    private $XMLResponse;
    /** @var SimpleXMLElement */
    private $Header;
    /** @var SimpleXMLElement */
    private $Body;
    /** @var SimpleXMLElement */
    private $RequestedMethod;

    /** @var string */
    private $Version;
    /** @var string */
    private $TimeStamp;
    /** @var string */
    private $EchoToken;
    /** @var string */
    private $PrimaryLangID;
    /** @var string */
    private $Target;
    /** @var string */
    private $startDate;
    /** @var string */
    private $endDate;
    /** @var string */
    private $Action;
    /** @var string */
    private $From;
    /** @var string */
    private $ReplyTo;
    /** @var string */
    private $Password;
    /** @var string */
    private $MessageID;
    /** @var string */
    private $MustUnderstand;
    /** @var string */
    private $Username;
    /** @var string */
    private $To;

    /** @var Collection */
    private $availableRoomRates;
    /** @var Collection */
    private $availableRoomRatesDates = false;
    /** @var Collection */
    private $availableRoomDescriptions;
    /** @var Collection */
    private $requestedRoomRates;
    /** @var \App\Hotel */
    private $requestedHotel;
    /** @var Collection */
    private $availableRoomRatesInventores;
    /** @var Collection */
    private $availableRoomRatesCalendary;
    /** @var Collection */
    private $availableRooms;
    /** @var \App\Channel|Collection */
    private $channel;

    /** @var CarbonPeriod */
    private $requestedDateRanges = null;

    /** @var array */
    private $RatesPlanIds;
    /** @var array */
    private $RatesPlanRoomsIds;

    /**
     * Recive una instancia de App\Channel o un codigo de channel para crear una instancia nueva
     *
     * @param $channel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function setChannel($channel)
    {
        if ($channel instanceof ChannelModel) {
            $this->channel = $channel;
        } else {
            $this->channel = ChannelModel::where('code', '=', $channel)->first();
        }

        if (!$this->getChannel()) {
            return response('Channel unable to proscess', 401);
        }
    }

    /**
     * @return ChannelModel|Collection
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return Channel|Collection
     */
    private function channelId()
    {
        return $this->getChannel()->id;
    }

    /**
     * @param string $xml
     */
    private function setXMLSoapRequest($xml)
    {
        $this->XMLSoapREquest = new SimpleXMLElement($xml);
    }

    /**
     *
     * @link https://php.net/manual/en/simplexmlelement.construct.php
     *
     * @param $message
     * @param $code
     * @param string $type Default Value "12" = "Processing exception"
     * @return SimpleXMLElement
     */
    private function responseError($message, $code, $type = '12')
    {
        $xmlError = $this->setXMLResponse();

        $Error = $xmlError->addChild('Errors')
            ->addChild('Error', $message);

        $Error->addAttribute('Code', $code);
        $Error->addAttribute('Type', $type); // Type = 12 - Processing exception

        return $xmlError;
    }

    private function setXMLSoapRequestChild()
    {
        return $this->XMLSoapREquest->children(self::SOAPENV);
    }

    private function setHeader()
    {
        $this->Header = $this->setXMLSoapRequestChild()->Header;
    }

    /**
     * @param null $ns
     * @return SimpleXMLElement
     */
    private function getHeaderChild($ns = null)
    {
        return $this->Header->children($ns);
    }

    private function setAction()
    {
        $this->Action = $this->getHeaderChild(self::WSA)->Action->__toString();
    }

    private function setFrom()
    {
        $this->From = $this->getHeaderChild(self::WSA)->From->Address->__toString();
    }

    private function setTo()
    {
        $this->To = $this->getHeaderChild(self::WSA)->Security->__toString();
    }

    private function setReplyTo()
    {
        $this->ReplyTo = $this->getHeaderChild(self::WSA)->ReplyTo->Address->__toString();
    }

    private function setMessageID()
    {
        $this->MessageID = $this->getHeaderChild(self::WSA)->MessageID->__toString();
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    private function getSecurity()
    {
        if (!isset($this->getHeaderChild(self::WSSE)->Security)) {
            throw new Exception('Security headers cant by prodessced', self::AUTHENTICATION);
        }
        if (!isset($this->getHeaderChild(self::WSSE)->Security->UsernameToken)) {
            throw new Exception('Security headers cant by prodessced', self::AUTHENTICATION);
        }
        if (!isset($this->getHeaderChild(self::WSSE)->Security->UsernameToken->Username)) {
            throw new Exception('Security headers cant by prodessced', self::AUTHENTICATION);
        }
        if (!isset($this->getHeaderChild(self::WSSE)->Security->UsernameToken->Password)) {
            throw new Exception('Security headers cant by prodessced', self::AUTHENTICATION);
        }

        return $this->getHeaderChild(self::WSSE)->Security;
    }

    /**
     * @throws Exception
     */
    private function setMustUnderstand()
    {
        $this->MustUnderstand = (bool)$this->getSecurity()->children(self::SOAPENV)->mustUnderstand->__toString();
    }


    /**
     * @throws Exception
     */
    private function setUsername()
    {
        $this->Username = trim($this->getSecurity()->UsernameToken->Username->__toString());
    }

    /**
     * @throws Exception
     */
    private function getUsername()
    {
        return $this->Username;
    }

    /**
     * @throws Exception
     */
    private function setPassword()
    {
        $this->Password = trim($this->getSecurity()->UsernameToken->Password->__toString());
    }

    /**
     * @throws Exception
     */
    private function getPassword()
    {
        return $this->Password;
    }

    private function setBody()
    {
        $this->Body = $this->setXMLSoapRequestChild()->Body;
    }

    /**
     * @param null $ns
     * @return SimpleXMLElement
     */
    private function getBodyChld($ns = null)
    {
        return $this->Body->children($ns);
    }

    /**
     * @param string $xml
     */
    private function setXMLRequest($xml)
    {
        $xml = trim($xml);

        $posicion = strpos($xml,'SOAP-ENV:Body');
        $cadena1= substr($xml,$posicion);

        $cadena2 = str_replace("</SOAP-ENV:Envelope>"," ",$cadena1);
        $cadena3 = str_replace("</SOAP-ENV:Body>"," ",$cadena2);

        $cadena4 = trim($cadena3);


        $posicion2= strpos($cadena4,'envelope/">');
        $cadena5 = substr($cadena4,$posicion2);
        $cadena6 = str_replace('envelope/">'," ",$cadena5);

        $posicion3= strpos($cadena6,"envelope/'>");
        $cadena7 = substr($cadena6,$posicion3);
        $cadena8 = str_replace("envelope/'>"," ",$cadena7);

       $cadena = trim($cadena8);

        $this->XMLREquest = new SimpleXMLElement($cadena);
    }

    /**
     * @param string $xml
     */
    private function getUserXmlCredentials($xml)
    {
        $xml = trim($xml);

        $cadena = substr($xml, 0, strpos($xml, "</wsse:Username>"));

        $cadena2 = substr($cadena, strpos($cadena, "<wsse:Username>")-1);

        $cadena3 = str_replace("<wsse:Username>"," ",$cadena2);

        $cadena3 = trim($cadena3);

        return $cadena3;
    }

    private function getPasswordXmlCredentials($xml)
    {
        $xml = trim($xml);

        $cadena = substr($xml, 0, strpos($xml, "</wsse:Password>"));

        $cadena2 = substr($cadena, strpos($cadena, "PasswordText'>"));

        $cadena3 = str_replace("PasswordText'>"," ",$cadena2);

        $cadena3 = trim($cadena3);

        $cadena4 = substr($cadena3, strpos($cadena3, 'PasswordText">'));

        $cadena5 = str_replace('PasswordText">'," ",$cadena4);

        $cadena6 = trim($cadena5);


        return $cadena5;
    }

    /**
     * @return SimpleXMLElement
     */
    private function getXMLRequest()
    {
        return $this->XMLREquest;
    }

    private function setVersion()
    {
        $this->Version = $this->getXMLRequest()->attributes()->Version->__toString();
    }

    private function getVersion()
    {
        return $this->Version;
    }

    private function setTimeStamp()
    {
        $this->TimeStamp = $this->getXMLRequest()->attributes()->TimeStamp->__toString();
    }

    /**
     * @throws Exception
     */
    private function setEchoToken()
    {
        if (empty($this->getXMLRequest()->attributes()->EchoToken)) {
            throw new Exception('EchoToken date can not null or empty', self::REQUIRED_FIELD_MISSING);
        }

        $this->EchoToken = $this->getXMLRequest()->attributes()->EchoToken->__toString();
    }

    private function createToken($str = null)
    {
        return md5($str ?? uniqid(rand(), true));
    }

    private function createEchoToken()
    {
        $this->EchoToken = $this->createToken();
    }

    public function getEchoToken()
    {
        return $this->EchoToken;
    }

    private function setPrimaryLangID()
    {
        if (!empty($this->getXMLRequest()->attributes()->PrimaryLangID)) {
            $this->PrimaryLangID = $this->getXMLRequest()->attributes()->PrimaryLangID->__toString();
        } else {
            $this->PrimaryLangID = 'en_US';
        }
    }

    private function getPrimaryLangID()
    {
        return $this->PrimaryLangID;
    }

    private function setTarget()
    {
        $this->Target = $this->getXMLRequest()->attributes()->Target->__toString();
    }

    private function getTarget()
    {
        return $this->Target;
    }

    private function requestedMethodName()
    {
        if (empty($this->getXMLRequest())) {
            return false;
        }
        return $this->getXMLRequest()->getName();
    }

    private function responseMethodName()
    {
        return substr($this->requestedMethodName(), 0, -2) . "RS";
    }


    /**
     * @param SimpleXMLElement $DateRange
     * @throws Exception
     */
    private function setStartEndDates(SimpleXMLElement $DateRange)
    {
        if (empty($DateRange->attributes()->Start) or empty($DateRange->attributes()->End)) {
            throw new Exception('Start and End date can not null or empty', self::REQUIRED_FIELD_MISSING);
        }

        $startDate = trim($DateRange->attributes()->Start->__toString());
        $endDate = trim($DateRange->attributes()->End->__toString());

        if (strtotime($startDate) > strtotime($endDate)) {
            throw new Exception('Start date can not be higer than End date', self::INVALID_PROPERTY_CODE);
        }

        $this->setStartDate($startDate);
        $this->setEndDate($endDate);

        $this->setDateRange($this->getStartDate(), $this->getEndDate());
    }

    private function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    private function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    private function getStartDate()
    {
        return $this->startDate;
    }

    private function getEndDate()
    {
        return $this->endDate;
    }

    private function getCheckOut()
    {
        return (new DateTime($this->getEndDate()))->modify('- 1 day')->format('Y-m-d');
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param string $intervalDays
     * @throws Exception
     */
    private function setDateRange($startDate, $endDate, $intervalDays = 'P1D')
    {
        $this->requestedDateRanges = CarbonPeriod::create($startDate, $endDate);
    }

    /**
     * @return CarbonPeriod
     */
    private function getDateRange()
    {
        return $this->requestedDateRanges;
    }

    /**
     * @return array
     */
    private function getDateRangeArray()
    {
        return $this->requestedDateRanges->toArray();
    }

    /**
     * @param SimpleXMLElement $HotelRef
     * @throws Exception
     */
    private function setRequestedHotel(SimpleXMLElement $HotelRef)
    {
        $HotelCode = $HotelRef->attributes()->HotelCode->__toString();

        if (!$HotelCode) {
            throw new Exception('HotelCode cannot be null', self::REQUIRED_FIELD_MISSING);
        }

        $this->requestedHotel = $this->channel->hotels()->where('channel_hotel.code', '=', $HotelCode)->first();

        if (empty($this->requestedHotel)) {
            throw new Exception('Invalid HotelCode "' . $HotelCode . '"', self::INVALID_PROPERTY_CODE);
        }
    }

    /**
     * @return \App\Hotel
     */
    private function hotel()
    {
        return $this->requestedHotel;
    }

    private function getHotelCode()
    {
        return $this->hotel()->pivot->code;
    }

    private function getHotelId()
    {
        return $this->hotel()->id;
    }

    private function getMessageId()
    {
        return $this->MessageID;
    }

    /**
     * @param SimpleXMLElement $to
     * @param SimpleXMLElement $from
     */
    private function xmlAppend(SimpleXMLElement $to, SimpleXMLElement $from)
    {
        $toDom = dom_import_simplexml($to);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }
}
