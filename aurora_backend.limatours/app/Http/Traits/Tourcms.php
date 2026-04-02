<?php

namespace App\Http\Traits;

use App\Channel;
use App\TourcmsHeader;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use TourCMS\Utils\TourCMS as cnxTourCMS;

trait Tourcms
{
    private $marketplace_id;
    private $api_key_;
    private $timeout_;
    private $channel_id;

    private $tourcms;

    private function setMarketPlace(){
        $this->marketplace_id = self::MARKETPLACE_ID;
    }
    private function setApiKey(){
        $this->api_key_ = self::API_KEY;
    }
    private function setTimeout(){
        $this->timeout_ = self::TIMEOUT;
    }
    private function setChannelId(){
        $this->channel_id = self::CHANNEL_ID;
    }

    private function connect()
    {
        $this->setMarketPlace();
        $this->setApiKey();
        $this->setTimeout();
        $this->setChannelId();
        $this->tourcms = new cnxTourCMS($this->marketplace_id, $this->api_key_, 'simplexml', $this->timeout_);
    }

    private function doSearchBookings($params){
//        var_export( $this->tourcms->search_bookings($params, $this->channel_id) ); die;
        $result = json_encode( $this->tourcms->search_bookings($params, $this->channel_id) );
        $result = json_decode( $result );

        if( $result->error != 'OK' || $result->total_bookings_count == 0 ){
            $result->booking = [];
        }
        if( $result->error != 'OK' || $result->total_bookings_count == 1 ){
            $result->booking =  [ $result->booking ];
        }
        return $result;
    }

    private function doShowBooking($booking_id, $channel_id){

        if( $channel_id == '' ){
            $channel_id = $this->channel_id;
        }

//        var_export( $this->tourcms->show_booking($booking_id, $channel_id) ); die;
        $result = json_encode( $this->tourcms->show_booking($booking_id, $channel_id) );
        $result = json_decode( $result );

        if( $result->error != 'OK' ){
            return $result;
        }

        if( $result->booking->customer_count == 1 ){
            $result->booking->customers->customer =  [ $result->booking->customers->customer ];
        }

        if( is_object( $result->booking->components->component ) ){
            $result->booking->components->component =  [ $result->booking->components->component ];
        }

        return $result;
    }

    /**
     * @param $date / DD/MM/YYYY
     * @return $response / YYYY-MM-DD
     */
    private function parseDate($date){
        $explode = explode("/", $date);
        $response = $explode[2] . '-' . $explode[1] . '-' . $explode[0];
        return $response;
    }
    /**
     * @param $date /  YYYY-MM-DD
     * @return $response / DD/MM/YYYY
     */
    private function parseDateReserve($date){
        $explode = explode("-", $date);
        $response = $explode[2] . '/' . $explode[1] . '/' . $explode[0];
        return $response;
    }

    /**
     * @param $dateIni / 2019-08-05
     * @param $dateFin / 2019-08-05
     * @return bool
     */
    private function compareDates($dateIni, $dateFin)
    {
        $dateIni = strtotime($dateIni);
        $dateFin = strtotime($dateFin);
        if ($dateFin >= $dateIni) return true;
        else return false;
    }

    private function do_params($servs, $nrofile){

        $r = 'ok';

        if( $nrofile == '' ){
            $nrofile = 0;
        }
        $paxdes = $servs[0]['paxdes'];
        $fecini = $servs[0]['start_date'];
        $agent_ref = TourcmsHeader::find($servs[0]['tourcms_header_id'])->agent_ref;

        $currency = $servs[0]['currency'];

        $canadu = 0;
        $canchd = 0;
        $caninf = 0;

        $import = 0;

        foreach ( $servs as $serv ){
            if( $this->compareDates( $serv['start_date'], $fecini ) ){
                $fecini = $serv['start_date'];
            }
            if( (int)$serv['canadu'] > $canadu ){
                $canadu = (int)$serv['canadu'];
            }
            if( (int)$serv['canchd'] > $canchd ){
                $canchd = (int)$serv['canchd'];
            }
            if( (int)$serv['caninf'] > $caninf ){
                $caninf = (int)$serv['caninf'];
            }
            if( $serv['currency'] != $currency ){
                $r = 'El tipo de moneda no coincide entre montos';
                break;
            } else {
                if( (double)$serv['total'] == 0 ) {
                    $r = 'Ningún P.C. puede ser 0.00';
                    break;
                } else {
                    $import = $import + (double)$serv['total'];
                }
            }
        }

        $buffer = [
            'datapla' => [
                'fecini' => $fecini,
                'codcli' => "2HBEDS",
                'descri' => $paxdes,
                'observ' => (string)$import,
                'canadu' => $canadu,
                'canchd' => $canchd,
                'caninf' => $caninf,
                'import' => $import,
                'nroref' => $nrofile,
                'operad' => Auth::user()->code,
                'refext' => $agent_ref,
                'viene' => "BREDOW",
            ],
            'datasvs' => [],
            'datahtl' => [],
            'datapax' => [],
        ];

        $nrolin = 0;

        if( $r == 'ok' ) {

            for( $s=0; $s < count( $servs ); $s++ ){
                $nrolin++;
                $paxServ = (int)$servs[$s]['canadu'];

                array_push($buffer["datasvs"], [
                    "nrolin" => $nrolin,
                    "fecha" => $servs[$s]['start_date'],
                    "codigo" => $servs[$s]['code'],
                    "canpax" => $paxServ,
                    "infoad" => "",
                ]);

            }
        }

        // tipodoc DNI o RUC o vacio

        if( $r == 'ok' ){

            $paxsData = $servs[0]['paxsData'];

            for( $p=0; $p < count( $paxsData ); $p++ ){

                array_push($buffer["datapax"], [
                    "nombre" => $paxsData[$p]['nombre'],
                    "genero" => $paxsData[$p]['genero'],
                    "edad" => $paxsData[$p]['edad'],
                    "nac" => $paxsData[$p]['nac'],
                    "tipdoc" => "",
                    "nrodoc" => ""
                ]);

            }

        }

        if( $r == 'ok' ){
            return array( 'status' => $r, 'response' => $buffer );
        } else {
            return array( 'status' => 'error', 'response' => $r );
        }
    }

