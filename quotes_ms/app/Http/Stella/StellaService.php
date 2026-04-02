<?php

namespace App\Http\Stella;

use App\Http\Stella\Traits\AuthorizesStellaApiRequests;
use App\Http\Stella\Traits\ConsumesExternalStellaApi;
use App\Http\Stella\Traits\InteractsWithStellaApiResponses;
use GuzzleHttp\Exception\GuzzleException;

class StellaService
{
    use ConsumesExternalStellaApi;
    use AuthorizesStellaApiRequests;
    use InteractsWithStellaApiResponses;

    /**
     * The url from which send the requests
     *
     * @var string
     */
    protected mixed $baseUri;

    public function __construct()
    {
        $this->baseUri = config('services.stella.domain');
    }

    /**
     * @throws GuzzleException
     */
    public function store_client($formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/clients',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_flights($nrofile): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/flights/find/'.$nrofile,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function passengers_list($nrofile): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$nrofile.'/passengers_list',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function save_flight($nrofile, $nroite, $params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/flights/save/'.$nrofile.'/'.$nroite,
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_destinations($params): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/flights/origins',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_airlines($params): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/flights/airlines',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_paxs($params): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/passenger/conpax',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function status_pax($params): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/passenger/stapax',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function register_paxs($formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/passenger/regpax',
            [],
            $formParams,
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_file_services($nrofile, $clasif, $include_xl, $nroites_not_in, $confirmation): string
    {
        $params['clasif'] = $clasif; // all
        $params['include_xl'] = (bool) $include_xl;
        $params['nroites_not_in'] = $nroites_not_in;
        $params['confirmation'] = $confirmation;

        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$nrofile.'/services',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function get_service_complements($nrofile, $nroite): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$nrofile.'/services/'.$nroite.'/complements',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function save_service_file($nrofile, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/service',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function update_service_file($nrofile, $nroite, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/service/'.$nroite,
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function delete_service_file($nrofile, $nroite): string
    {
        return $this->makeRequest(
            'DELETE',
            'api/v1/files/'.$nrofile.'/service/'.$nroite,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_services_by_types($formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/services/filter',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_services_component($formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/services/component',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function update_file($nrofile, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile,
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function update_passengers_file($nrofile, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/update_passengers_file',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function save_passengers_file($nrofile, $nroite, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/passengers/'.$nroite,
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function get_accommodation_file_by_service($nrofile, $nroite): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$nrofile.'/passengers/'.$nroite,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_remarks($code): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/remarks',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_restrictions($code): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/restrictions',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_bastar($code, $formParams): string
    { // t04
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/bastar',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_exchange(): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/services/exchange',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function filter_tour($code): string
    { // t06b
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/filter_tour',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_bastar_service($code, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/filter_bastar',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_bastars_service($code, $formParams): string
    { // t04
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/bastars',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_components($code, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/services/'.$code.'/components',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function find_component($code): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/services/components/'.$code,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function cancel_components($nroref, $nroite): string
    {
        return $this->makeRequest(
            'DELETE',
            'api/v1/files/'.$nroref.'/components/'.$nroite,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function create_component($code, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$code.'/components',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function save_component($code, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$code.'/save_component',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function delete_component($code, $nroite): string
    {
        return $this->makeRequest(
            'DELETE',
            'api/v1/files/'.$code.'/delete_component/'.$nroite,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function find_file($nroref): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$nroref,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function board_files($params): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/boardFiles/boardfiles',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_services_by_paxs($nrofile, $paxs): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/services/'.$nrofile.'/paxs/'.$paxs,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function save_room_paxs_by_nroite($nrofile, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/rooms_pax',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function save_room_paxs_general($nrofile, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/rooms_pax_general',
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function create_relation_t21($nrofile, $nroite, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/relation_t21/'.$nroite,
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function update_relation_t21($nrofile, $nroite, $formParams): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/'.$nrofile.'/update_relation_t21/'.$nroite,
            [],
            $formParams,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getExecutivesByClient($client_code): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/clients/'.$client_code.'/executives',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function reference_quote($nrofile): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$nrofile.'/reference_quote',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * Genera el numero de file en stella
     *
     * @throws GuzzleException
     */
    public function generateFileNumber(): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/stelaFiles/generateFileNumber',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * Genera el file en stella
     *
     * @throws GuzzleException
     */
    public function createFileStella($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/stelaFiles/aurora2file',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function cancelFileStella($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/stelaFiles/canaur2file',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_search_logs($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/search_logs',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_search_all_logs($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/search_all_logs',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_report_all_logs($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/report_all_logs',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_search_paxs_by_file($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/search_paxs_by_file',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_mailing_job($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/mailing_job',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_services_file($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/services_file',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_store($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/store',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_store_logs($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/store_logs',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getCertificationCarbonByFile($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/getCertificationCarbonByFile',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getTextoSkeleton($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/getTextoSkeleton',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_find_mailing($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/find_mailing',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function relate_coti_order($params): array
    {
        return $this->makeRequest(
            'POST',
            'api/v1/quotes/relate_order',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getCitiesByIsoCountry($iso): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/countries/'.$iso.'/cities',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getKamsByClient($client_code): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/clients/'.$client_code.'/kams',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getKamByExecutive($executive_code): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/executives/'.$executive_code.'/kam',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_executives($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/executives/search',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function masi_get_dates_services_by_file($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/get_dates_services_by_file',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function get_file_information($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/masi/file/information',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function migrate_files_non_exists($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/migration/non_exists',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function migrate_files_news($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/migration/news',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function migrate_files_exists($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/migration/exists',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function clients_verify_credit($code): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/clients/'.$code.'/credit',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_teams(): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/executives/teams',
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_orders($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/orders/search',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_boss($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/executives/boss',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function file_pay_status($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/pay_status',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function find_file_service($file, $service, $date): string
    {
        return $this->makeRequest(
            'GET',
            'api/v1/files/'.$file.'/service/'.$service.'?date='.$date,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true
        );
    }

    /**
     * @throws GuzzleException
     */
    public function search_surveys($params): string
    {
        return $this->makeRequest(
            'POST',
            'api/v1/files/surveys',
            [],
            $params,
            ['Content-Type' => 'application/json'],
            true
        );
    }
}
