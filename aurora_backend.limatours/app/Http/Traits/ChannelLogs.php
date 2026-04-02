<?php

namespace App\Http\Traits;

use App\Channel as ChannelModel;
use App\ChannelsLogs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait ChannelLogs
{

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function Storage()
    {
        return Storage::disk('conector');
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string|resource $contents
     * @param mixed $options
     * @return bool
     */
    public function put($path, $contents, $options = [])
    {
        return $this->Storage()->put($path, $contents, $options);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get_path($path)
    {
        return $this->Storage()->get($path);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        return $this->Storage()->directories($directory, $recursive);
    }

    /**
     * Determine if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        return $this->Storage()->exists($path);
    }

    /**
     * @param $path
     * @return null|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getLogRequest($path, $type_doc = 'xml')
    {
        $logRequest = $path . 'request.' . $type_doc;
        if ($this->exists($logRequest)) {
            return $this->get_path($logRequest);
        } else {
            return null;
        }
    }

    /**
     * @param $path
     * @return null|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getLogResponse($path, $type_doc = 'xml')
    {
        $logDir = $path . 'response.' . $type_doc;
        if ($this->exists($logDir)) {
            return $this->get_path($logDir);
        } else {
            return null;
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param ChannelModel $channel
     * @param $methodName
     * @param $echoToken
     * @param bool $error
     */
    public function putXmlLog(
        Request $request,
        Response $response,
        ChannelModel $channel,
        $methodName,
        $echoToken = null,
        $success = true
    ) {
        try {
            $tokenLog = $this->createToken();

            $logDir = strtolower($channel->code) . '/' . $methodName . '/' . date('Y-m-d') . '/' . $tokenLog . '/';

            $log = new ChannelsLogs();
            $log->request_ip = '127.0.0.1';
            $log->request_headers = json_encode($request->headers->all());
            $log->response_headers = json_encode($response->headers->all());
            $log->log_directory = $logDir;
            $log->echo_token = $echoToken ?? $tokenLog;
            $log->token = $tokenLog;
            $log->method_name = $methodName;
            $log->date = date('c');
            $log->status = $success;
            $log->channel_id = $channel->id;
            $log->created_at = date('Y-m-d H:i:s');
            $log->log_request = ($request->getContent() == '') ? '' : $request->getContent();
            $log->log_response = ($response->getContent() == '') ? '' : $response->getContent();
            $log->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $uri
     * @param string $requestXml
     * @param string $responseXml
     * @param string $requestHeaders
     * @param string $responseHeaders
     * @param $methodName
     * @param $echoToken
     * @param bool $success
     * @param string $type_doc json | xml
     */
    private function putXmlLogAurora(
        $uri,
        $requestXml,
        $responseXml,
        $requestHeaders,
        $responseHeaders,
        $methodName,
        $echoToken = null,
        $success = true,
        $type_doc = 'xml'
    ) {
        try {


            $tokenLog = md5(uniqid(rand(), true));

            $logDir = strtolower('AURORA') . '/' . $methodName . '/' . date('Y-m-d') . '/' . $tokenLog . '/';

            $log = new ChannelsLogs();
            if (is_integer($echoToken) and ($methodName == 'CreandoFile' or $methodName == 'CreandoCliente' or $methodName == 'CancelaFile' or $methodName == 'webhook-n8n-create-file')) {
                $log->reservation_id = $echoToken;
            }
            $log->request_ip = '127.0.0.1';
            $log->request_headers = !$requestHeaders ? '{}' : $requestHeaders;
            $log->response_headers = !$responseHeaders ? '{}' : $responseHeaders;
            $log->log_directory = $logDir;
            $log->method_name = $methodName;
            $log->echo_token = $echoToken ?? $tokenLog;
            $log->token = $tokenLog;
            $log->date = date('c');
            $log->status = $success;
            $log->channel_id = 1;
            $log->type_data = $type_doc;
            if ($type_doc === 'json') {
                $log->log_request = $requestXml;
                $log->log_response = $responseXml;
            } else {
                if ($type_doc === 'xml') {
                    $log->log_request = $requestXml;
                    $log->log_response = $responseXml;
                }
            }
            $log->created_at = date('Y-m-d H:i:s');

        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function putXmlLogHyperguest(
        string $request,
        string $response,
        ChannelModel $channel,
        $methodName,
        $echoToken = null,
        $success = true
    ) {
        try {


            $tokenLog = $this->createToken();

            $logDir = strtolower($channel->code) . '/' . $methodName . '/' . date('Y-m-d') . '/' . $tokenLog . '/';

            $log = new ChannelsLogs();
            $log->request_ip = '127.0.0.1';
            $log->request_headers = '{}';
            $log->response_headers = '{}';
            $log->log_directory = $logDir;
            $log->echo_token = $echoToken ?? $tokenLog;
            $log->token = $tokenLog;
            $log->method_name = $methodName;
            $log->date = date('c');
            $log->status = $success;
            $log->channel_id = $channel->id;
            $log->created_at = date('Y-m-d H:i:s');
            $log->log_request = $request;
            $log->log_response = $response;
            $log->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * @param $dir
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getAllLogChannel(ChannelModel $channel)
    {
        $fileRQ = null;
        $fileRS = null;

//        $dirRQ = $dir . 'request.xml';
//        if ($this->exists($dirRQ)) {
//            $fileRQ = $this->get($dirRQ);
//        }
//
//        $dirRS = $dir . 'response.xml';
//        if ($this->exists($dirRS)) {
//            $fileRS = $this->get($dirRS);
//        }

        return [
            'RQ' => $fileRQ,
            'RS' => $fileRS
        ];
    }

    /**
     * @param $dir
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getXmlLogs($dir)
    {
        $fileRQ = null;
        $fileRS = null;

        $dirRQ = $dir . 'request.xml';
        if ($this->exists($dirRQ)) {
            $fileRQ = $this->get_path($dirRQ);
        }

        $dirRS = $dir . 'response.xml';
        if ($this->exists($dirRS)) {
            $fileRS = $this->get_path($dirRS);
        }

        return [
            'RQ' => $fileRQ,
            'RS' => $fileRS
        ];
    }

    private function getXmlLog($echoToken, $error = false)
    {
        $fileRQ = '';
        $fileRS = '';
        $stotageName = $error ? 'erevmax_error' : 'erevmax_success';
        foreach (Storage::disk($stotageName)->directories() as $dir) {
            $dirRQ = $dir . '/' . $echoToken . '_RQ.xml';
            $dirRS = $dir . '/' . $echoToken . '_RS.xml';

            if (Storage::disk($stotageName)->exists($dirRQ)) {
                $fileRQ = Storage::disk($stotageName)->get($dirRQ);
            }

            if (Storage::disk($stotageName)->exists($dirRS)) {
                $fileRS = Storage::disk($stotageName)->get($dirRS);
            }
        }

        return [
            'RQ' => $fileRQ,
            'RS' => $fileRS
        ];
    }

    private function putReservationXmlLog($reservaHotelId, $xmlRequest, $xmlResponse, $error = false)
    {
        $stotageName = $error ? 'travelclick_reservation_error' : 'travelclick_reservation_success';
        $dir = $reservaHotelId . '/' . $this->getEchoToken();

        Storage::disk($stotageName)->put($dir . '_RQ.xml', $xmlRequest);
        Storage::disk($stotageName)->put($dir . '_RS.xml', $xmlResponse);
    }

    private function getReservationXmlLog($reservaHotelId, $echoToken, $error = false)
    {
        $stotageName = $error ? 'travelclick_reservation_error' : 'travelclick_reservation_success';
        $dir = $reservaHotelId . '/' . $echoToken;

        return [
            'RQ' => Storage::disk($stotageName)->get($dir . 'RQ.xml'),
            'RS' => Storage::disk($stotageName)->get($dir . 'RS.xml')
        ];
    }

    private function putCancelationXmlLog($reservaHotelId, $xmlRequest, $xmlResponse, $error = false)
    {
        $stotageName = $error ? 'travelclick_cancelation_error' : 'travelclick_cancelation_success';
        $dir = $reservaHotelId . '_' . $this->getEchoToken();

        Storage::disk($stotageName)->put($dir . '_RQ.xml', $xmlRequest);
        Storage::disk($stotageName)->put($dir . '_RS.xml', $xmlResponse);
    }

    private function getCancelationXmlLog($methodName, $echoToken, $error = false)
    {
        $stotageName = $error ? 'travelclick_error' : 'travelclick_success';
        $dir = $methodName . '/' . $echoToken;

        return [
            'RQ' => Storage::disk($stotageName)->get($dir . 'RQ.xml'),
            'RS' => Storage::disk($stotageName)->get($dir . 'RS.xml')
        ];
    }
}
