<?php

namespace App\Http\Controllers;

use App\EquivalenceService;
use App\MasterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\MasterService as MasterServiceTrait;

class MasterServicesController extends Controller
{
    use MasterServiceTrait;

    public function __construct()
    {
        $this->middleware('permission:masterservices.read')->only('index');
        $this->middleware('permission:masterservices.create')->only('store');
        $this->middleware('permission:masterservices.update')->only('update');
        $this->middleware('permission:masterservices.delete')->only('delete');
    }


    public function index(Request $request){

        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = ($request->has('limit')) ? $request->input('limit') : 10;
        $status = ($request->has('status')) ? [(int)$request->input('status')] : [1, 0];
        $querySearch = $request->input('queryCustom');
        $lang = $request->input('lang');

        $filter_check = ($request->has('filter_check')) ? $request->input('filter_check') : "";

        $parents = $request->input('parents');
        $directs = $request->input('directs');
        $components = $request->input('components');

        $master_services = MasterService::whereIn('status', $status);

        if ($querySearch) {
            $master_services = $master_services->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%'.$querySearch.'%');
                $query->orWhere('classification', 'like', '%'.$querySearch.'%');
                $query->orWhere('city_in_iso', 'like', '%'.$querySearch.'%');
                $query->orWhere('city_out_iso', 'like', '%'.$querySearch.'%');
                $query->orWhere('description', 'like', '%'.$querySearch.'%');
                $query->orWhere('description_large', 'like', '%'.$querySearch.'%');
            });
        }

        if( !($parents && $directs && $components) ){
            if($parents){
                $master_services = $master_services->where('provider_code_bill', "000000");
            }
            if($directs){
                $ids_in_equivalence_services = EquivalenceService::all()->pluck('master_service_id');
                $master_services = $master_services->where('provider_code_bill', "!=", "000000")
                    ->whereIn('id', $ids_in_equivalence_services);
            }
            if($components){
                $ids_in_equivalence_services = EquivalenceService::all()->pluck('master_service_id');
                $master_services = $master_services->where('provider_code_bill', "!=", "000000")
                    ->whereNotIn('id', $ids_in_equivalence_services);
            }
        }

        $master_services->with(['translations']);

        $count = $master_services->count();
        if ($paging === 1) {
            $master_services = $master_services->take($limit)
                ->orderBy('status', 'desc')->orderBy('status_ifx', 'asc')->get();
        } else {
            $master_services = $master_services->skip($limit * ($paging - 1))
                ->orderBy('status', 'desc')->orderBy('status_ifx', 'asc')->take($limit)->get();
        }

        $data = [
            'data' => $master_services,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function show($id){

        $data = MasterService::find($id);

        if($data->provider_code_bill === '000000'){
            $data->type = "parent";
        } else {
            $equivalence_services_count = EquivalenceService::where('master_service_id', $id)->count();
            if ($equivalence_services_count > 0){
                $data->type = "direct";
            } else { // notIn
                $data->type = "component";
            }
        }

        return Response::json($data);
    }

    public function import_more(){
        set_time_limit ( 0 );
        $master_services_codes = MasterService::all()->pluck('code')->toArray();

        if( count($master_services_codes) > 0 ){
            $master_services_codes_string = implode(',', $master_services_codes);
        } else {
            $master_services_codes_string = "";
        }

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request('POST',
            config('services.stella.domain') . 'api/v1/services/services/master', [
                "json" => [
                    'use_limit' => 1,
                    'string_services_codes_not_in' => $master_services_codes_string,
                    'texts' => 1
                ]
            ]);
        $response = json_decode( $response->getBody()->getContents() );

        $services = [];
        foreach ( $response->data as $data ){
            array_push( $services, (array)$data );
        }
        $response = $this->insert_master_services($services);

        return Response::json($response);
    }

    public function cud_master_service(Request $request){

        $master_service = (array)json_decode(json_encode($request->input('data')));

        $master_service["code"] = $master_service["codigo"];
        $master_service["classification"] = $master_service["clasvs"];
        $master_service["city_in_iso"] = $master_service["ciudes"];
        $master_service["city_out_iso"] = $master_service["ciuhas"];
        $master_service["description"] = $master_service["descri"];
        $master_service["description_large"] = $master_service["lintlx"];
        $master_service["type_iso"] = $master_service["tipo"];
        $master_service["country_iso"] = $master_service["codgru"];
        $master_service["provider_code_request"] = $master_service["preped"];
        $master_service["provider_code_bill"] = $master_service["prefac"];
        $master_service["provider_code_voucher"] = $master_service["prevou"];
        $master_service["unit"] = $master_service["unidad"];
        $master_service["pricing_code_time"] = $master_service["diario"];
        $master_service["pricing_code_sale"] = $master_service["paxuni"];
        $master_service["allow_provider_email"] = $master_service["via"];
        $master_service["allow_voucher"] = $master_service["vouche"];
        $master_service["allow_itinerary"] = $master_service["itiner"];
        $master_service["assignable"] = $master_service["asigna"];
        $master_service["nights"] = $master_service["cantnt"];
        $master_service["allow_markup"] = $master_service["basein"];
        $master_service["accounting_account_sale"] = $master_service["ctavta"];
        $master_service["accounting_account_cost"] = $master_service["ctacos"];
        $master_service["intermediation"] = $master_service["interm"];
        $master_service["status_ifx"] = $master_service["operad"];
//        $master_service["codaux"] = $master_service["codaux"];
        $master_service["allow_export"] = $master_service["codfac"];

        $response = $this->insert_master_services([$master_service]);

        return Response::json($response);
    }

}
