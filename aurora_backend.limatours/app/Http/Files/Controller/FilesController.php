<?php

namespace App\Http\Files\Controller;
 
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request; 
use App\Http\Traits\Aurora3;

class FilesController extends Controller
{
    use Aurora3;

    public function search_hotel_rates(Request $request)
    {
        try
        {
 
            ini_set('post_max_size', '64M');
            ini_set('upload_max_filesize', '64M');
    
            $response = [];
            
            $token = $this->getToken();

            try
            {            
                $endpoint = config('services.aurora_files.domain'). 'api/v1/commercial/files-ms/files/search-hotel-rates';
                $client = new \GuzzleHttp\Client();
                $response= $client->request('POST',
                    $endpoint, [
                        "json" => [
                            'hotel_id' => $request->input('hotel_id'),
                            'rates_plans_id' => $request->input('rates_plans_id'),
                            'rangos' => $request->input('rangos')
                        ],
                        "headers" => [
                            'Content-Type' => 'application/json',
                            'Authorization' => "Bearer {$token}"
                        ]
                    ]);
                $response = $response->getBody()->getContents();
                $response = json_decode($response);
                
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

            return response()->json($response);
    
        }
        catch(\Exception $ex)
        {
            return json_encode([$this->throwError($ex)]);
        }
    }

    
 
 
}
