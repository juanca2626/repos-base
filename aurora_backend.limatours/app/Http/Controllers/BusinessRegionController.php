<?php

namespace App\Http\Controllers;

use App\Client;
use App\Markup;
use App\HotelClient;
use App\ServiceClient;
use App\BusinessRegion;
use App\ClientExecutive;
use App\BusinessRegionUser;
use Illuminate\Http\Request;
use App\BusinessRegionClient;
use App\BusinessRegionsCountry;
use Illuminate\Validation\Rule;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use App\Http\Stella\StellaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BusinessRegionController extends Controller
{
    use Translations;

    private $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
        // $this->middleware('permission:countries.read')->only('index');
        // $this->middleware('permission:countries.create')->only('store');
        // $this->middleware('permission:countries.update')->only('update');
        // $this->middleware('permission:countries.delete')->only('delete');
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index()
    {
        $businessRegion = BusinessRegion::with('countries.translations')->get();
        // $data_stella = $this->stellaService->list_business_region();

        return Response::json([
            'success'   => true,
            'data'      => $businessRegion,
            'count'     => $businessRegion->count(),
            'code'      => 200,
            // 'stella'    => $data_stella,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => [
                'required',
                Rule::unique('business_regions', 'description')->whereNull('deleted_at')
            ]
        ], [
            "description.required" => 'Field description is required.',
            "description.unique"  => 'A record with that description already exists.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'errors'    => $validator->errors(),
                'code'      => 422
            ], 422);
        }

        try{
            DB::beginTransaction();

            $businessRegion = BusinessRegion::create($validator->validated());

            $client = Client::where('code','CLDEPA')->first();

            if ($client) {
                // Añadir markups
                Markup::create([
                    'period'                => now(),
                    'hotel'                 => 0.00,
                    'service'               => 0.00,
                    'status'                => 1,
                    'client_id'             => $client->id,
                    'clone'                 => 0,
                    'business_region_id'    => $businessRegion->id,
                    'created_at'            => now()
                ]);

                $client->businessRegions()->syncWithoutDetaching([$businessRegion->id]);

            }

            // CREACIÓN EN STELA
            $response_stella = $this->stellaService->create_business_region([
                "code"      => $businessRegion->id,
                "descri"    => $request->description,
            ]);

            DB::commit();

            return Response::json([
                'success'   => true,
                'data'      => $businessRegion,
                'code'      => 200,
                'stella'    => $response_stella
            ], 200);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success'   => false,
                'error'     => 'Error interno del servidor',
                'code'      => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Id  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $businessRegion = BusinessRegion::with('countries.translations')->where('id', $id)->get()->first();

        return Response::json([
            'success'   => true,
            'data'      => $businessRegion,
            'code'      => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  BusinessRegion $businessRegion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BusinessRegion $businessRegion)
    {
        $validator = Validator::make($request->all(), [
            'description' => [
                'required',
                Rule::unique('business_regions', 'description')->ignore($businessRegion->id)
            ]
        ], [
            "description.required" => 'Field description is required.',
            "description.unique"  => 'A record with that description already exists.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'errors'    => $validator->errors(),
                'code'      => 422
            ], 422);
        }

        $businessRegion->update([
            'description'   => $request->description,
            'updated_at'    => now()
        ]);

        // ACTUALIZAR STELLA
        $response_stella = $this->stellaService->update_business_region([
            "code"      => $businessRegion->id,
            "descri"    => $request->description,
        ]);

        return Response::json([
            'success'   => true,
            'data'      => $businessRegion,
            'code'      => 200,
            'stella'    => $response_stella
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  BusinessRegion $businessRegion
     * @return \Illuminate\Http\Response
     */
    public function destroy(BusinessRegion $businessRegion)
    {
        try {
            DB::beginTransaction();

            $countryIds = $businessRegion->countries->pluck('id');

            // ELIMINAR USUARIOS ASOCIADOS A LA REGION
            BusinessRegionUser::where('business_region_id', $businessRegion->id)->delete();

            // ELIMINAR LA RELACION ASOCIADOS CON LOS CLIENTES
            BusinessRegionClient::where('business_region_id', $businessRegion->id)->delete();

            // ELIMINAR TODOS LOS MARKUPS ASOCIADOS
            Markup::where('business_region_id', $businessRegion->id)->delete();

            // ELIMINAR LOS EJECUTIVOS
            ClientExecutive::where('business_region_id', $businessRegion->id)->delete();

            // ELIMINAR SERVICIOS BLOQUEADOS ASOCIADOS A LA REGION
            ServiceClient::where('business_region_id', $businessRegion->id)
                // ->where('period', $now) -- se borra de todos los periodos
                ->whereHas('service.serviceOrigin', function($query) use($countryIds) {
                    $query->whereIn('country_id', $countryIds);
                })->delete();

            // ELIMINAR HOTELES BLOQUEADOS ASOCIADOS A LA REGION
            HotelClient::where('business_region_id', $businessRegion->id)
                // ->where('period',$now) -- se borra de todos los periodos
                ->whereHas('hotel', function ($query) use($countryIds) {
                    $query->whereIn('country_id', $countryIds);
                })->delete();

            // ELIMINAR PAISES
            BusinessRegionsCountry::whereIn('country_id', $countryIds)->delete();

            $client = Client::where('code', 'CLDEPA')->first();

            if ($client) {
                $exists = $client->businessRegions()->where('business_region_id', $businessRegion->id)->exists();

                if ($exists) {
                    $client->businessRegions()->detach($businessRegion->id);
                }
            }

            // ELIMINAR EN STELLA (CUIDADO - PUEDE ELIMINAR)
            $response_stella = $this->stellaService->delete_business_region([
                "code"      => $businessRegion->id,
            ]);

            // ELIMINAR LA BUSINESS REGION
            $businessRegion->delete();

            DB::commit();

            return Response::json([
                'success'   => true,
                'data'      => [],
                'code'      => 200,
                'stella'    => $response_stella
            ], 200);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return Response::json([
                'success'   => false,
                'error'     => 'Error al eliminar la Business Region',
                'code'      => 500
            ], 500);
        }
    }

    public function validateDestroy(BusinessRegion $businessRegion)
    {
        $userCount = BusinessRegionUser::where('business_region_id', $businessRegion->id)->count();
        $clientExecutiveCount = ClientExecutive::where('business_region_id', $businessRegion->id)->count();
        $clientCount = BusinessRegionClient::where('business_region_id', $businessRegion->id)->count();
        $markupCount = Markup::where('business_region_id', $businessRegion->id)->count();

        $canDelete = ($userCount === 0 && $clientCount === 0 && $clientExecutiveCount === 0 && $markupCount === 0);
        $message = $canDelete
            ? '¿Estás seguro de eliminar?'
            : $this->buildDeleteMessage($userCount, $clientCount, $markupCount, $clientExecutiveCount);

        return response()->json([
            'success' => $canDelete,
            'message' => $message,
            'code' => 200
        ]);
    }

    protected function buildDeleteMessage(int $userCount, int $clientCount, int $markupCount, int $clientExecutiveCount): string
    {
        if ($userCount === 0 && $clientCount === 0 && $markupCount === 0 && $clientExecutiveCount) {
            return '¿Estás seguro de eliminar?';
        }

        $associations = collect([
            $userCount > 0 ? "{$userCount} usuario(s)" : null,
            $clientCount > 0 ? "{$clientCount} cliente(s)" : null,
            $clientExecutiveCount > 0 ? "{$clientExecutiveCount} ejecutivo(s)": null,
            $markupCount > 0 ? "{$markupCount} markup(s)" : null
        ])->filter()->implode(', ');

        return "Esta región tiene {$associations} asociados. \n"
            . "Si procedes con la eliminación, todas estas relaciones serán desvinculadas. \n"
            . "¿Deseas continuar?";
    }

    public function addCountry(Request $request, BusinessRegion $businessRegion)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        if ($businessRegion->countries()->where('country_id', $request->country_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este país ya está asociado con esta región.',
                'code' => 409
            ], 409);
        }

        if (BusinessRegionsCountry::where('country_id', $request->country_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este país esta asociado a otra región.',
                'code' => 409
            ], 409);
        }

        $businessRegion->countries()->attach($request->country_id, [
            'created_at' => now()
        ]);

        return Response::json([
            'success' => true,
            'data' => [],
            'message' => 'País correctamente asociado',
            'code' => 200
        ], 200);
    }

    /**
     *
     * @param BusinessRegion $businessRegion
     * @param int $countryId
     * @return JsonResponse
     */
    public function removeCountry(BusinessRegion $businessRegion, $countryId)
    {
        // $now = now()->format('Y');

        // ELIMINAR SERVICIOS BLOQUEADOS
        ServiceClient::where('business_region_id', $businessRegion->id)
            // ->where('period', $now)
            ->whereHas('service.serviceOrigin', function($query) use($countryId) {
                $query->where('country_id', $countryId);
            })
            ->delete();
        // ELIMINAR HOTELES BLOQUEADOS
        HotelClient::where('business_region_id', $businessRegion->id)
            // ->where('period',$now)
            ->whereHas('hotel', function ($query) use($countryId) {
                $query->where('country_id', $countryId);
            })->delete();

        $businessRegion->countries()->detach($countryId);

        return Response::json([
            'success' => true,
            'data' => [],
            'code' => 200
        ], 200);
    }

    public function validateDestroyCountry(BusinessRegion $businessRegion, $countryId){
        // $now = now()->format('Y');

        // return response()->json(["aqui"]);

        $hotelCount = ServiceClient::where('business_region_id', $businessRegion->id)
            // ->where('period',$now) -- se borra de todos los periodos
            ->whereHas('service.serviceOrigin', function($query) use($countryId) {
                $query->where('country_id', $countryId);
            })->count();

        $serviceCount = HotelClient::where('business_region_id', $businessRegion->id)
            // ->where('period',$now) -- se borra de todos los periodos
            ->whereHas('hotel', function ($query) use($countryId) {
                $query->where('country_id', $countryId);
            })->count();

        // return response()->json($serviceCount);

        $associations = collect([
            $hotelCount > 0 ? "{$hotelCount} hotele(s)": null,
            $serviceCount > 0 ? "{$serviceCount} servicio(s)" : null
        ])->filter()->implode(', ');

        $canDelete = ($hotelCount === 0 && $serviceCount === 0);
        $message = $canDelete ? "¿Estás seguro de eliminar?" : "Este pais tiene {$associations} asociados. \n"
            . "Si procedes con la eliminación, todas estas relaciones serán desvinculadas. \n"
            . "¿Deseas continuar?";

        return response()->json([
            'success' => $canDelete,
            'message' => $message,
            'code' => 200
        ]);
    }
}
