<?php


namespace App\Http\Siteminder;

use DateTime;
use Exception;
use SimpleXMLElement;

class OTA_HotelResNotifRQ extends Client
{

    /**
     * Server constructor.
     * @param $hotelRes
     */
    public function __construct($hotelRes)
    {
        parent::__construct('OTA_HotelResNotifRQ');

        $this->data_handlle = $hotelRes;
    }

    /**
     * @throws Exception
     */
    public function commit()
    {
        if (!$this->data_handlle) {
            return null;
        }

        try {
            $this->buildContent(false);

            $this->push();

            $result = $this->validateXmlResponse();
        } catch (Exception $ex) {
            $result = [
                'success' => false,
                'error' => $ex->getMessage(),
            ];
        }

        $this->putXmlLog($this->request, $this->response, $this->getChannel(), 'OTA_HotelResNotifRQ', $this->data_handlle['reservation_code'], $result['success']);

        return $result;
    }

    /**
     * @throws Exception
     */
    public function cancel()
    {
        if (!$this->data_handlle) {
            return null;
        }

        try {
            $this->buildContent(true);

            $this->push();

            $result = $this->validateXmlResponse();
        } catch (Exception $ex) {
            $result = [
                'success' => false,
                'error' => $ex->getMessage(),
            ];
        }

        $this->putXmlLog($this->request, $this->response, $this->getChannel(), 'OTA_HotelResCancelNotifRQ', $this->data_handlle['reservation_code'], $result['success']);

        return $result;
    }

