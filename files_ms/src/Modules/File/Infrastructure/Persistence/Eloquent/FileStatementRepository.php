<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatementDetailEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatementEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Presentation\Http\Traits\PaymentStatement;
use Carbon\Carbon;


class FileStatementRepository implements FileStatementRepositoryInterface
{

    use PaymentStatement;

    /**
     * @param int $id
     * @return bool
     */
    public function createStatementBk(int $id): bool
    {
        $fileEloquent = FileEloquentModel::with(['statement'], ['itineraries'])->findOrFail($id);
        // $fileEloquent->total_amount = $fileEloquent->itineraries->sum('total_amount');
        // $fileEloquent->save();

        if(!$fileEloquent->statement){

            // $time = strtotime($fileEloquent->itineraries->min('date_in'));    
            $time = strtotime($fileEloquent->date_in);
            $deadline = NULL;
            if(!$fileEloquent->client_have_credit)
            {
                $deadline = date('Y-m-d', strtotime('-10 days', $time));
            }

            $total = $fileEloquent->total_amount; //$fileEloquent->itineraries->sum('total_amount');
            $fileStatementModel = FileStatementEloquentModel::create([
                'file_id' => $fileEloquent->id,
                'date' => date('Y-m-d'),
                'deadline' => $deadline,
                'total' => $total
            ]);
            $details = $this->getDetails($total, $fileEloquent->adults, $fileEloquent->children);

            if(count($details)>0){
                foreach($details as $detail)
                {
                    FileStatementDetailEloquentModel::create([
                        'file_statement_id' => $fileStatementModel->id,
                        'description' => $detail['description'],
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'amount' => $detail['amount']
                    ]);
                }
            }
        }

        return true;
    }

