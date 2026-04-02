<?php

namespace App\Http\Traits;

use App\Channel;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

trait TrainService
{
    private $wsdl_ir;
    private $token_ir;
    private $agency_id_ir;
    private $user_id_ir;

    private function setWsdlIR(){
        $this->wsdl_ir = self::WSDL_IR;
    }

    private function setTokenIR(){
        $this->token_ir = self::TOKEN_IR;
    }

    private function getTokenIR(){
       return $this->token_ir;
    }

    private function setAgencyIdIR(){
        $this->agency_id_ir = self::AGENCY_ID_IR;
    }

    private function getAgencyIdIR(){
       return $this->agency_id_ir;
    }

    private function setUserIdIR( $_user_id ){
        $this->user_id_ir = $_user_id;
    }

    private function getUserIdIR(){
       return $this->user_id_ir;
    }

    private function connectIR()
    {
        self::setWsdlIR();
        //instanciando un nuevo objeto cliente para consumir el webservice
        // new SoapClient("some.wsdl", array('soap_version'   => SOAP_1_2));
        $client = new \SoapClient($this->wsdl_ir, [
            'encoding' => 'UTF-8',
            'trace' => true,
            'soap_version' => SOAP_1_1,
            'exceptions' => true
        ]);
        self::setTokenIR();
        self::setAgencyIdIR();
        return $client;
    }

    private function searchIR($train_type_id, $route_id, $date_from, $date_to, $quantity_num, $train_train_class_id)
    {

        $client = self::connectIR();
        //pasando los parámetros a un array
        $filter = [
            'idAgencia' => self::getAgencyIdIR(),
            'numWservicesToken' => self::getTokenIR(),
            'idAgenciaIntermediario' => self::getAgencyIdIR(),
            'idUsuarioIntermediario' =>  self::getUserIdIR(),
            'idViajeTipo' => $train_type_id,
            'idRuta' => (int)$route_id,
            'idServicio' => (int)$train_train_class_id,
            'fecViajeIda' => $date_from,
            'fecViajeRetorno' => $date_to,
            'numCupo' => $quantity_num,
            'indVenta' => 0
        ];
//        return $filter;
        $_filter = json_encode($filter);
        $filter = array('json' => $_filter);
        //llamando al método y pasándole el array con los parámetros
        $result = $client->__call("buscar", $filter );
        return json_decode($result);
    }

}