    /**
     * @param bool $cancel
     * @throws Exception
     */
    public function buildContent($cancel = false)
    {
        if ($cancel) {
            $ResID_Value = $this->data_handlle['channel_reservation_code'];// identificador unico para las reservas de ReservaHotel
        } else {
            $ResID_Value = $this->data_handlle['hotel_channel_code'] . '-' . $this->data_handlle['reservation_code'];// identificador unico para las reservas de ReservaHotel
        }

        $UniqueID =  $this->data_handlle['hotel_channel_code'] . '-' . $this->data_handlle['reservation_code'];

        $this->setClientRequestPos($cancel ? 'Cancel' : 'Commit');
        $HotelReservation = $this->setClientRequestReservation($UniqueID, $cancel ? $this->data_handlle['last_modify_date_time'] : null);

        /** @var SimpleXMLElement $ResGuests */
        $ResGuests = $HotelReservation->addChild('ResGuests'); // opcional
        /** @var SimpleXMLElement $RoomStays */
        $RoomStays = $HotelReservation->addChild('RoomStays');

        $setPrincipalGuest = true;
        $RPH = 1;
        $totalReservation = 0;
        foreach ($this->data_handlle['rooms'] as $Room) {
            $RoomsGestsType = [];
            // AgeQualifyingCode = 10; // adults
            // AgeQualifyingCode = 8; // childs
            $customer_name = explode(' ', $this->data_handlle['customer_name']);
            $GivenName = $customer_name[0];
            array_shift($customer_name);
            $Surname = implode(' ', $customer_name);
            $RoomsGestsType[10][0] = [
                'GivenName' => $GivenName,
                'Surname' => $Surname,
                'AgeQualifyingCode' => '10'
            ];

            for ($i = 1; $i < (($Room['adult_num'] + $Room['extra_num']) - 1); $i++) {
                $RoomsGestsType[10][] = [
                    'GivenName' => 'adult',
                    'Surname' => 'adult',
                    'AgeQualifyingCode' => '10'
                ];
            }

            for ($i = 0; $i < $Room['child_num']; $i++) {
                $RoomsGestsType[8][] = [
                    'GivenName' => 'child',
                    'Surname' => 'child',
                    'AgeQualifyingCode' => '8'
                ];
            }

            foreach ($RoomsGestsType as $RoomGestsType) {
                foreach ($RoomGestsType as $Guests) {
                    $ResGuest = $ResGuests->addChild('ResGuest');
                    $ResGuest->addAttribute('ResGuestRPH', $RPH); // Index by which this guest can be referenced within the RoomStay elements

                    if ($setPrincipalGuest) {
                        $ResGuest->addAttribute('PrimaryIndicator', '1');
                        $setPrincipalGuest = false;
                    } else {
                        $ResGuest->addAttribute('PrimaryIndicator', '0');
                    }

                    $Profile = $ResGuest->addChild('Profiles')->addChild('ProfileInfo')->addChild('Profile');
                    $Profile->addAttribute('ProfileType', '1');

                    $PersonName = $Profile->addChild('Customer')->addChild('PersonName');
                    $PersonName->addChild('GivenName', $Guests['GivenName']);
                    $PersonName->addChild('Surname', $Guests['Surname']);

//                    $ResGuest->addChild('Comments')->addChild('Comment')->addChild('Text', 'No comment');
                }
            }

            $RoomStay = $RoomStays->addChild('RoomStay');

            // RoomGuest Data
            $RoomStay->addChild('ResGuestRPHs')->addChild('ResGuestRPH')->addAttribute('RPH', $RPH);// Guest RPH associated with this RoomStay
            $RPH++;

            $RoomStay->addChild('BasicPropertyInfo')->addAttribute('HotelCode', $this->data_handlle['hotel_channel_code']);

            $TimeSpan = $RoomStay->addChild('TimeSpan');
            $TimeSpan->addAttribute('Start', $this->data_handlle['check_in']); // Check in date of this stay
            $TimeSpan->addAttribute('End', $this->data_handlle['check_out']); // Check out date of this stay

            $GuestCounts = $RoomStay->addChild('GuestCounts');
            foreach ($RoomsGestsType as $AgeQualifyingCode => $Guests) {
                $GuestCount = $GuestCounts->addChild('GuestCount');
                $GuestCount->addAttribute('Count', count($Guests));
                $GuestCount->addAttribute('AgeQualifyingCode', $AgeQualifyingCode);
            }

            if ($cancel) {
                // Room Rate Data
                $RoomRate = $RoomStay->addChild('RoomRates')->addChild('RoomRate');
                $RoomRate->addAttribute('NumberOfUnits', '1'); // Number of rooms booked with these stay details. Currently only a value of '1' is supported. Additional rooms must be specified by individual RoomStay elements instead.
                $RoomRate->addAttribute('RoomTypeCode', $Room['room_code']); // Room type identifier. This is expected to match across all RoomRate elements within the given RoomStay
                $RoomRate->addAttribute('RatePlanCode', $Room['rate_plan_code']); // Rate plan identifier

            } else {
                // Room Rate Data
                $RoomRate = $RoomStay->addChild('RoomRates')->addChild('RoomRate');
                $RoomRate->addAttribute('NumberOfUnits', '1'); // Number of rooms booked with these stay details. Currently only a value of '1' is supported. Additional rooms must be specified by individual RoomStay elements instead.
                $RoomRate->addAttribute('RateTimeUnit', "Day");
                $RoomRate->addAttribute('RoomTypeCode', $Room['room_code']); // Room type identifier. This is expected to match across all RoomRate elements within the given RoomStay
                $RoomRate->addAttribute('RatePlanCode', $Room['rate_plan_code']); // Rate plan identifier

                // Room Rate Dates
                $Rates = $RoomRate->addChild('Rates');
                foreach ($Room['rates'] as $dateInfo) {
                    $Rate = $Rates->addChild('Rate');
                    $Rate->addAttribute('UnitMultiplier', '1');
                    //$Rate->addAttribute('RoomPricingType', 'Per night'); // Can be one of the following five values: Per night, Per person, Per person per night, Per stay, Per use
                    $Rate->addAttribute('EffectiveDate', $dateInfo['date']); // Effective (start) date of pricing date range (inclusive)
                    $Rate->addAttribute('ExpireDate', (new DateTime($dateInfo['date']))->modify('+1 day')->format('Y-m-d')); // Expire (end) date of pricing date range (exclusive)

                    $Base = $Rate->addChild('Base');
                    $Base->addAttribute('AmountAfterTax', priceRound($dateInfo['total_amount_base'])); // Base price after tax. Either this or @AmountBeforeTax are needed, but it is not necessary to include both.
                    $Base->addAttribute('CurrencyCode', $this->Currency);
                }

                // Room Comment
                if ($Room['guest_note']) {
                    $RoomStay->addChild('Comments')->addChild('Comment')->addChild('Text', $Room['guest_note']);
                }
            }

            // Room Totals
            $Total = $RoomStay->addChild('Total');
            $Total->addAttribute('CurrencyCode', $this->Currency);
            $Total->addAttribute('AmountAfterTax', priceRound($Room['total_amount_base'])); // Total price AFTER tax. Either this or @AmountBeforeTax are needed, but it is not necessary to include both.
            $totalReservation += $Room['total_amount_base'];
        }

        $ResGlobalInfo = $HotelReservation->addChild('ResGlobalInfo');
        $HotelReservationID = $ResGlobalInfo->addChild('HotelReservationIDs')->addChild('HotelReservationID');
        $HotelReservationID->addAttribute('ResID_Type', $this->UniqueIDType); // Reservation ID type. Currently only "14" is supported

        $HotelReservationID->addAttribute('ResID_Value', $ResID_Value);// identificador unico para las reservas de ReservaHotel

        $Total = $ResGlobalInfo->addChild('Total');
        $Total->addAttribute('CurrencyCode', $this->Currency);
        $Total->addAttribute('AmountAfterTax', priceRound($totalReservation));

//        header("Content-Type:text/xml");
//        die($this->getRequestMethod()->asXML());
    }