    public function getDetails($total, $adults, $children=0): array
    {
        $price_pers = $total / ($adults+ $children);
        $details = [];


        if($adults>0){
            $amount = ($adults * $price_pers);
            array_push($details,[
                'description' => 'Tarifa neta por adulto',
                'quantity' => $adults,
                'unit_price' => $price_pers,
                'amount' => $amount
            ]);
        }

        if($children>0){
            $amount = ($children * $price_pers);
            array_push($details,[
                'description' => 'Tarifa neta por niño',
                'quantity' => $children,
                'unit_price' => $price_pers,
                'amount' => $amount
            ]);
        }
        return $details;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function createStatement(int $id, array $params): bool
    {
         
        $fileEloquent = FileEloquentModel::with(['statement.details'], ['itineraries'])->findOrFail($id);

        if($fileEloquent->statement){

            throw new \DomainException("There is already a statement");
        }

        $deadline = isset($params['deadline']) ? $params['deadline']: NULL; 

        if($deadline === NULL)
        {             
            $time = strtotime($fileEloquent->date_in);
             
            if(!$fileEloquent->client_have_credit)
            {
                $deadline = date('Y-m-d', strtotime('-10 days', $time));
            }
        }
    
        $statement_details = collect($params['details']);

        $statement_details = $statement_details->map(function($detail){             
            $detail['amount'] = $detail['quantity'] * $detail['unit_price'];
            return $detail;
        });

        $new_total_amount = $statement_details->sum('amount');

 
        DB::transaction(function () use ($fileEloquent, $new_total_amount, $statement_details, $deadline, $params) { 
 
            // actualizamos el log
            $statement_log = [];                   
                 
            $fileStatementEloquement = FileStatementEloquentModel::create([
                'file_id' => $fileEloquent->id, 
                'user_id' => $fileEloquent->executive_id, 
                'date' => date('Y-m-d'),
                'deadline' => $deadline, 
                'user_id' => $params['user_id'],
                'total' => $new_total_amount,
                'file_statement_reason_modification_id' => NULL,
                'file_statement_reason_modification_others' => NULL,
                'logs'=> json_encode($statement_log)
            ]);

            foreach($statement_details as $detail)
            {                 
                FileStatementDetailEloquentModel::create([
                    'file_statement_id' => $fileStatementEloquement->id,
                    'description' => $detail['description'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'amount' => $detail['amount']
                ]);            
            }            
             
        });

        return true;
    }
    
    /**
     * @param int $id
     * @return bool
     */
    public function updateStatement(int $id, array $params): bool
    {
        $restore = isset($params['restore']) ? $params['restore']: false;
        $deadline = isset($params['deadline']) ? $params['deadline']: false;

        $fileEloquent = FileEloquentModel::with(['statement.details'], ['itineraries'])->findOrFail($id);

        if(!$fileEloquent->statement){

            throw new \DomainException("There is no initial statement");
        }

        $total_amount = $fileEloquent->statement->total;

        $statement_details = collect($params['details']);

        $statement_details = $statement_details->map(function($detail) use ($restore){
            if($restore === true){
                $detail['id'] = null; // cuando restauramos eliminamos el detalle actual y insertamos como nuevos
            }
            $detail['amount'] = $detail['quantity'] * $detail['unit_price'];
            return $detail;
        });

        $new_total_amount = $statement_details->sum('amount');

        // if($new_total_amount<$total_amount and $restore == false)
        // {
        //     throw new \DomainException("The new total cannot be less than the statement total");
        // }
         
        DB::transaction(function () use ($fileEloquent, $new_total_amount, $statement_details, $deadline, $params) { 
 
            // actualizamos el log
            $statement_log = [];
            if(isset($fileEloquent->statement->logs) and $fileEloquent->statement->logs){
                $statement_log = json_decode($fileEloquent->statement->logs);
            }

            $statementAdd = clone $fileEloquent->statement;
            unset($statementAdd->logs);
            array_push($statement_log,  $statementAdd);
            // $fileEloquent->statement->user_id = 1; // user admin
            // fin log


            $statement_db_details = $fileEloquent->statement->details;
            foreach($statement_details as $detail)
            {
                if(isset($detail['id'])){

                    $update_detail = FileStatementDetailEloquentModel::find($detail['id']);
                    if(!$update_detail){
                        throw new \DomainException("no exists row");
                    }

                    $update_detail->description = $detail['description'];
                    $update_detail->quantity = $detail['quantity'];
                    $update_detail->unit_price = $detail['unit_price'];
                    $update_detail->amount = $detail['amount'];
                    $update_detail->save();
                }else{

                    FileStatementDetailEloquentModel::create([
                        'file_statement_id' => $fileEloquent->statement->id,
                        'description' => $detail['description'],
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'amount' => $detail['amount']
                    ]);
                }

            }

            foreach($statement_db_details as $statement_db_detail){
                $find = false;
                foreach($statement_details as $detail)
                {
                    if(isset($detail['id']) and  $statement_db_detail->id == $detail['id']){
                        $find = true;
                    }
                }
                if($find == false){
                    FileStatementDetailEloquentModel::find($statement_db_detail->id)->delete();
                }
            }

            $fileStatementEloquement = FileStatementEloquentModel::find($fileEloquent->statement->id);
            if($deadline){
               $fileStatementEloquement->deadline = $deadline;
            }

            $fileStatementEloquement->total = $new_total_amount;
            $fileStatementEloquement->user_id = $params['user_id'];
            $fileStatementEloquement->file_statement_reason_modification_id = NULL;
            $fileStatementEloquement->file_statement_reason_modification_others = NULL;
            $fileStatementEloquement->logs = json_encode($statement_log);
            $fileStatementEloquement->save();

        });

        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateManuallyStatement(int $id, array $params): bool
    {
        $restore = isset($params['restore']) ? $params['restore']: false;

        $fileEloquent = FileEloquentModel::with(['statement.details'], ['itineraries'])->findOrFail($id);

        if(!$fileEloquent->statement){

            throw new \DomainException("There is no initial statement");
        }

        DB::transaction(function () use ($fileEloquent, $params) {

            // actualizamos el log
            $statement_log = [];
            if(isset($fileEloquent->statement->logs) and $fileEloquent->statement->logs){
                $statement_log = json_decode($fileEloquent->statement->logs);
            }

            array_push($statement_log,  clone $fileEloquent->statement);

            $fileStatementEloquement = FileStatementEloquentModel::find($fileEloquent->statement->id);
            $fileStatementEloquement->user_id = $params['user_id'];
            $fileStatementEloquement->file_statement_reason_modification_id = isset($params['file_statement_reason_modification_id']) ? $params['file_statement_reason_modification_id'] : NULL;
            $fileStatementEloquement->file_statement_reason_modification_others = isset($params['file_statement_reason_modification_others']) ? $params['file_statement_reason_modification_others'] : NULL;
            $fileStatementEloquement->logs = json_encode($statement_log);
            $fileStatementEloquement->save();

        });

        return true;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function report(int $file_id): array
    {
        $stella = new ApiGatewayExternal();

        $file = FileEloquentModel::with(['statement.details', 'credit_notes.details', 'debit_notes.details','itineraries'])->where("id",$file_id)->first();

        $statement_payments_received = (array) $stella->statement_payments_received($file->file_number); //'365704'
     
        $payment_received = count($statement_payments_received['payments']) > 0 ? $statement_payments_received['payments'] : [];
        $total_pagos = count($statement_payments_received['payments']) > 0 ? $statement_payments_received['total_amount'] : 0;
        // foreach($statement_payments_received as $payment){

        //     $tipdoc = trim($payment->tipdoc);

        //     if($tipdoc == 'RE'){

        //         array_push($payment_received, [
        //             'date' => $payment->fecha,
        //             'descri' => $payment->descri,
        //             'type_transac' => 'Transferencia',
        //             'import' => $payment->habelo
        //         ]);
        //         $total_pagos = $total_pagos  + $payment->habelo;
        //     }

        //     if($tipdoc == 'TR'){
        //         $import = 0;
        //         if($payment->debemo > 0){
        //             $import = $payment->debeba * (-1);
        //         }else{
        //             $import = $payment->habeba;
        //         }

        //         $total_pagos = $total_pagos  + $import;

        //         array_push($payment_received, [
        //             'date' => $payment->fecha,
        //             'descri' => $payment->descri,
        //             'type_transac' => 'Transferencia de pago tesoreria',
        //             'import' => $import
        //         ]);
        //     }

        // }
        
        $result = [
            'statement' => 0,
            'credit_note' => [],
            'debit_note' => [],
            'payment_received' => [],
            'balance' => 0
        ];

        if($file->statement)
        {
            $credit_notes = isset($file->credit_notes) ? $file->credit_notes->total : 0;
            $debit_notes = isset($file->debit_notes) ? $file->debit_notes->total : 0;
       
            $balance = $file->statement->total - $total_pagos;
            $balance = $balance - $credit_notes;
            $balance = $balance + $debit_notes;
            $balance = $balance * -1;

            $result = [
                'statement' => number_format($file->statement->total, 2, '.', '') ,
                'credit_note' => isset($file->credit_notes) ? $file->credit_notes : [],
                'debit_note' => isset($file->debit_notes) ? $file->debit_notes : [],
                'payment_received' => $payment_received,
                // 'payment_received_results' => $statement_payments_received,
                'balance' => number_format($balance, 2, '.', '')
                // 'concepts' => $this->concepts($file)
            ];

        }

        return $result;
        
    }

        /**
     * @param int $id 
     * @return bool
     */
    public function document_details_for_stella(int $file_id): array
    {         
         
        $file = FileEloquentModel::with(
            ['statement.details'], 
            ['credit_notes.details'], 
            ['debit_notes.details'] 
        )->where("id",$file_id)->first(); 
 
        $details = [];        
        foreach($file->statement->details as $statement)
        {
            array_push($details, [
                'document_type' => 'ST',
                'description' => $statement->description,
                'quantity' => $statement->quantity,
                'amount' => $statement->unit_price,
                'deadline' => $file->statement->deadline
            ]);
        }

        if(isset($file->credit_notes->details))
        {
            foreach($file->credit_notes->details as $credit_note)
            {
                array_push($details, [
                    'document_type' => 'NC',
                    'description' => $credit_note->description,
                    'quantity' => $credit_note->quantity,
                    'amount' => $credit_note->unit_price,
                    'deadline' => $file->statement->deadline
                ]);
            }
        }

        if(isset($file->debit_notes->details))
        {
            foreach($file->debit_notes->details as $debit_note)
            {
                array_push($details, [
                    'document_type' => 'ND',
                    'description' => $debit_note->description,
                    'quantity' => $debit_note->quantity,
                    'amount' => $debit_note->unit_price,
                    'deadline' => $file->statement->deadline
                ]);
            }
        }
        
        $response = [              
            'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file->file_number."/statements",
            'method' => 'put',
            'stela' => [
                'deadline' => Carbon::parse($file->statement->deadline)->format('d/m/Y') ,
                'details' => $details
            ]               
        ]; 

        return $response;
    }

    public function concepts($file): array {

        $concepts = [];
        // foreach($file->statement->details as $detail){
        //     array_push($concepts, $detail['description']);
        // }

        array_push($concepts, "PRECIO NETO POR PERSONA - HAB SIMPLE");
        array_push($concepts, "PRECIO NETO POR PERSONA - HAB DOBLE");
        array_push($concepts, "PRECIO NETO POR PERSONA - HAB TRIPLE");
        array_push($concepts, "TICKETS AÉREOS DOMÉSTICOS");
        array_push($concepts, "TICKETS AÉREOS INTERNACIONALES");
        array_push($concepts, "OTROS");

        // array_push($concepts, "NET PRICE PER PERSON - SINGLE ROOM");
        // array_push($concepts, "NET PRICE PER PERSON - DOUBLE ROOM");
        // array_push($concepts, "NET PRICE PER PERSON - TRIPLE ROOM");
        // array_push($concepts, "DOMESTIC AIRLINE TICKETS");
        // array_push($concepts, "INTERNATIONAL AIRLINE TICKETS");
        // array_push($concepts, "OTHERS");

        return $concepts;
    }



    /**
     * @param int $id
     * @return bool
     */
    public function details(int $file_id): array
    {

        $aurora = new AuroraExternalApiService();


        $fileEloquent = FileEloquentModel::with(['statement.details', 'credit_notes.details', 'debit_notes.details'])->findOrFail($file_id)->toArray();
        $statement_logs = [];        
        if((isset($fileEloquent['statement']['logs']) and $fileEloquent['statement']['logs'] =! ''))
        {          
            $statement_logs = json_decode($fileEloquent['statement']['logs'], true);
            if(!is_array($statement_logs))
            {
                $statement_logs = [];
            }
        }


        foreach($statement_logs as $index => $logs){
            // $statement_logs[$index]['index'] = $index + 1;

            // array_unshift($statement_logs[$index], ["index" => $index + 1]);

            unset($statement_logs[$index]['id']);
            unset($statement_logs[$index]['logs']);
            unset($statement_logs[$index]['deleted_at']);

            $statement_logs[$index] = ["index" => $index + 1 , ...$statement_logs[$index]];
        }

        $data = [
            'executive_code' => '',
            'hotel_id' => '',
            'client_id' => $fileEloquent['client_id']
        ];
        $response = $aurora->searchByCommunication($data);

        if((isset($fileEloquent['statement'])))
        {    
            $fileEloquent['statement']['logs'] = $statement_logs;
        }
        
        $fileEloquent['client_aurora'] = $response->client;
        return $fileEloquent;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function blocked(): array
    {

        $FilesEloquentModel = FileEloquentModel::with(
            ['statement.details'],
            ['credit_notes.details'],
            ['debit_notes.details'],
            ['itineraries']
        )
        ->where("status", 'BL')->get();

        $payments = $this->getPayments($FilesEloquentModel);

        $files = [];

        foreach($FilesEloquentModel as $file){

            $payment_imports = 0;
            if(isset($payments[$file->file_number])){
                $payment_imports = $payments[$file->file_number]['total_payment'];
            }

            $credit_notes = isset($file->credit_notes) ? $file->credit_notes->sum('total') : 0;
            $debit_notes = isset($file->debit_notes) ? $file->debit_notes->sum('total') : 0;

            $statement = $file->statement->total - $credit_notes + $debit_notes;
            $balance = $statement - $payment_imports;

            array_push($files, [
                'id' => $file->id,
                'client' => $file->client_code,
                'file_number' => $file->file_number,
                'file_description' => $file->description,
                'executive_code' => $file->executive_code,
                'date_in' => $file->date_in,
                'import' => $file->statement->total,
                'balance' => $balance,
                'status' => "Blocked",
            ]);
        }

        return $files;

    }


    /**
     * @param int $id
     * @return bool
     */
    public function desBlocked($params): bool
    {

        $files = FileEloquentModel::whereIn("id", $params)->get();
        foreach($files as $file)
        {
            if($file->status == 'BL')
            {
                $file->status = 'OK';
                $file->save();
            }
        }

        return true;

    }


    public function getPayments($files){

        $file_codes = [];
        foreach($files as $file){
            array_push($file_codes, intval($file['file_number']));
        }
        if(count($file_codes)>0)
        {
            return $this->getPaymentArray($file_codes);
        }

        return  [];

    }

}
