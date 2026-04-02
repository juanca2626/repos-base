<?php


namespace App\Http\Stella;


use App\Http\Stella\Traits\AuthorizesStellaApiRequests;
use App\Http\Stella\Traits\ConsumesExternalStellaApi;
use App\Http\Stella\Traits\InteractsWithStellaApiResponses;

class StellaService
{
    use ConsumesExternalStellaApi, AuthorizesStellaApiRequests, InteractsWithStellaApiResponses;
    /**
     * The url from which send the requests
     * @var string
     */
    protected $baseChip;
    protected $baseUri;
    protected $baseFilesOnedb;

    public function __construct()
    {
        $this->baseChip = 'stela';
        $this->baseUri = config('services.stella.domain');
        $this->baseFilesOnedb = config('services.files_onedb.domain');
    }

    public function set_baseURI($base_uri)
    {
        $this->baseUri = $base_uri;
    }

    public function store_client($formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/clients', [], $formParams, ['Content-Type' => 'application/json'], true, true);
    }

    public function search_flights($nrofile)
    {
        return $this->makeRequest('GET',
            'api/v1/flights/find/' . $nrofile, [], [], ['Content-Type' => 'application/json'], true, true);
    }

    public function passengers_list($nrofile)
    {
        return $this->makeRequest('GET',
            'api/v1/files/' . $nrofile.'/passengers_list', [], [], ['Content-Type' => 'application/json'], true, true);
    }

