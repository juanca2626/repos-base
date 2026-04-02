<?php


namespace App\Http\Services\Controllers;


use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\PackageBestSellerRequest;
use App\Http\Requests\PackageItineraryRequest;
use App\Http\Resources\Package\PackageLightResource;
use App\Http\Resources\Package\PackageResource;
use App\Http\Services\Traits\ClientTrait;
use App\Http\Traits\Services;
use App\Jobs\SendReservationPackageEmail;
use App\Language;
use App\PackageCancellationPolicy;
use App\PackagePlanRate;
use App\PackageRateSaleMarkup;
use App\Reservation;
use App\Http\Traits\Images;
use App\Http\Traits\Package;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ClientPackagesController extends Controller
{
    use ClientTrait, Images, Package, Services;

    public $expiration_search = 1440; // 24 horas

    public function parameter_search(Request $request)
    {
        $client_id = $this->getClientId($request);
        $lang = $request->get('lang');
        try {
            $language = Language::where('iso', $lang)->first(['id']);
            $market = Client::find($client_id);
            // obtengo los destinos de paquetes
            $packages_sale_rate = PackageRateSaleMarkup::select([
                'id',
                'seller_type',
                'seller_id',
                'package_plan_rate_id'
            ])
                ->where(function ($query) use ($client_id, $market) {
                    $query->orWhere('seller_id', $client_id);
                    $query->orWhere('seller_id', $market->market_id);
                })->where('status', 1)->get();

            $package_plan_rate = PackagePlanRate::whereIn('id', $packages_sale_rate->pluck('package_plan_rate_id'))
                ->whereHas('package', function ($query) {
                    $query->select(['id', 'extension']);
                    // INFO: Line commented to get interest from all type of packages
                    // $query->where('extension', 0);
                    $query->where('status', 1);
                })->where('status', 1)->distinct()->get(['package_id']);
            $package_client = $package_plan_rate->pluck('package_id')->unique();
            $package_destinations = $this->getDestinationPackageClientState($package_client, $language->id);
            $package_days = $this->getDaysPackage($package_client);
            $package_interests = $this->getInterestsPackage($package_client, $language->id)->sortBy('label')->values();
            $package_groups = $package_interests->map(function ($interest) {
                return [
                    'code' => $interest['group_code'],
                    'label' => $interest['group_label']
                ];
            })->unique('code')->sortBy('label')->values();

            $data = [
                'days' => $package_days,
                'interests' => $package_interests,
                'destinations' => $package_destinations,
                'groups' => $package_groups
            ];

            return Response::json(['success' => true, 'data' => $data]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }

    public function getPackageClient(Request $request)
    {
        return $this->handlePackageClientSearch($request);
    }

    public function getCachePackageSelect(Request $request)
    {
        $package_token = $request->input('package_token');
        //Todo Busco el paquete seleccionado y almacenado en cache
        if (Cache::has($package_token)) {
            $packages_selected = Cache::get($package_token);
            if (count($packages_selected) === 0) {
                return Response::json([
                    'success' => false,
                    'message' => 'Su tiempo de reserva termino, por favor vuelva a buscar su paquete',
                ], 404);
            }
        } else {
            return Response::json([
                'success' => false,
                'message' => 'Su tiempo de reserva termino, por favor vuelva a buscar su paquete'
            ]);
        }

        return Response::json(['success' => true, 'message' => 'ok', 'packages_selected' => $packages_selected]);
    }

    public function getPackageBestSellers(PackageBestSellerRequest $request)
    {
        $client_id = $this->getClientId($request);
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $type_service = $request->input('type_service');
        $date_from = $request->input('date');
        $from = Carbon::parse($date_from);
        $from = $from->format('Y-m-d');
        $quantity_persons = $request->input('quantity_persons');
        $adult = (int)$quantity_persons['adults'];
        $child_with_bed = (int)$quantity_persons['child_with_bed'];
        $child_without_bed = (int)$quantity_persons['child_without_bed'];
        $child = $child_with_bed + $child_without_bed;
        $age_child = $quantity_persons['age_child'];

        $type_class = "all";
        $type_package = [0];
        $sales_from = '';
        $package_ids = [];

        // Más vendidos por cliente
        $reservations_by_client_id = DB::select('
            select r.object_id,
                (select count(*) from reservations as rr where rr.entity = "Package"
                    and rr.client_id = ' . $client_id . ' and rr.object_id=r.object_id)
                as total_reserved
            from reservations as r
            where r.entity = "Package" and r.client_id = ' . $client_id . '
            group by r.object_id
            order by total_reserved desc;
        ');
        if (count($reservations_by_client_id) > 0) {
            $sales_from = 'client';
            foreach ($reservations_by_client_id as $reservation) {
                array_push($package_ids, $reservation->object_id);
            }
        } else { // probar con los del mismo mercado que el cliente
            $sales_from = 'market';
            $market_id = Client::find($client_id)->market_id;
            $client_ids_ = Client::where('market_id', $market_id)->pluck('id')->toArray();
            $client_ids = implode(', ', $client_ids_);
            $reservations_by_market = DB::select('
                select r.object_id,
                    (select count(*) from reservations as rr where rr.entity = "Package"
                        and rr.client_id in (' . $client_ids . ') and rr.object_id=r.object_id)
                    as total_reserved
                from reservations as r
                where r.entity = "Package" and r.client_id in (' . $client_ids . ')
                group by r.object_id
                order by total_reserved desc;
            ');

            if (count($reservations_by_market) > 0) {
                foreach ($reservations_by_market as $reservation) {
                    array_push($package_ids, $reservation->object_id);
                }
            }
        }
        $packages = collect();

        if (count($package_ids) > 0) {
            $client = Client::find($client_id, ['id', 'code', 'market_id', 'country_id', 'language_id']);
            $packages = $this->getPackagesClient(
                $client,
                [],
                $type_package,
                $language,
                $type_service,
                $from,
                $type_class,
                $adult,
                $child,
                $child_with_bed,
                [],
                '',
                0,
                false,
                false,
                $package_ids,
                3
            );
            $packages = $this->transformDataPackage(
                $packages,
                $date_from,
                $adult,
                $child_with_bed,
                $child_without_bed,
                $type_class,
                $lang,
                null,
                $type_service,
                '',
                0,
                1,
                0
            );

            $packages = $this->transformPackageAvailable($packages);

            $packages = PackageResource::collection($packages);
        }

        return Response::json([
            'success' => true,
            'data' => $packages,
            'sales_from' => $sales_from,
            'count' => $packages->count()
        ]);
    }

    public function storeTokenSearchPackages($token_search, $packages, $minutes)
    {
        Cache::put($token_search, $packages, now()->addMinutes($minutes));
    }

    public function getReservationPackage($file_code, Request $request)
    {

        $lang_iso = $request->get('lang');
        $language = Language::where('iso', $lang_iso)->first();
        $reservation = Reservation::where('file_code', $file_code)
            ->with([
                'client',
                'reservationsPackage' => function ($query) use ($language) {
                    $query->with([
                        'package' => function ($query) use ($language) {
                            $query->select(['id', 'nights', 'map_link']);
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->select(['id', 'package_id', 'tradename']);
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                            $query->with([
                                'galleries' => function ($query) {
                                    $query->select('object_id', 'slug', 'url');
                                    $query->where('type', 'package');
                                },
                            ]);
                        }
                    ]);
                    $query->with([
                        'serviceType' => function ($query) use ($language) {
                            $query->select(['id']);
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->select(['id', 'object_id', 'value']);
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'typeClass' => function ($query) use ($language) {
                            $query->select(['id']);
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->select(['id', 'object_id', 'value']);
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                        }
                    ]);
                },
            ])
            ->with([
                'reservationsService' => function ($query) use ($language) {
                    $query->select(['id', 'service_id', 'service_name', 'reservation_id', 'total_amount']);
                    $query->where('compensation', 1);
                    $query->with([
                        'service' => function ($query) use ($language) {
                            $query->select(['id']);
                            $query->with([
                                'service_translations' => function ($query) use ($language) {
                                    $query->select(['name_commercial', 'service_id']);
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                            $query->with([
                                'galleries' => function ($query) {
                                    $query->select('object_id', 'slug', 'url');
                                    $query->where('type', 'service');
                                },
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'reservationsPassenger' => function ($query) {
                    $query->first();
                }
            ])
            ->where('entity', 'Package')
            ->first();

        if ($reservation) {
            $on_request = 'OK';
            foreach ($reservation->reservationsHotel as $reservationsHotel) {
                foreach ($reservationsHotel->reservationsHotelRooms as $reservationHotelRoom) {
                    if ($reservationHotelRoom->channel_id == 1 and $reservationHotelRoom->onRequest == 0) {
                        $on_request = 'RQ';
                        break;
                    }
                }
            }
            return Response::json([
                'success' => true,
                'on_request' => $on_request,
                'data' => $reservation,
                'file_code' => $file_code
            ]);
        } else {
            return Response::json([
                'success' => false,
                'data' => [],
                'file_code' => $file_code
            ]);
        }
    }

    public function sendReservation(Request $request)
    {
        $file_code = $request->get('file_code');
        $email = $request->get('email');

        $reservation = Reservation::getReservations([
            'file_code' => $file_code,
        ], true);

        if ($reservation) {
            SendReservationPackageEmail::dispatchNow($reservation, $email);
            return Response::json([
                'success' => true
            ]);
        } else {
            return Response::json([
                'success' => false
            ]);
        }
    }

    public function createItinerary(PackageItineraryRequest $request)
    {
        $year = (!empty($request->input('year'))) ? $request->input('year') : date("Y");
        $packageCache = Cache::get($request->input('token_search'));
        $packageCache = $packageCache->firstWhere('id', $request->input('package_id'));

        if (!$packageCache) {
            return response()->json([
                'success' => false,
                'message' => 'El paquete no se encontró en el cache o expiró la búsqueda.',
            ], 404);
        }
        $packageCache = collect([$packageCache]);
        $categoryId = $request->has('category_id') ? $request->input('category_id') : $packageCache->first()['price_per_adult']['category_id'];
        $packages = PackageResource::collection($packageCache)->resolve();
        $packagesArray = json_decode(json_encode($packages), true);

        foreach ($packagesArray as $key => $package) {
            $url = config('services.cloudinary.domain') . '/packages/' . $package['id'] . '/frontpage.jpg';
            $headers = @get_headers($url);

            if ($headers && strpos($headers[0], '200')) {
                $packagesArray[$key]['portada_link'] = $url;
            }

            $url = config('services.cloudinary.domain') . '/packages/' . $package['id'] . '/map.jpg';
            $headers = @get_headers($url);

            if ($headers && strpos($headers[0], '200')) {
                $packagesArray[$key]['map_itinerary_link'] = $url;
            }
        }

        $params = [
            'client_id' => $request->input('client_id'),
            'lang' => strtolower($request->input('lang')),
            'package_ids' => [$request->input('package_id')],
            'year' => $year,
            'category' => $categoryId,
            'package' => $packagesArray[0],
            'use_header' => $request->input('use_cover'),
            'use_prices' => $request->input('use_prices'),
            'user_id' => 0,
            'portada' => $request->input('url_cover'),
        ];

        $url = $this->generateWordItinerary($params);
        return response()->download($url)->deleteFileAfterSend(true);
    }

    /**
     * Normaliza los parámetros del request para getPackageClient.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $clientId
     * @return array
     */
    protected function buildPackageSearchParams(Request $request, $clientId)
    {
        // Idioma
        $lang = $request->get('lang', 'en');
        $language = Language::where('iso', $lang)->first();

        // Destinos, grupos, tags
        $destinations = (array)$request->get('destinations', []);
        $groups = (array)$request->get('groups', []);
        $tags = (array)$request->get('tags', []);

        // Tipo de servicio y tipo de paquete
        $typeService = $request->get('type_service', 'all');
        $typePackage = (array)$request->get('type_package', [0, 1, 2]);

        // Fecha, días
        $date_from = $request->get('date'); // 2025-11-20
        $days = (int)$request->get('days', 0);
        $from = Carbon::parse($date_from);
        $from = $from->format('Y-m-d');
        // Personas
        $quantityPersons = $request->get('quantity_persons', []);
        $adults = (int)data_get($quantityPersons, 'adults', 0);
        $childWithBed = (int)data_get($quantityPersons, 'child_with_bed', 0);
        $childWithoutBed = (int)data_get($quantityPersons, 'child_without_bed', 0);
        $ageChild = (array)data_get($quantityPersons, 'age_child', []);
        $child = $childWithBed + $childWithoutBed;
        // Habitaciones
        $rooms = $request->get('rooms', []);
        $quantitySgl = (int)data_get($rooms, 'quantity_sgl', 0);
        $quantityDbl = (int)data_get($rooms, 'quantity_dbl', 0);
        $quantityTpl = (int)data_get($rooms, 'quantity_tpl', 0);
        $quantityChildDbl = (int)data_get($rooms, 'quantity_child_dbl', 0);
        $quantityChildTpl = (int)data_get($rooms, 'quantity_child_tpl', 0);

        // Otros filtros
        $filter = $request->get('filter', '');
        $typeClass = $request->get('category', 'all');
        $limit = (int)$request->get('limit', 0);
        $packageIds = (array)$request->get('package_ids', []);
        $onlyRecommended = (bool)$request->get('only_recommended', false);
        $gtm = (bool)$request->get('gtm', false);

        return [
            'client_id' => $clientId,
            'lang' => $lang,
            'language' => $language,

            'destinations' => $destinations,
            'groups' => $groups,
            'tags' => $tags,

            'type_service' => $typeService,
            'type_package' => $typePackage,
            'type_class' => $typeClass,

            'date_from' => $date_from,
            'from' => $from,
            'days' => $days,

            'adults' => $adults,
            'child' => $child,
            'child_with_bed' => $childWithBed,
            'child_without_bed' => $childWithoutBed,
            'age_child' => $ageChild,

            'quantity_sgl' => $quantitySgl,
            'quantity_dbl' => $quantityDbl,
            'quantity_tpl' => $quantityTpl,
            'quantity_child_dbl' => $quantityChildDbl,
            'quantity_child_tpl' => $quantityChildTpl,

            'filter' => $filter,
            'limit' => $limit,
            'package_ids' => $packageIds,
            'only_recommended' => $onlyRecommended,
            'gtm' => $gtm,
        ];
    }

    /**
     * Agrega links de portada y mapa de Cloudinary a la colección de paquetes.
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Http\Resources\Json\ResourceCollection $packages
     * @return mixed
     */
    protected function addCloudinaryImagesToPackages($packages)
    {
        foreach ($packages as $key => $package) {
            $base = config('services.cloudinary.domain') . '/packages/' . $package['id'];
            $packages[$key]['portada_link'] = '';
            $packages[$key]['map_itinerary_link'] = '';

            $frontUrl = $base . '/frontpage.jpg';
            $mapUrl = $base . '/map.jpg';

            if (count($packages) === 1) {
                $headers = @get_headers($frontUrl);

                if ($headers && strpos($headers[0], '200') !== false) {
                    $packages[$key]['portada_link'] = $frontUrl;
                }

                $headers = @get_headers($mapUrl);

                if ($headers && strpos($headers[0], '200') !== false) {
                    $packages[$key]['map_itinerary_link'] = $mapUrl;
                }
            } else {
                $packages[$key]['portada_link'] = $frontUrl;
                $packages[$key]['map_itinerary_link'] = $mapUrl;
            }
        }

        return $packages;
    }

    /**
     * Versión GET del endpoint de paquetes.
     *
     * Reutiliza exactamente la misma lógica de getPackageClient,
     * pero recibe los parámetros por query string.
     *
     * GET /api/client/packages?lang=en&date=2025-11-20&...
     */
    public function getPackageClientGet(Request $request)
    {
        // Fecha de hoy por defecto
        $today = Carbon::today()->addDays()->format('Y-m-d');

        // 1. Normalizamos los campos simples
        if (!$request->filled('date')) {
            $request->merge([
                'date' => $today,
            ]);
        }

        if (!$request->filled('lang')) {
            $request->merge([
                'lang' => 'es',
            ]);
        }

        if (!$request->filled('type_service')) {
            $request->merge([
                'type_service' => 'all',
            ]);
        }

        if (!$request->filled('days')) {
            $request->merge([
                'days' => 0,
            ]);
        }

        if (!$request->has('filter')) {
            // filter vacío por defecto
            $request->merge([
                'filter' => '',
            ]);
        }


        // 2. quantity_persons con sus defaults
        $quantityPersons = $request->get('quantity_persons', []);

        if (!array_key_exists('adults', $quantityPersons) || $quantityPersons['adults'] === null || $quantityPersons['adults'] === '') {
            $quantityPersons['adults'] = 2; // default 2 adultos
        }

        if (!array_key_exists('child_with_bed', $quantityPersons)) {
            $quantityPersons['child_with_bed'] = 0;
        }

        if (!array_key_exists('child_without_bed', $quantityPersons)) {
            $quantityPersons['child_without_bed'] = 0;
        }

        // Si no envían age_child, por defecto arreglo vacío
        if (!array_key_exists('age_child', $quantityPersons) || !is_array($quantityPersons['age_child'])) {
            $quantityPersons['age_child'] = [];
        }

        $request->merge([
            'quantity_persons' => $quantityPersons,
        ]);

        // 3. rooms con sus defaults
        $rooms = $request->get('rooms', []);

        if (!array_key_exists('quantity_sgl', $rooms) || $rooms['quantity_sgl'] === null || $rooms['quantity_sgl'] === '') {
            $rooms['quantity_sgl'] = 0;
        }

        if (!array_key_exists('quantity_dbl', $rooms) || $rooms['quantity_dbl'] === null || $rooms['quantity_dbl'] === '') {
            // tomo como default 1 doble (como en tu ejemplo de URL)
            $rooms['quantity_dbl'] = 1;
        }

        if (!array_key_exists('quantity_tpl', $rooms) || $rooms['quantity_tpl'] === null || $rooms['quantity_tpl'] === '') {
            $rooms['quantity_tpl'] = 0;
        }

        $request->merge([
            'rooms' => $rooms,
        ]);

        // 4. type_package por defecto [0,1,2] si no viene
        if (!$request->has('type_package')) {
            $request->merge([
                'type_package' => [0, 1, 2],
            ]);
        }

        if (!$request->has('destinations')) {
            $request->merge([
                'destinations' => [],
            ]);
        }

        if (!$request->has('groups')) {
            $request->merge([
                'groups' => [],
            ]);
        }

        if (!$request->has('tags')) {
            $request->merge([
                'tags' => [],
            ]);
        }

        if (!$request->has('client_id')) {

            $clientID = $this->getClientId($request);

            $request->merge([
                'client_id' => $clientID ?? 16861 // 4PROD para test,
            ]);
        }

        // 5. package_ids por defecto arreglo vacío si no viene
        if (!$request->has('package_ids')) {
            $request->merge([
                'package_ids' => [],
            ]);
        }

        // 6. Finalmente llamas a tu método principal usando el mismo Request
        return $this->handlePackageClientSearch($request, false);
    }

    private function handlePackageClientSearch(Request $request, $versionFull = true)
    {
        try {
            $client_id = $this->getClientId($request);
            $userId = Auth::check() ? Auth::id() : null;

            $params = $this->buildPackageSearchParams($request, $client_id);

            $cacheKey = $this->buildPackageSearchCacheKey($params, $client_id, $versionFull, $userId);

            $ttl = now()->addMinutes(5);

            $client = Client::find($client_id, ['id', 'code', 'market_id', 'country_id', 'language_id']);

            //Todo Busqueda de politicas de cancelacion por la cantidad de adultos
            $policy = PackageCancellationPolicy::where('pax_from', '<=', $params['adults'])
                ->where('pax_to', '>=', $params['adults'])
                ->get([
                    'day_from',
                    'day_to',
                    'cancellation_fees'
                ]);

            $data = Cache::remember($cacheKey, $ttl, function () use ($params, $client_id, $client, $policy, $versionFull) {

                $faker = Faker::create();
                $token_search = $faker->unique()->uuid;

                // 3) Reasignar variables locales (para que tu código actual siga funcionando igual)
                $destination_code = $params['destinations'];
                $lang = $params['lang'];
                $language = $params['language'];

                $only_recommended = $params['only_recommended'];
                $gtm = $params['gtm'];

                $type_service = $params['type_service'];
                $type_class = $params['type_class'];
                $type_package = $params['type_package'];

                $date_from = $params['date_from'];
                $from = $params['from'];
                $days = $params['days'];

                $adult = $params['adults'];
                $child_with_bed = $params['child_with_bed'];
                $child_without_bed = $params['child_without_bed'];

                $room_quantity_sgl = $params['quantity_sgl'];
                $room_quantity_dbl = $params['quantity_dbl'];
                $room_quantity_tpl = $params['quantity_tpl'];
                $room_quantity_child_dbl = $params['quantity_child_dbl'];
                $room_quantity_child_tpl = $params['quantity_child_tpl'];

                $filter = $params['filter'];
                $limit = $params['limit'];
                $package_ids = $params['package_ids'];
                $tags = $params['tags'];
                $child = $params['child'];
                $groups = $params['groups'];

                $packages = $this->getPackagesClient(
                    $client,
                    $destination_code,
                    $type_package,
                    $language,
                    $type_service,
                    $from,
                    $type_class,
                    $adult,
                    $child,
                    $child_with_bed,
                    $tags,
                    $filter,
                    $days,
                    false,
                    $only_recommended,
                    $package_ids,
                    $limit,
                    $groups,
                    $gtm
                );

                $packages = $this->transformDataPackage(
                    $packages,
                    $date_from,
                    $adult,
                    $child_with_bed,
                    $child_without_bed,
                    $type_class,
                    $lang,
                    $policy,
                    $type_service,
                    $token_search,
                    $room_quantity_sgl,
                    $room_quantity_dbl,
                    $room_quantity_tpl,
                    $room_quantity_child_dbl,
                    $room_quantity_child_tpl,
                    $filter,
                    $gtm
                );

                $packages = $packages->where('total_amount', '>', 0)->where('available', true);
                $packages = $this->transformPackageAvailable($packages);

                $this->storeTokenSearchPackages($token_search, $packages, $this->expiration_search);
                // Lo devolvemos en un array por si luego quieres agregar más cosas
                return [
                    'packages' => $packages,
                ];
            });

            $packages = $data['packages'];

            if ($versionFull) {
                $packages = PackageResource::collection($packages);
            } else {
                $packages = PackageLightResource::collection($packages);
            }

            $packages = $this->addCloudinaryImagesToPackages($packages);

            //add commission
            $client = Client::select('commission', 'commission_status')->where('id', $client_id)->first();
            $commission = 0;
            $commission_status = 0;
            if ($client && $client->commission > 0) {
                $commission = (float) $client->commission;
                $commission_status = $client->commission_status;
            }

            return Response::json([
                'success' => true,
                'count' => $packages->count(),
                'data' => $packages,
                'cloudinary_link' => config('services.cloudinary.domain'),
                'commission' => $commission, 'commission_status' => $commission_status
            ]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }

//    public function getPackageCache(Request $request)
//    {
//        return Response::json(Cache::get($request->get('token_search')));
//    }
}
