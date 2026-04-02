<?php

namespace Src\Modules\File\Presentation\Http\Traits;

use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;

trait CommunicationServiceTemplate
{
    /**
     * Decode correspondingly the response
     * @param  array $response
     * @return stdClass
     */

    public function communication_html($file_header, $services, $template)
    {        
        $stella = new ApiGatewayExternal();

        foreach($services as $index => $service_new){

            $masterServices = (array) $stella->getSuppliers($service_new['code_request_book']);
            if(isset($masterServices) and count($masterServices)>0){
                $masterServices = (object) $masterServices[0];
                $supplier = $masterServices->razon;
                $emails = [];
                foreach($masterServices->contacts as $contact){
                    array_push($emails, $contact->email);
                }
                $services[$index]['supplier_name'] = $supplier;
                $services[$index]['supplier_emails'] = $emails;
                $services[$index]['html'] = view($template, [
                    "file" => $file_header,
                    "services" => $services[$index]
                ])->render();

                // return $services[$index];

            }

        }

        return $services;

    }

    public function communication_modification_html($file_header, $services)
    {        
        $stella = new ApiGatewayExternal();

        foreach($services as $index => $service_new){

            if(isset($service_new['code_request_book'])){
                $masterServices = (array) $stella->getSuppliers($service_new['code_request_book']);
                if(isset($masterServices) and count($masterServices)>0){
                    $masterServices = (object) $masterServices[0];
                    $supplier = $masterServices->razon;
                    $emails = [];
                    foreach($masterServices->contacts as $contact){
                        array_push($emails, $contact->email);
                    }
                    $services[$index]['supplier_name'] = $supplier;
                    $services[$index]['supplier_emails'] = $emails;
                    $services[$index]['html'] = view("emails.reservations.services.reservation_modification", [
                        "file" => $file_header,
                        "services" => $services[$index]
                    ])->render();

                    // return $services[$index];

                }
            }else{
                if(count($service_new['cancellation'])>0){
                    $services[$index]['cancellation'] = $this->communication_html($file_header, $service_new['cancellation'], "emails.reservations.services.cancellation_new");
                }

                if(count($service_new['reservations'])>0){
                    $services[$index]['reservations'] = $this->communication_html($file_header, $service_new['reservations'], "emails.reservations.services.reservation_solicitude");
                }
            }

        }

        return $services;

    }

    public function getServicesMaster($params)
    {
        $code = $params['service'];
        $date_in = $params['date_in'];
        $total_passengers = $params['total_adults'];
        $total_children = $params['total_children'];
        $start_time = isset($params['start_time']) ? $params['start_time'] : '07:01:00' ;

        $params = [
            'equivalences' => [
            [
                'code' => $code,
                'date_in' => $date_in,
                'total_passengers' => $total_passengers,
                'total_children' => $total_children,
                'start_time' => $start_time
            ]
            ]
        ];

        $stela = new ApiGatewayExternal();
        $services_news = $stela->getMasterServices($params);
        $services_news = json_decode(json_encode($services_news), true);

        return $services_news;
    }

}