    public function save_flight($nrofile, $nroite, $params)
    {
        return $this->makeRequest('POST',
            'api/v1/flights/save/' . $nrofile . '/' . $nroite, [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function search_destinations($params)
    {
        return $this->makeRequest('GET',
            'api/v1/flights/origins', $params, [], ['Content-Type' => 'application/json'], true, true);
    }

    public function search_airlines($params)
    {
        return $this->makeRequest('GET',
            'api/v1/flights/airlines', $params, [], ['Content-Type' => 'application/json'], true, true);
    }

    public function search_paxs($params)
    {
//     return $this->makeRequest('GET', 'api/v1/passenger/conpax', $params, [], ['Content-Type' => 'application/json'], true);
        $this->baseChip = 'files';
        return $this->makeRequest('GET',
            'files/'.$params['nroref'].'/passengers', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function status_pax($params)
    {
        return $this->makeRequest('GET',
            'api/v1/passenger/stapax', $params, [], ['Content-Type' => 'application/json'], true);
    }

//    public function register_paxs($formParams)
    public function register_paxs($file, $formParams)
    {
//        return $this->makeRequest('POST',
//            'api/v1/passenger/regpax', [], $formParams, [], ['Content-Type' => 'application/json'], true);
        $this->baseChip = 'files';
        return $this->makeRequest('POST',
            'files/'.$file.'/passengers', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function search_file_services($nrofile, $clasif, $include_xl, $nroites_not_in, $confirmation)
    {
        $params['clasif'] = $clasif; // all
        $params['include_xl'] = ($include_xl) ? true : false;
        $params['nroites_not_in'] = $nroites_not_in;
        $params['confirmation'] = $confirmation;

        return $this->makeRequest('GET',
            'api/v1/files/'.$nrofile.'/services', $params, [], ['Content-Type' => 'application/json'], true);
    }

    public function get_service_complements($nrofile, $nroite)
    {
        return $this->makeRequest('GET',
            'api/v1/files/'.$nrofile.'/services/'.$nroite.'/complements', [], [],
            ['Content-Type' => 'application/json'], true);
    }

    public function save_service_file($nrofile, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile.'/service', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function update_service_file($nrofile, $nroite, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile.'/service/'.$nroite, [], $formParams, ['Content-Type' => 'application/json'],
            true);
    }

    public function delete_service_file($nrofile, $nroite)
    {
        return $this->makeRequest('DELETE',
            'api/v1/files/'.$nrofile.'/service/'.$nroite, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_services_by_types($formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/services/filter', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function search_services_component($formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/services/component', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function update_file($nrofile, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile, [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function update_passengers_file($nrofile, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile.'/update_passengers_file', [], $formParams, ['Content-Type' => 'application/json'],
            true);
    }

    public function save_passengers_file($nrofile, $nroite, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile.'/passengers/'.$nroite, [], $formParams, ['Content-Type' => 'application/json'],
            true);
    }

    public function get_file_accommodations($nrofile)
    {
        $this->baseChip = 'files';
        return $this->makeRequest('GET',
            'files/'.$nrofile.'/services/accommodations', [], [], ['Content-Type' => 'application/json'],
            true);
    }

    public function search_remarks($code)
    {
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/remarks', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_restrictions($code)
    {
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/restrictions', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_bastar($code, $formParams)
    { // t04
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/bastar', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function search_exchange()
    {
        return $this->makeRequest('GET',
            'api/v1/services/exchange', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function filter_tour($code)
    { // t06b
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/filter_tour', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_bastar_service($code, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/filter_bastar', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function search_bastars_service($code, $formParams)
    { // t04
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/bastars', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function search_components($code, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/services/'.$code.'/components', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function find_component($code)
    {
        return $this->makeRequest('GET',
            'api/v1/services/components/'.$code, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function cancel_components($nroref, $nroite)
    {
        return $this->makeRequest('DELETE',
            'api/v1/files/'.$nroref.'/components/'.$nroite, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function create_component($code, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$code.'/components', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function save_component($code, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$code.'/save_component', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function delete_component($code, $nroite)
    {
        return $this->makeRequest('DELETE',
            'api/v1/files/'.$code.'/delete_component/'.$nroite, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function find_file($nroref)
    {
        return $this->makeRequest('GET',
            'api/v1/files/'.$nroref, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function board_files($params)
    {
        return $this->makeRequest('GET',
            'api/v1/boardFiles/boardfiles', $params, [], ['Content-Type' => 'application/json'], true);
    }

    public function search_services_by_paxs($nrofile, $paxs)
    {
        return $this->makeRequest('GET',
            'api/v1/services/'.$nrofile.'/paxs/'.$paxs, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function save_room_paxs_by_nroite($nrofile, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile.'/rooms_pax', [], $formParams, ['Content-Type' => 'application/json'], true);
    }

    public function save_room_paxs_general($nrofile, $formParams)
    {
        return $this->makeRequest('POST',
            'api/v1/files/'.$nrofile.'/rooms_pax_general', [], $formParams, ['Content-Type' => 'application/json'],
            true);
    }

    public function create_relation_t21($nrofile, $nroite, $formParams)
    {
        return $this->makeRequest('POST',
            sprintf('api/v1/files/%s/relation_t21/%s', $nrofile, $nroite), [], $formParams, ['Content-Type' => 'application/json'],
            true);
    }

    public function update_relation_t21($nrofile, $nroite, $formParams)
    {
        return $this->makeRequest('POST',
            sprintf('api/v1/files/%s/update_relation_t21/%s', $nrofile, $nroite), [], $formParams,
            ['Content-Type' => 'application/json'], true);
    }

    public function getExecutivesByClient($client_code)
    {
        return $this->makeRequest('GET',
            'api/v1/clients/'.$client_code.'/executives', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function reference_quote($nrofile)
    {
        return $this->makeRequest('GET',
            'api/v1/files/'.$nrofile.'/reference_quote', [], [], ['Content-Type' => 'application/json'], true);
    }

    /**
     * Genera el numero de file en stella
     *
     * @access public
     * @return string
     */
    public function generateFileNumber()
    {
        return $this->makeRequest('GET',
            'api/v1/stelaFiles/generateFileNumber', [], [], ['Content-Type' => 'application/json'], true);
    }


    /**
     * Genera el file en stella
     *
     * @access public
     * @param $params
     * @return string
     */
    public function createFileStella($params)
    {
        return $this->makeRequest('POST',
            'api/v1/stelaFiles/aurora2file', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function cancelFileStella($params)
    {
        return $this->makeRequest('POST',
            'api/v1/stelaFiles/canaur2file', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_search_logs($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/search_logs', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_search_all_logs($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/search_all_logs', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_report_all_logs($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/report_all_logs', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_search_paxs_by_file($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/search_paxs_by_file', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_mailing_job($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/mailing_job', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_services_file($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/services_file', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_store($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/store', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_store_logs($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/store_logs', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function getCertificationCarbonByFile($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/getCertificationCarbonByFile', [], $params, ['Content-Type' => 'application/json'], true,
            true);
    }

    public function getTextoSkeleton($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/getTextoSkeleton', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function masi_data($params)
    {
        return $this->makeRequest(
            'GET',
            'api/v1/masi/files_data',
            $params,
            [],
            ['Content-Type' => 'application/json'],
            true,
            true
        );
    }

    public function masi_find_mailing($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/find_mailing', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function relate_coti_order($params)
    {
        return $this->makeRequest('POST',
            'api/v1/quotes/relate_order', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function getCitiesByIsoCountry($iso)
    {
        return $this->makeRequest('GET',
            'api/v1/countries/'.$iso.'/cities', [], [], ['Content-Type' => 'application/json'], true, true);
    }

    public function getKamsByClient($client_code)
    {
        return $this->makeRequest('GET',
            'api/v1/clients/'.$client_code.'/kams', [], [], ['Content-Type' => 'application/json'], true,true);
    }

    public function getKamByExecutive($executive_code)
    {
        return $this->makeRequest('GET',
            'api/v1/executives/'.$executive_code.'/kam', [], [], ['Content-Type' => 'application/json'], true,true);
    }

    public function search_executives($params)
    {
        return $this->makeRequest('POST',
            'api/v1/executives/search', [], $params, ['Content-Type' => 'application/json'], true,true);
    }

    public function masi_get_dates_services_by_file($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/get_dates_services_by_file', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function get_file_information($params)
    {
        return $this->makeRequest('POST',
            'api/v1/masi/file/information', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function migrate_files_non_exists($params)
    {
        return $this->makeRequest('POST',
            'api/v1/files/migration/non_exists', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function migrate_files_news($params)
    {
        return $this->makeRequest('POST',
            'api/v1/files/migration/news', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function migrate_files_exists($params)
    {
        return $this->makeRequest('POST',
            'api/v1/files/migration/exists', [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function clients_verify_credit($code)
    {
        return $this->makeRequest('GET',
            'api/v1/clients/' . $code . '/credit', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_teams()
    {
        return $this->makeRequest('GET',
            'api/v1/executives/teams', [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_orders($params)
    {
        return $this->makeRequest('POST',
            'api/v1/orders/search', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function search_boss($params)
    {
        return $this->makeRequest('POST',
            'api/v1/executives/boss', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function file_pay_status($params)
    {
        return $this->makeRequest('POST',
            'api/v1/files/pay_status', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function find_file_service($file, $service, $date)
    {
        return $this->makeRequest('GET',
            'api/v1/files/'. $file .'/service/' . $service . '/' . $date, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function search_surveys($params)
    {
        return $this->makeRequest('POST',
            'api/v1/files/surveys', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function equivalences_services($params)
    {
        return $this->makeRequest('POST',
            'api/v1/equivalences/services', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function customer_service_pendings($params)
    {
        return $this->makeRequest('GET',
            'api/v1/customerService/pendings', $params, [], ['Content-Type' => 'application/json'], true);
    }

    public function customer_service_occurrences_pendings($params)
    {
        return $this->makeRequest('GET',
            'api/v1/customerService/occurrences/pendings', $params, [], ['Content-Type' => 'application/json'], true);
    }

    // BUSINESS REGIONS
    public function list_business_region($params = [])
    {
        return $this->makeRequest('GET','api/v1/businessRegion', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function create_business_region($params = [])
    {
        return $this->makeRequest('POST','api/v1/businessRegion', [], $params, ['Content-Type' => 'application/json'], true);
    }

    public function update_business_region($params = [])
    {
        $code = $params['code'];
        return $this->makeRequest('PUT',"api/v1/businessRegion/{$code}", [], $params, ['Content-Type' => 'application/json'], true, true);
    }

    public function delete_business_region($params = [])
    {
        $code = $params['code'];
        return $this->makeRequest('DELETE',"api/v1/businessRegion/{$code}", [], [], ['Content-Type' => 'application/json'], true);
    }
}
