<?php

namespace App\Http\Multichannel\Hyperguest\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HyperguestGatewayService
{
    protected $client;
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiBaseUrl = config('multichannel.hyperguest.base_url');
    }

    /**
     * @throws GuzzleException
     */
    private function post(string $endpoint, array $data = [])
    {
        try {
            $baseUrl = "$this->apiBaseUrl/gateway";
            $response = $this->client->request('POST', $baseUrl . $endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'x-multichannel-internal-api-key' => config('multichannel.hyperguest.api_key'),
                ],
                'json' => $data,
                'allow_redirects' => false,
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function search(array $params)
    {
        return $this->post('/search', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function searchAvailability(array $params)
    {
        return $this->post('/search/availability', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function getHotels(array $params)
    {
        return $this->post('/hotel', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function getHotelsFilter(array $params)
    {
        return $this->post('/hotel/filter', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function getHotelDetail(array $params)
    {
        return $this->post('/hotel/detail', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function getReservationDetail(array $params)
    {
        return $this->post('/reservation/detail', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function createReservation(array $params)
    {
        return $this->post('/reservation/create', $params);
    }

    /**
     * @throws GuzzleException
     */
    public function cancelReservation(array $params)
    {
        return $this->post('/reservation/cancel', $params);
    }

    public function getStaticHotels(?string $country = null, ?array $hotelIds = null): array
    {
        $url = null;
        try {
            $staticApiUrl = config('multichannel.hyperguest.static_api_url');
            $apiKey = config('multichannel.hyperguest.api_key_hyperguest');

            if (empty($apiKey)) {
                throw new \Exception('API_KEY_HYPERGUEST no configurado en el archivo .env');
            }

            $url = $staticApiUrl . '/hotels.json';

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'timeout' => 300, // 5 minutos
                'read_timeout' => 300,
            ]);

            $body = $response->getBody()->getContents();
            $hotels = json_decode($body, true);

            if (!is_array($hotels)) {
                throw new \Exception('Respuesta inválida del endpoint estático de Hyperguest');
            }

            // Aplicar filtros si se proporcionan
            if ($country !== null) {
                $hotels = array_filter($hotels, function($hotel) use ($country) {
                    return isset($hotel['country']) && $hotel['country'] === $country;
                });
            }

            if ($hotelIds !== null && !empty($hotelIds)) {
                $hotels = array_filter($hotels, function($hotel) use ($hotelIds) {
                    return isset($hotel['hotel_id']) && in_array($hotel['hotel_id'], $hotelIds);
                });
            }

            // Reindexar array después de filtrar
            return array_values($hotels);

        } catch (GuzzleException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getStaticHotelDetail($hotelId)
    {
        $url = null;
        try {
            $staticApiUrl = config('multichannel.hyperguest.static_api_url');
            $apiKey = config('multichannel.hyperguest.api_key_hyperguest');

            if (empty($apiKey)) {
                throw new \Exception('API_KEY_HYPERGUEST no configurado en el archivo .env');
            }

            $url = $staticApiUrl . '/' . $hotelId . '/property-static.json';

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'timeout' => 60, // 1 minuto para detalle individual
                'read_timeout' => 60,
            ]);

            $body = $response->getBody()->getContents();
            $hotelDetail = json_decode($body, true);

            if (!is_array($hotelDetail)) {
                return null;
            }

            return $hotelDetail;

        } catch (GuzzleException $e) {
            // Si es 404, el hotel no existe en el endpoint estático
            if ($e->getCode() === 404) {
                return null;
            }

            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
