<?php
 
namespace Src\Modules\File\Presentation\Http\Traits;

use Illuminate\Support\Facades\Log;

trait Amazon
{
    function publish_sqs($data, $return = FALSE)
    {
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');

        $response = [];
        
        try
        {        
            $endpoint = config('services.amazon.domain'). '/sqs/publish';
            $client = new \GuzzleHttp\Client();
            $response_sqs = $client->request('POST',
                $endpoint, [
                    "json" => $data,
                    "headers" => ['Content-Type' => 'application/json']
                ]);
            $response = $response_sqs->getBody()->getContents();
        }
        catch(\Exception $ex)
        {

            $response = [
                'error' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'file' => $ex->getFile(),
            ];
          
        }
        finally
        {
            if($return)
            {
                return $response;
            }
        }
    }
}
