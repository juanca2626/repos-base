<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Http\Traits\Aurora3;

class TestConnectFiles extends Command
{
    use Aurora3;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:connect_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $token = $this->getToken();
   


        // $endpoint = 'https://nlem5hsika.execute-api.us-east-1.amazonaws.com/api/v1/commercial/files-ms/files/create-basic-file';
        // $client = new \GuzzleHttp\Client();
        // $response_sqs = $client->request('POST',
        //     $endpoint, [
        //         "json" => [],
        //         "headers" => [
        //             'Content-Type' => 'application/json',
        //             'Authorization' => "Bearer {$response->access_token}"
        //         ]
        //     ]);
        // $response = $response_sqs->getBody()->getContents();

        $endpoint = 'https://nlem5hsika.execute-api.us-east-1.amazonaws.com/api/v1/commercial/files-ms/reasons-rate';
        $client = new \GuzzleHttp\Client();
        $response = $client->get($endpoint, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        $response = $response->getBody()->getContents();

        dd($response);

    }
}
