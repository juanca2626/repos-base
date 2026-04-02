<?php


namespace App\Http\Traits;


trait Aurora1
{
    /**
     * @return Array
     */
    public function getUserRegion($codusu) // No se usa..
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $data = [
            'codusu' => $codusu,
        ];

        $params = http_build_query($data);
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchRegion&' . $params);
        $_response = json_decode($request->getBody()->getContents(), true);
        $response = $_response['REGION'];
        return $response;
    }

    public function getExecutivesByRegion($region) // No se usa..
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $data = [
            'region' => $region,
        ];

        $params = http_build_query($data);
        $request = $client->get($baseUrlExtra . '/api/orders/api.php?method=searchExecutivesByRegion&' . $params);
        $_response = json_decode($request->getBody()->getContents(), true);

        $response = [];
        foreach ($_response as $k => $v) {
            $response[] = $v['CODIGO'];
        }
        return $response;
    }

    public function getMasiStadistics($from_date, $to_date, $region, $market, $client_code) // No se usa..
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $data = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'region' => $region,
            'market' => $market,
            'client' => $client_code
        ];

        $params = http_build_query($data);
        $request = $client->get($baseUrlExtra . '/api/masi/api.php?method=searchStadistics&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);
        return $response;
    }

    public function searchDestinations($type, $query) // No se usa..
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $types = ['filterPeru', 'filter'];

        $data = [
            'term' => $query,
        ];

        $params = http_build_query($data);
        $request = $client->get($baseUrlExtra . '/api/quotes/api.php?method=' . $types[$type] . '&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return $response;
    }

    public function searchAirlines($query) // No se usa..
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $data = [
            'term' => $query,
        ];

        $params = http_build_query($data);
        $request = $client->get($baseUrlExtra . '/api/quotes/api.php?method=filterAir&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return $response;
    }

    // No se usa..
    public function updateFlight(
        $nrofile,
        $nroite,
        $origin,
        $destiny,
        $date,
        $departure,
        $arrival,
        $pnr,
        $airline,
        $number_flight,
        $paxs
    ) {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $data = [
            'nrofile' => $nrofile,
            'nroite' => $nroite,
            'origin' => $origin,
            'destiny' => $destiny,
            'date' => $date,
            'departure' => $departure,
            'arrival' => $arrival,
            'pnr' => $pnr,
            'airline' => $airline,
            'number_flight' => $number_flight,
            'paxs' => $paxs
        ];

        $params = http_build_query($data);
        $request = $client->get($baseUrlExtra . '/api/quotes/api.php?method=updateFly&' . $params);
        $response = json_decode($request->getBody()->getContents(), true);

        return $response;
    }

    // No se usa..
    public function removeFlight($flight_id) {}
}
