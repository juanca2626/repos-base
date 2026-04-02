<?php

namespace App\Jobs;

use App\GenerateRatesInCalendar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\GenerateRatesCalendar;
use App\Http\Traits\Aurora3;

class ProcessRateInFilesCalendaries implements ShouldQueue
{
    use GenerateRatesCalendar, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Aurora3;

    public $generate_rates_in_calendar_id;
    public $files;
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($generate_rates_in_calendar_id, $files)
    {
        $this->connection = 'clone_hotel_rate_protection';
        $this->queue = 'rate_protection';
        $this->generate_rates_in_calendar_id = $generate_rates_in_calendar_id;
        $this->files = $files;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $file = 'people.txt';  // debe de estar creado en la raiz del proyecto no dentro de public
        // $current = file_get_contents($file);

        $generate_rates_in_calendar =  GenerateRatesInCalendar::find($this->generate_rates_in_calendar_id);
        if($generate_rates_in_calendar->status == "2"){
        
            ini_set('post_max_size', '64M');
            ini_set('upload_max_filesize', '64M');
    
            $response = [];
            
            $token = $this->getToken();

            try
            {            
                $endpoint = config('services.aurora_files.domain'). 'api/v1/commercial/files-ms/files/update-hotel-rates';
                $client = new \GuzzleHttp\Client();
                $response_sqs = $client->request('POST',
                    $endpoint, [
                        "json" => $this->files,
                        "headers" => [
                            'Content-Type' => 'application/json',
                            'Authorization' => "Bearer {$token}"
                        ]
                    ]);
                $response = $response_sqs->getBody()->getContents();

                // $current .= json_encode($response)."n"; 
                // file_put_contents($file, $current);

            }
            catch(\Exception $ex)
            { 
                $response = [
                    'error' => $ex->getMessage(),
                    'line' => $ex->getLine(),
                    'file' => $ex->getFile(),
                ];
                // $current .= json_encode($response)."n"; 
                // file_put_contents($file, $current);                
    
            }
            finally
            {                
                return $response;                 
            }
        }
    }

    public function failed($exception)
    {
 

    }



}
