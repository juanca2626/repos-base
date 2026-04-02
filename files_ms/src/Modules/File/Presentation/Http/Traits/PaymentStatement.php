<?php

namespace Src\Modules\File\Presentation\Http\Traits;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;

trait PaymentStatement
{
    /**
     * Decode correspondingly the response
     * @param  array $response
     * @return stdClass
     */
    public function getPaymentArray($file_codes)
    { 
        // $file_codes = [350904,365704,368579,365619];        
        $stella = new ApiGatewayExternal();
        $statement_payments_received = (array) $stella->statement_array_payments_received(['files' =>  $file_codes]);
//  dd($statement_payments_received);
        $payments = [];
        foreach($statement_payments_received as $payment_data){

            if(count($payment_data->data)>0){

                $payment_received = [];
                $total_pagos = 0;
                foreach($payment_data->data as $payment){

                    $tipdoc = trim($payment->tipdoc);

                    if($tipdoc == 'RE'){

                        array_push($payment_received, [
                            'date' => $payment->fecha,
                            'descri' => $payment->descri,
                            'type_transac' => 'Transferencia',
                            'import' => $payment->habelo
                        ]);
                        $total_pagos = $total_pagos  + $payment->habelo;
                    }

                    if($tipdoc == 'TR'){
                        $import = 0;
                        if($payment->debemo > 0){
                            $import = $payment->debeba * (-1);
                        }else{
                            $import = $payment->habeba;
                        }

                        $total_pagos = $total_pagos  + $import;

                        array_push($payment_received, [
                            'date' => $payment->fecha,
                            'descri' => $payment->descri,
                            'type_transac' => 'Transferencia de pago tesoreria',
                            'import' => $import
                        ]);
                    }
                }

                if($total_pagos>0){
                    if(!isset($payments[$payment_data->file])){
                        $payments[$payment_data->file] = [];
                    }

                    $payments[$payment_data->file] = [
                        'total_payment' => $total_pagos,
                        'payment_received' => $payment_received
                    ];
                }

            }
        }

        return $payments;
    }

}