    public function validateXmlResponse()
    {
//        header("Content-Type:text/xml");
//        die($this->request->getContent());
        $error = '';
        $channel_reservation_code = '';
        try {
            $this->setXMLSoapResponse($this->response->getContent());
            $objXml = $this->xmlSoapResponseBodyContent();

            if (!$objXml) {
                $error = 'Mal formed xml response';
            } else {
                $errorArray = [];
                if (!empty($objXml->OTA_HotelResNotifRS->Errors)) {
                    if ($objXml->OTA_HotelResNotifRS->Errors->Error) {
                        foreach ($objXml->OTA_HotelResNotifRS->Errors->Error as $value) {
                            $errorArray[] = (string)$value;
                        }
                    }
                }

                if (!empty($objXml->OTA_HotelResNotifRS->Warnings)) {
                    if ($objXml->OTA_HotelResNotifRS->Warnings->Warning) {
                        foreach ($objXml->OTA_HotelResNotifRS->Warnings->Warning as $value) {
                            $errorArray[] = (string)$value;
                        }
                    }
                }

                if (count($errorArray) > 0) {
                    $error = implode(' | ', $errorArray);
                }
            }
            if ($error != '') {
                throw new Exception($error);
            } else {
                $channel_reservation_code = $objXml->OTA_HotelResNotifRS->HotelReservations->HotelReservation->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Value->__toString();
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        $success = (bool)!$error;

        return [
            'success' => $success ? true : false,
            'error' => $error,
            'channel_reservation_code' => $channel_reservation_code
        ];
    }

    /**
     * @param $ResID_Value
     * @param null $LastModifyDateTime
     * @return SimpleXMLElement
     */
    private function setClientRequestReservation($ResID_Value, $LastModifyDateTime = null)
    {
        $HotelReservations = $this->getRequestMethod()->addChild('HotelReservations');
        $HotelReservation = $HotelReservations->addChild('HotelReservation');

        if ($LastModifyDateTime) {
            $HotelReservation->addAttribute('LastModifyDateTime', $LastModifyDateTime); // Opcional
        } else {
            $HotelReservation->addAttribute('CreateDateTime', $this->data_handlle['created_at']); // Opcional
        }

        $UniqueID = $HotelReservation->addChild('UniqueID');
        $UniqueID->addAttribute("Type", $this->UniqueIDType);// Reservation ID type. Currently only "14" is supported
        $UniqueID->addAttribute("ID", $ResID_Value); // identificador unico para las reservas de ReservaHotel

        return $HotelReservation;
    }

    /**
     * @param $ResStatus
     */
    private function setClientRequestPos($ResStatus)
    {
        $this->getRequestMethod()->addAttribute('ResStatus', $ResStatus); // Reservation status. Must be one of the following: Commit, Cancel, Modify
        $POS = $this->getRequestMethod()->addChild('POS');
        $Source = $POS->addChild('Source');
        $RequestorID = $Source->addChild('RequestorID');
        $RequestorID->addAttribute('Type', $this->RequestorType);
        $RequestorID->addAttribute('ID', $this->RequestorID);

        $BookingChannel = $Source->addChild('BookingChannel');
        $BookingChannel->addAttribute("Primary", "true");
        $CompanyName = $Source->addChild('CompanyName', $this->CompanyName);
        $CompanyName->addAttribute("Code", $this->CompanyCode);
    }
}
