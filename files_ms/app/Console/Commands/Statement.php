<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;   
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel; 
use Src\Modules\File\Presentation\Http\Traits\PaymentStatement;
use Illuminate\Support\Facades\Log;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatusReasonEloquentModel;

class Statement extends Command
{
    use PaymentStatement;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:statement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    /**
     * Execute the job.
     */
    public function handle()
    {
        try{
                                
            $files = FileEloquentModel::with(
                ['statement.details'], 
                ['credit_notes.details'], 
                ['debit_notes.details'], 
                ['itineraries']            
            )
            ->whereHas('statement', function ($query){
                $query->where('deadline','<', date('Y-m-d')); 
            })
            ->where("status", 'OK')->get();
        
            $payments = $this->getPayments($files);
 
            foreach($files as $file){

                $total_pagos = 0;
                if(isset($payments[$file->file_number])){
                    $total_pagos = $payments[$file->file_number]['total_payment'];
                }
                                      
                $credit_notes = isset($file->credit_notes) ? $file->credit_notes->sum('total') : 0;
                $debit_notes = isset($file->debit_notes) ? $file->debit_notes->sum('total') : 0;

                $balance = $file->statement->total - $total_pagos;
                $balance = $balance - $credit_notes;
                $balance = $balance + $debit_notes;
                // $balance = $balance * -1;


                if($balance > 0) // si positivo es porque no se completo el pago
                {
                    $file->status = 'BL';
                    $file->status_reason_id= 15;
                    $file->save();

                    FileStatusReasonEloquentModel::create([
                        'file_id' => $file->id,
                        'status' => 'BL',
                        'status_reason_id' => 15,
                        'user_id' => 1
                    ]);                    
                }
 
            }

        } catch (\DomainException $domainException) {
             dd("error");
        }
    }
 
    public function getPayments($files){

        $file_codes = [];
        foreach($files as $file){
            array_push($file_codes, intval($file['file_number']));
        }

        return $this->getPaymentArray($file_codes);
    }

}