    private function doXML($servs, $nrofile){

        $r = 'ok';

        if( $nrofile == '' ){
            $nrofile = 0;
        }
        $paxdes = $servs[0]['paxdes'];
        $fecini = $servs[0]['start_date'];
        $agent_ref = TourcmsHeader::find($servs[0]['tourcms_header_id'])->agent_ref;

        $currency = $servs[0]['currency'];

        $canadu = 0;
        $canchd = 0;
        $caninf = 0;

        $import = 0;

        foreach ( $servs as $serv ){
            if( $this->compareDates( $serv['start_date'], $fecini ) ){
                $fecini = $serv['start_date'];
            }
            if( (int)$serv['canadu'] > $canadu ){
                $canadu = (int)$serv['canadu'];
            }
            if( (int)$serv['canchd'] > $canchd ){
                $canchd = (int)$serv['canchd'];
            }
            if( (int)$serv['caninf'] > $caninf ){
                $caninf = (int)$serv['caninf'];
            }
            if( $serv['currency'] != $currency ){
                $r = 'El tipo de moneda no coincide entre montos';
                break;
            } else {
                if( (double)$serv['total'] == 0 ) {
                    $r = 'Ningún P.C. puede ser 0.00';
                    break;
                } else {
                    $import = $import + (double)$serv['total'];
                }
            }
        }

        $buffer = "<?xml version=\"1.0\" encoding=\"ANSI_X3.4-1968\"?>
<!-- Comentario -->
<!-- tiphab=\"1\" Simple -->
<!-- tiphab=\"2\" Doble  -->
<!-- tiphab=\"3\" Triple -->

<!-- descri=\"\" equivale referencia pax -->
<!-- observ=\"\" equivale observaciones -->
<Planilla>
    <PllDatos fecini=\"".$this->parseDateReserve( $fecini )."\" />
    <PllDatos codcli=\"2HBEDS\" />
    <PllDatos descri=\"".$paxdes."\" />
    <PllDatos observ=\"".$import."\" />
    <PllDatos canadu=\"".$canadu."\" />
    <PllDatos canchd=\"".$canchd."\" />
    <PllDatos caninf=\"".$caninf."\" />
    <PllDatos import=\"".$import."\" />
    <PllDatos operad=\"".Auth::user()->code."\" />
    <PllDatos refext=\"".$agent_ref."\" />
    <PllDatos nroref=\"".$nrofile."\" />
	<PllDatos viene=\"MERIDI\" />
</Planilla>";

        $nrolin = 0;

        if( $r == 'ok' ) {

            for( $s=0; $s < count( $servs ); $s++ ){
                $nrolin++;

                $paxServ = (int)$servs[$s]['canadu'];
                $buffer.="
<Servicio>
    <SerDatos nrolin=\"". $nrolin ."\" />
    <SerDatos fecha=\"". $this->parseDateReserve( $servs[$s]['start_date'] ) ."\" />
    <SerDatos codigo=\"". $servs[$s]['code'] ."\" />
    <SerDatos canpax=\"". $paxServ ."\" />
    <SerDatos infoad=\"". "" ."\" />
</Servicio>";

            }
        }

        // tipodoc DNI o RUC o vacio

        if( $r == 'ok' ){

            $paxsData = $servs[0]['paxsData'];

            $buffer.="
<DatosPax>";
            for( $p=0; $p < count( $paxsData ); $p++ ){
                $buffer.="
    <Pax>
        <PaxDatos nombre=\"". $paxsData[$p]['nombre'] ."\" />
        <PaxDatos genero=\"". $paxsData[$p]['genero'] ."\" />
        <PaxDatos edad=\"". $paxsData[$p]['edad'] ."\" />
        <PaxDatos nac=\"". $paxsData[$p]['nac'] ."\" />
        <PaxDatos tipdoc=\"". "" ."\" />
        <PaxDatos nrodoc=\"". "" ."\" />
    </Pax>";
            }

            $buffer.="
</DatosPax>";
        }

        if( $r == 'ok' ){
            return array( 'status' => $r, 'response' => $buffer );
        } else {
            return array( 'status' => 'error', 'response' => $r );
        }
    }

}
