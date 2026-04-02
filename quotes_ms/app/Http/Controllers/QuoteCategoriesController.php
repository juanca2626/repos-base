<?php

namespace App\Http\Controllers;

use File;
use DateTime;
use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Language;
use App\Models\QuoteLog;
use App\Models\TypeClass;
use App\Http\Traits\Images;
use Illuminate\Support\Str;
use App\Models\ClientSeller;
use App\Models\QuoteService;
use Illuminate\Http\Request;
use App\Models\QuoteCategory;
use PhpOffice\PhpWord\PhpWord;
use App\Models\QuoteServiceRate;
use App\Models\QuoteServiceRoom;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\QuoteServices;
use App\Models\QuoteCategoryRates;
use App\Models\QuoteServiceAmount;
use App\Models\ServiceTranslation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Shared\Html;
use App\Http\Traits\QuoteHistories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\QuotesExportPassenger;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Table as TableStyle;
use App\Http\Aurora\AuroraExternalApiService;
use App\Models\QuoteServiceRoomHyperguest;
use Illuminate\Validation\Rule;

class QuoteCategoriesController extends Controller
{
    use QuoteServices;
    use QuoteHistories;
    use Images;
    use QuotesExportPassenger;


    protected AuroraExternalApiService $auroraService;
    protected $authorization;

    public function __construct(Request $request, AuroraExternalApiService $auroraService)
    {
        $this->auroraService = $auroraService;
        $this->auroraService->setAuthorization($request->header('authorization'));
    }

    public function storeService($quote_id, Request $request)
    {
        // return response()->json("error", 500);
        $validator = Validator::make($request->all(), [
            'categories'       => 'required',
            'type'             => 'required',
            'object_id'        => 'required',
            'date_in'          => 'required',
            'date_out'         => 'required',
            'service_rate_ids' => [
                'array',
                Rule::requiredIf(fn() => empty(request('service_rooms_rate_plans_ids')))
            ],

            'service_rooms_rate_plans_ids' => [
                'array',
                Rule::requiredIf(fn() => empty(request('service_rate_ids')))
            ],
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
            return response()->json($response, 200);
        } else {
            $categories = $request->input('categories');
            $type = $request->input('type');
            $object_id = $request->input('object_id');
            $service_code = $request->input('service_code');
            $date_in = $request->input('date_in');
            $date_out = $request->input('date_out');
            $service_rate_ids = $request->input('service_rate_ids');
            $service_rooms_rate_plans_ids = $request->input('service_rooms_rate_plans_ids');
            $adults = ($request->has('adult')) ? $request->input('adult') : 0;
            $child = ($request->has('child')) ? $request->input('child') : 0;
            $single = ($request->has('single') and $request->input('single') != null) ? $request->input('single') : 0;
            $double = ($request->has('double') and $request->input('double') != null) ? $request->input('double') : 0;
            $triple = ($request->has('triple') and $request->input('triple') != null) ? $request->input('triple') : 0;
            $on_request = $request->input('on_request');
            $extension_parent_id = $request->input('extension_parent_id');
            $new_extension_id = $request->input('new_extension_id');
            $file_itinerary_id = $request->input('file_itinerary_id');

            $client_id = $this->getClientId($request);
            $histories = [];
            $newServiceCreated = [];
            foreach ($categories as $c) {
                $newServiceCreated = $this->newService(
                    $c,
                    $type,
                    $object_id,
                    $date_in,
                    $date_out,
                    $adults,
                    $child,
                    0,
                    $single,
                    $double,
                    $triple,
                    0,
                    $service_rate_ids,
                    $client_id,
                    $on_request,
                    $quote_id,
                    $new_extension_id,
                    $file_itinerary_id,
                    $service_rooms_rate_plans_ids
                );

                if ($extension_parent_id != '') {
                    $_service_parent = QuoteService::where('quote_category_id', $c)
                        ->where('id', $extension_parent_id);
                    if ($_service_parent->count() > 0) {
                        $_service_parent = $_service_parent->first();
                        $last_service =
                            QuoteService::where('quote_category_id', $c)->orderBy('created_at', 'desc')->first();
                        // date_in : 2020-08-05
                        if (strtotime($date_in) < strtotime(convertDate($_service_parent->date_in, '/', '-', 1))) {
                            $last_service->extension_id = $_service_parent->extension_id;
                            $last_service->save();

                            $_service_parent->extension_id = null;
                            $_service_parent->parent_service_id = $last_service->id;
                            $_service_parent->save();
                            DB::table('quote_services')->where('parent_service_id', $_service_parent->id)
                                ->update([
                                    "parent_service_id" => $last_service->id
                                ]);
                        } else {
                            $last_service->parent_service_id = $_service_parent->id;
                            $last_service->save();
                        }
                    }
                }

                $type_class_id_ = QuoteCategory::find($c)->type_class_id;
                array_push($histories, [
                    "type"          => "store",
                    "slug"          => "store_service",
                    "previous_data" => "",
                    "current_data"  => json_encode([
                        "type_class_id" => $type_class_id_,
                        "type_service"  => $type,
                        "object_id"     => $object_id,
                        "service_code"  => $service_code,
                        "date_in"       => convertDate($date_in, "-", "/", 1)
                    ]),
                    "description" => "Agregó servicio"
                ]);
            }

            if (count($histories) > 0) {
                $this->store_history_logs($quote_id, $histories);
            }

            $this->updateAmountAllServices($quote_id, $client_id);
        }

        if (Cache::has('quote_markup_errors')) {
            Cache::forget('quote_markup_errors');

            return Response::json(["success" => false, "type" => "client_markup"]);
        } else {
            return Response::json(["success" => true, "new_service_created" => $newServiceCreated]);
        }
    }

    public function storeFlight($quote_id, Request $request)
    {
        $rules = [
            'type'        => 'required',
            'categories'  => 'required',
            'type_flight' => 'required',
            'date_in'     => 'date',
            'origin'      => ($request->__get('destination') == '') ? 'required' : '',
            'destination' => ($request->__get('origin') == '') ? 'required' : '',
        ];

        if ($request->__get('destination') == '') {
            $rules['origin'] = 'required';
        }

        if ($request->__get('origin') == '') {
            $rules['destination'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);
        // cities / nights
        if ($validator->fails()) {
            $response = ['success' => false, 'rules' => $rules, 'messages' => $validator->errors()];

            return response()->json($response, 200);
        } else {
            $type = $request->input('type');
            $categories = $request->input('categories');
            $type_flight = $request->input('type_flight');
            $date_flight = $request->input('date_in');
            $origin = $request->input('origin');
            $destiny = $request->input('destination');
            $adults = $request->input('adult');
            $child = $request->input('child');
            $code_flights = [
                'AECFLT',
                'AEIFLT'
            ]; // Códigos para identificar si son vuelos internacionales o nacionales..

            if (Auth::user()->user_type_id == 4) {
                $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first()->client_id;
            }
            if (Auth::user()->user_type_id == 3) {
                $client_id = $request->post('client_id');
            }

            $histories = [];

            foreach ($categories as $c) {
                $this->newFlight(
                    $c,
                    $type,
                    $date_flight,
                    $client_id,
                    $adults,
                    $child,
                    $origin,
                    $destiny,
                    $code_flights[$type_flight]
                );
                $type_class_id_ = QuoteCategory::find($c)->type_class_id;
                array_push($histories, [
                    "type"          => "store",
                    "slug"          => "store_flight",
                    "previous_data" => "",
                    "current_data"  => json_encode([
                        "type_class_id" => $type_class_id_,
                        "type_service"  => "flight",
                        "date_in"       => convertDate($date_flight, "-", "/", 1),
                        "origin"        => $origin,
                        "destiny"       => $destiny,
                    ]),
                    "description" => "Agregó vuelo"
                ]);
            }

            if (count($histories) > 0) {
                $this->store_history_logs($quote_id, $histories);
            }
        }

        return response()->json(['success' => true, "message" => "servicio agregado exitosamente"], 200);
    }

    public function updateOnRequest(Request $request)
    {
        $quote_service_id = $request->post('quote_service_id');
        $on_request = $request->post('on_request');

        DB::transaction(function () use ($quote_service_id, $on_request) {

            DB::table('quote_services')->where('id', $quote_service_id)->update([
                'on_request' => $on_request
            ]);
        });

        return response()->json("On request Actualizado");
    }

    public function updateOnRequestMultiple(Request $request)
    {
        $services_update = $request->post('services_update');
        DB::transaction(function () use ($services_update) {

            foreach ($services_update as $service) {

                DB::table('quote_services')->where('id', $service['quote_service_id'])->update([
                    'on_request' => $service['on_request']
                ]);
            }
        });

        return response()->json("On request Actualizado");
    }

    public function replaceMultiple($quote_id, $category_id, Request $request)
    {

        $type_classes = $request->input('type_classes');
        $services_for_copy = DB::table('quote_services')->where('quote_category_id', $category_id)
            ->orderby('date_in', 'asc')
            ->orderby('order', 'asc')
            ->get();

        $client_id = '';
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first()->client_id;
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        DB::transaction(function () use ($quote_id, $type_classes, $category_id, $services_for_copy, $client_id) {
            $created_at = Carbon::now();

            foreach ($type_classes as $tc) {
                $quote_category_id = DB::table('quote_categories')->insertGetId([
                    'quote_id'      => $quote_id,
                    'type_class_id' => $tc,
                    'created_at'    => $created_at,
                    'updated_at'    => $created_at
                ]);

                foreach ($services_for_copy as $s) {
                    $this->newService(
                        $quote_category_id,
                        $s->type,
                        $s->object_id,
                        $s->date_in,
                        $s->date_out,
                        $s->adult,
                        $s->child,
                        $s->infant,
                        $s->single,
                        $s->double,
                        $s->triple,
                        $s->triple_active,
                        [],
                        $client_id,
                        0,
                        $quote_id
                    );
                }
            }

            $this->destroy($category_id);
        });

        $response = ['success' => true];

        return Response::json($response);
    }

    public function add($quote_id, $category_id, Request $request)
    {

        $quote_in_edition = Quote::where('user_id', Auth::user()->id)->where('status', 2);

        if ($quote_in_edition->count() == 0) {
            return Response::json(['success' => false, 'type' => 'empty']);
        }

        $quote_in_edition = $quote_in_edition->first();
        $quote_in_edition_id = $quote_in_edition->id;
        $add_in_this_categories_ids = DB::table('quote_categories')
            ->where('quote_id', $quote_in_edition_id)
            ->pluck('id');

        $services_for_add = DB::table('quote_services')->where('quote_category_id', $category_id)
            ->orderby('date_in', 'asc')
            ->orderby('order', 'asc')
            ->get();

        $client_id = '';
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first()->client_id;
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        DB::transaction(function () use (
            $quote_id,
            $quote_in_edition_id,
            $add_in_this_categories_ids,
            $services_for_add,
            $client_id
        ) {

            foreach ($add_in_this_categories_ids as $in_category_id) {
                foreach ($services_for_add as $s) {
                    $service_rooms_rate_plans_ids = [];
                    if ($s->type == 'service') {
                        $service_rate_ids = QuoteServiceRate::where('quote_service_id', $s->id)->pluck('id');
                    } else { // hotel

                        if (!$s->hyperguest_pull) {
                            $service_rate_ids = QuoteServiceRoom::where('quote_service_id', $s->id)->pluck('id');
                        } else {
                            $quoteServiceRoomHyperguest = QuoteServiceRoomHyperguest::where('quote_service_id', $s->id)->get();
                            foreach ($quoteServiceRoomHyperguest as $hyper) {
                                array_push($service_rooms_rate_plans_ids, [
                                    'room' => $hyper->room,
                                    'rate' => $hyper->rate_plan_id,
                                ]);
                            }
                        }
                    }

                    $this->newService(
                        $in_category_id,
                        $s->type,
                        $s->object_id,
                        $s->date_in,
                        $s->date_out,
                        $s->adult,
                        $s->child,
                        $s->infant,
                        $s->single,
                        $s->double,
                        $s->triple,
                        $s->triple_active,
                        $service_rate_ids,
                        $client_id,
                        0,
                        $quote_in_edition_id,
                        null,
                        null,
                        $service_rooms_rate_plans_ids
                    );
                }
                $services = QuoteService::where('quote_category_id', $in_category_id)->orderBy('date_in')->get();
                $this->updateOrderAndDateServices($services);
            }

            $this->updateNightsAndCities($quote_in_edition_id);

            DB::table('quote_logs')->insert([
                "quote_id"  => $quote_in_edition_id,
                "type"      => 'quote_added',
                "object_id" => $quote_id
            ]);
        });

        $response = ['success' => true];

        return Response::json($response);
    }

    public function destroy($id)
    {

        DB::transaction(function () use ($id) {


            $services = DB::table('quote_services')->where('quote_category_id', $id);
            $_services = $services->get();

            foreach ($_services as $s) {
                DB::table('quote_service_rates')->where('quote_service_id', $s->id)->delete();
                DB::table('quote_service_rooms')->where('quote_service_id', $s->id)->delete();
            }
            $services->delete();
            DB::table('quote_categories')->where('id', $id)->delete();
        });

        $response = ['success' => true];

        return Response::json($response);
    }

    public function searchAllServices($quote_id, Request $request)
    {

        $lang = $request->input('lang');
        $language_id = Language::where('iso', $lang)->first()->id;
        $categories = QuoteCategory::where('quote_id', $quote_id)
            ->with([
                'services' => function ($query) {
                    $query->select([
                        'id',
                        'quote_category_id',
                        'type',
                        'object_id',
                        'order',
                        'date_in',
                        'date_out',
                        'nights',
                        'adult',
                        'child',
                        'infant',
                        'single',
                        'double',
                        'triple',
                        'triple_active',
                        'locked',
                        'created_at',
                        'updated_at',
                        'on_request',
                        'extension_id',
                        'parent_service_id',
                        'optional',
                        'code_flight',
                        'origin',
                        'destiny',
                        'date_flight',
                    ]);
                    $query->with('amount');
                    $query->with([
                        'service' => function ($query) {
                            $query->select(['id', 'aurora_code', 'name']);
                        }
                    ]);
                    $query->with([
                        'hotel' => function ($query) {
                            $query->select(['id', 'name']);
                            $query->with([
                                'channel' => function ($query) {
                                    $query->select(['id', 'code', 'hotel_id']);
                                    $query->where('state', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->orderBy('date_in');
                    $query->orderBy('order');
                }
            ])
            ->with([
                'type_class' => function ($query) use ($language_id) {
                    $query->select(['id', 'code', 'order', 'color']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select(['object_id', 'value']);
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->get();

        return Response::json($categories);
    }

    public function wordSkeleton($quote_id, $category_id, Request $request)
    {
        $lang = strtolower($request->input('lang'));
        $_lang = Language::where('iso', $lang)->first();
        $refPax = $request->input('refPax');
        $client_id = $request->input('client_id');
        $use_header = $request->input('use_header');

        $language_id = Language::select('id')->where('iso', $lang)->first()->id;

        $dataLang = File::get(database_path() . "/data/translations/Itinerary.json");
        $trad = json_decode($dataLang, true);
        $trad = $trad[$lang];

        $client = Client::find($client_id);
        $quote = Quote::find($quote_id);

        $user_type_id = null;
        $client_commission_status = 0;
        $client_commission = 0;
        $has_commission_label = false;

        if (Auth::check()) {
            $user_type_id = Auth::user()->user_type_id;

            if ($user_type_id == 4) {
                $client = Client::select('commission', 'commission_status')
                    ->where('id', $client_id)
                    ->first();

                if ($client) {
                    $client_commission_status = (int) $client->commission_status;
                    $client_commission = (float) $client->commission;

                    // aplica la lógica del flag
                    $has_commission_label = (
                        $client_commission_status === 1 &&
                        $client_commission > 0 &&
                        $user_type_id === 4
                    );
                }
            }
        }

        // \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(false);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $sectionStyle = array(
            'marginTop' => 2600,
        );

        if ($use_header == "true") {
            $section = $phpWord->addSection($sectionStyle);
            $header = $section->addHeader();
            //------------------ ENCABEZADO DE IMATEN
            // se cambia carpeta word por la de portadas
            $header->addImage(
                'https://res.cloudinary.com/litodti/image/upload/aurora/portadas/Encabezado.jpg',
                array(
                    'width'            => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(16),
                    'positioning'      => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                    'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                    'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                    'posVerticalRel'   => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                )
            );
            //------------------ FIN ENCABEZADO DE IMAGEN
            $phpWord->addFontStyle('space1', array('size' => 9));
        } else {
            $section = $phpWord->addSection();
        }

        //------------------ CAJAS DE TEXTO TITUTLO Y DATOS DE CLIENTE Y VENDEDOR
        //----------------- INICIO PRIMERA CAJA DE TEXTO DATOS CLIENTE
        $textbox = $section->addTextBox(
            array(
                'width'            => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(6.5),
                'height'           => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.82),
                'positioning'      => \PhpOffice\PhpWord\Style\TextBox::POSITION_ABSOLUTE,
                'borderSize'       => 1,
                'borderColor'      => '#FFFFFF',
                'bgColor'          => '#FFFFFF',
                'align'            => 'right',
                'posHorizontal'    => \PhpOffice\PhpWord\Style\TextBox::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\TextBox::POSITION_RELATIVE_TO_MARGIN,
                'wrappingStyle'    => 'infront',
            )
        );
        //TEXTO PARA CAJA
        $phpWord->addParagraphStyle('pStyleLeft', array('align' => 'right', 'spaceAfter' => 5));
        $textbox->addText(htmlspecialchars($trad['lblcustom'] . ' ' . $client->name), array('name' => 'Calibri', 'size' => 11, 'color' => '757474', 'bold' => true), 'pStyleLeft');
        //        if ($codigo != 0) {
        //            $textbox->addText(htmlspecialchars($trad->lblQuotation.' '.$codigo)
        //                , array('name' => 'Calibri', 'size' => 11, 'color' => '757474', 'bold' => true), 'pStyleLeft');
        //        }

        $textbox->addText(htmlspecialchars($trad['lblPassenger'] . ' ' . $refPax), array('name' => 'Calibri', 'size' => 11, 'color' => '757474', 'bold' => true), 'pStyleLeft');
        //        //----------------- FIN PRIMERA CAJA DE TEXTO DATOS CLIENTE
        $section->addTextBreak(3);
        //TEXTO PIE PARA PORTADA
        //TITULO
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'spaceAfter'    => 5,
                'positioning'   => \PhpOffice\PhpWord\Style\TextBox::POSITION_RELATIVE,
                'wrappingStyle' => 'infront',
            )
        );

        //------------------INICIO ITINERARIO
        //Encabezado de pagina
        $phpWord->addParagraphStyle(
            'encabezado',
            array(
                'align'         => 'right',
                'spaceAfter'    => 0,
                'wrappingStyle' => 'infront',
            )
        );
        //-----------------INICIO TITULO LIMATOURS
        //ESTILO DE LINEA
        $linestyle = array(
            'width'      => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(12),
            'height'     => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(0),
            'weight'     => 2,
            'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1),
            'color'      => '#aea792',
        );
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
                'spaceAfter'    => 0,
            )
        );

        //-----------------INICIO CONTENIDO TEXTO
        $phpWord->addParagraphStyle(
            'paragraft',
            array(
                'align'         => 'both',
                'spaceAfter'    => 0,
                'wrappingStyle' => 'infront',
            )
        );
        //-----------------FIN CONTENIDO TEXTO
        //-----------------FIN TITULO LIMATOURS

        //-----------------INICIO  TITULO DIA DIA
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        //-----------------INICIO TITULO DETALLE DE SERVICIOS COTIZADOS
        $section->addText(
            htmlspecialchars($trad['titleDetailService']),
            array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        //        $section->addTextBreak(1);
        //------------------FIN  TITULO DIA DIA

        //-----------------INICIO ITINERARIO
        $phpWord->addParagraphStyle(
            'titleday',
            array(
                'align'         => 'left',
                'spaceAfter'    => 10,
                'wrappingStyle' => 'infront',
            )
        );
        $phpWord->addParagraphStyle(
            'fecha',
            array(
                'align'      => 'left',
                'spaceAfter' => 170,
            )
        );

        ///------------ INICIO RECORRIDO DE SERVICIOS
        $plusday = 0;
        $arreglo = [];
        $group = [];
        $nn = 0;

        $services = QuoteService::where('quote_category_id', $category_id)
            ->with([
                'service.service_translations' => function ($query) use ($_lang) {
                    $query->where('language_id', $_lang->id);
                },
                'service.serviceOrigin.state.translations' => function ($query) use ($_lang) {
                    $query->where('language_id', $_lang->id);
                },
                'hotel.translations',
                'hotel.channel',
                'hotel.state.translations' => function ($query) use ($_lang) {
                    $query->where('language_id', $_lang->id);
                },
                'service_rooms.rate_plan_room.rate_plan',
                'service_rooms.rate_plan_room.room.room_type.translations' => function ($query) use ($_lang) {
                    $query->where('language_id', $_lang->id);
                }
            ])
            ->orderBy('date_in', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        $listServicesMain = $services;
        $listServices = [];
        $listHotels = [];
        $listHotelsOptional = [];
        $listFlights = [];
        $totalAdl = 0;
        $totalChd = 0;
        foreach ($listServicesMain as $s) {

            $s->FECHA = $this->convertDate($s->date_in, '/', '-', 1);
            $s->ORDEN = $s->date_in . $s->order;
            if ($s->adult > $totalAdl) {
                $totalAdl = $s->adult;
            }
            if ($s->child > $totalChd) {
                $totalChd = $s->child;
            }
            if ($s->type == 'service') {
                $s->key = $s->object_id . '_' . convertDate($s->date_in, '/', '-', 1);
                $s->aurora_code = $s->service->aurora_code;
                $s->equivalence_aurora = $s->service->equivalence_aurora;
                $s->include_accommodation = $s->service->include_accommodation;
                $s->description = $s->service->service_translations[0]->description;
                $s->summary = $s->service->service_translations[0]->summary; // TEXTSK
                $s->itinerary = $s->service->service_translations[0]->itinerary;
                $s->CIUIN = $s->service->serviceOrigin[0]->state->translations[0]->value;
                array_push($listServices, $s);
            }

            if ($s->type == 'flight') {
                $s->key = $s->code_flight . '_' . convertDate($s->date_in, '/', '-', 1);
                $s->CODIGO = $s->code_flight;
                $s->ORIGIN = $s->origin;
                $s->DESTINY = $s->destiny;

                if ($s->CODIGO == 'AEC' or $s->codigo == 'AEI') {
                    array_push($listFlights, $s);
                }
            }

            if ($s->type == 'hotel') {
                $rate_plan_room = ($s->service_rooms->count() > 0) ? $s->service_rooms[0]->rate_plan_room_id : '0';
                $s->key = $s->object_id . '_' . $s->nights . '_' . convertDate($s->date_in, '/', '-', 1);
                $s->CODIGO = $s->hotel->channel[0]->code;
                $s->CIUIN = $s->hotel->state->translations[0]->value;
                $total_accommodation = (int)$s->single + (int)$s->double + (int)$s->triple + (int)$s->double_child + (int)$s->triple_child;
                if ($s->optional == 1) {
                    if ($total_accommodation > 0) {
                        array_push($listHotelsOptional, $s);
                    }
                } else {
                    if ($total_accommodation > 0) {
                        array_push($listHotels, $s);
                    }
                }
            }
        }

        $listServicesMain = $this->orderMultiDimensionalArray($listServicesMain, 'FECHA');
        // AGRUPO POR FECHA
        $listServicesMain = $this->groupedArray($listServicesMain, "FECHA");

        // ORDENO POR FECHA CADA GRUPO
        for ($r = 0; $r < count($listServicesMain); $r++) {
            array_push($group, $this->orderMultiDimensionalArray($listServicesMain[$r], 'ORDEN', false));
        }

        //DESAGRUPO EL ARRAY
        $listServicesMain = $this->array_flatten($group);
        for ($r = 0; $r < count($listServicesMain); $r++) {
            $listServicesMain[$r]['STATUS'] = true;
        }

        $textSearch = "#";
        for ($i = 0; $i < count($listServicesMain); $i++) {
            if ($listServicesMain[$i]->type == "service") {
                $parrafo = strip_tags(
                    $this->htmlDecode(
                        htmlspecialchars_decode(
                            $listServicesMain[$i]->service->service_translations[0]->summary,
                            ENT_QUOTES
                        )
                    )
                );
                $count = substr_count($parrafo, $textSearch);
                if ($count > 0) {
                    $textExplode = explode($textSearch, $parrafo);
                    for ($j = 0; $j < $count; $j++) {
                        $fecha = strtotime($listServicesMain[$i]['FECHA'] . "+ {$j} days");
                        $fecha = date("Y-m-d", $fecha);
                        $_count = (isset($arreglo[$fecha])) ? count($arreglo[$fecha]) : 0;
                        $arreglo[$fecha][$_count] = $listServicesMain[$i];
                        $arreglo[$fecha][$_count - 1]['FECHA'] = $fecha;
                        $arreglo[$fecha][$_count - 1]->service->service_translations[0]->summary = substr(
                            $textExplode[$j + 1],
                            1
                        );
                        if ($j != 0) {
                            $arreglo[$fecha][$_count - 1]['IMG'] = '';
                        }
                    }
                } else {
                    $_count = (isset($arreglo[$listServicesMain[$i]['FECHA']])) ? count($arreglo[$listServicesMain[$i]['FECHA']]) : 0;
                    $arreglo[$listServicesMain[$i]['FECHA']][$_count] = $listServicesMain[$i];
                }
            } elseif ($listServicesMain[$i]->type == "hotel") {
                if ((int)$listServicesMain[$i]['nights'] > 1) {
                    for ($n = 0; $n < (int)$listServicesMain[$i]['nights']; $n++) {
                        $fecha = strtotime($listServicesMain[$i]['FECHA'] . "+ {$n} days");
                        $fecha = date("Y-m-d", $fecha);
                        $_count = (isset($arreglo[$fecha])) ? count($arreglo[$fecha]) : 0;
                        $arreglo[$fecha][$_count] = $listServicesMain[$i];
                    }
                } else {
                    $_count = (isset($arreglo[$listServicesMain[$i]['FECHA']])) ? count($arreglo[$listServicesMain[$i]['FECHA']]) : 0;
                    $arreglo[$listServicesMain[$i]['FECHA']][$_count] = $listServicesMain[$i];
                }
            }
        }

        $phpWord->addParagraphStyle(
            'parrafo',
            array(
                'align'         => 'both',
                'spaceAfter'    => 170,
                'wrappingStyle' => 'infront'
            )
        );
        $current = reset($arreglo);

        $dates = array();
        $conta = 0;

        foreach ($arreglo as $x => $d) {
            foreach ($d as $k => $value) {
                if ($value["type"] == "service") {
                    $dates[$conta++] = $x;
                    break;
                }
            }
        }

        $period = array();
        if (count($dates) > 0) {
            $period = new DatePeriod(
                new DateTime($dates[0]),
                new DateInterval('P1D'),
                new DateTime(end($dates))
            );
        }
        if (is_array($period)) {
            if (count($period) > 0) {
                $con = 0;
                foreach ($period as $d) {
                    $key = $d->format('Y-m-d');
                    if (!in_array($key, $dates)) {
                        $data[$con++] = [
                            'FECHA' => $key
                        ];
                    }
                }

                for ($i = 0; $i < count($data); $i++) {
                    $_count = (isset($arreglo[$data[$i]['FECHA']])) ? count($arreglo[$data[$i]['FECHA']]) : 0;
                    $arreglo[$data[$i]['FECHA']][$_count] =
                        array(
                            'ORDEN'                 => $this->convertDate($data[$i]['FECHA'], '-', '/', 1) . '9',
                            'type'                  => 'service',
                            'order'                 => '9',
                            'aurora_code'           => '000000',
                            'equivalence_aurora'    => '',
                            'include_accommodation' => '0',
                            'description'           => $trad['dayOff'],
                            'summary'               => $trad['dayOff'],
                            'itinerary'             => $trad['dayOff'],
                            'CIUIN'                 => '',
                        );
                }
                ksort($arreglo);
            }
        }

        $service = array();
        $service_optional = array();
        $i = 0;
        foreach ($arreglo as $x => $d) {
            foreach ($d as $k => $value) {
                if ($value["type"] === "service") {

                    if ($value['optional'] == 1) {
                        $service_optional[$value['FECHA']]['ORDEN'] = $value['ORDEN'];
                        $service_optional[$value['FECHA']]['type'] = $value['type'];
                        $service_optional[$value['FECHA']]['FECHA'] = $value['FECHA'];
                        $service_optional[$value['FECHA']]['CIUIN'][] = trim($value['CIUIN']);
                        $service_optional[$value['FECHA']]['summary'][] = $value['summary'];
                        $service_optional[$value['FECHA']]['description'][] = $value['description'];
                    } else {
                        $service[$value['FECHA']]['ORDEN'] = $value['ORDEN'];
                        $service[$value['FECHA']]['type'] = $value['type'];
                        $service[$value['FECHA']]['FECHA'] = $value['FECHA'];
                        $service[$value['FECHA']]['CIUIN'][] = trim($value['CIUIN']);
                        $service[$value['FECHA']]['summary'][] = $value['summary'];
                        $service[$value['FECHA']]['description'][] = $value['description'];
                    }
                }
            }
        }
        $styleTable = array(
            'borderSize'  => 6,
            'borderColor' => 'A69F88',
            'cellMargin'  => 80,
            'align'       => 'center',
            'marginRight' => 10
        );
        $styleFirstRow = array('borderBottomColor' => 'A69F88', 'bgColor' => 'A69F88');
        $styleCell = array(
            'valign' => 'center',
            'color'  => '#ffffff',
            'name'   => 'Calibri',
            'size'   => 10,
            'bold'   => true
        );
        $phpWord->addParagraphStyle(
            'titleTable',
            array(
                'align'      => 'center',
                'spaceAfter' => 0
            )
        );
        $phpWord->addParagraphStyle(
            'titleTableSrv',
            array(
                'align'      => 'left',
                'spaceAfter' => 0
            )
        );

        ///------------ INICIO RECORRIDO DE SERVICIOS

        //TITULO SERVICIOS
        $section->addText(
            htmlspecialchars(strtoupper($trad['lblServiceTotal'])),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );

        $phpWord->addTableStyle('servicios', $styleTable, $styleFirstRow);
        $table = $section->addTable('servicios');
        $table->addRow(340, array('exactHeight' => true));
        $table->addCell(1300, $styleCell)->addText(
            htmlspecialchars($trad['thdate']),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTableSrv'
        );
        $table->addCell(2400, $styleCell)->addText(
            htmlspecialchars($trad['thCity']),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTableSrv'
        );
        $table->addCell(5400, $styleCell)->addText(
            htmlspecialchars($trad['thDescrip']),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTableSrv'
        );
        foreach ($service as $clave => $valor) {
            $dateLarge = $this->date_text($this->convertDate($clave, '-', '/', 1), 2, $lang);
            $table->addRow(340, array('exactHeight' => false));
            $table->addCell(1300)->addText(
                htmlspecialchars(ucwords(strtolower($dateLarge))),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                'titleTableSrv'
            );
            for ($i = 0; $i < count($valor['CIUIN']); $i++) {
                if ($i === 0) {
                    $ciuName = $valor['CIUIN'][$i];
                    $ciudad = trim($valor['CIUIN'][$i]);
                } else {
                    $guion = ' - ';
                    if ($ciuName !== trim($valor['CIUIN'][$i])) {
                        $ciudad .= $guion . trim($valor['CIUIN'][$i]);
                    }
                }
            }
            $table->addCell(2400)->addText(
                htmlspecialchars(ucwords(strtolower(trim($ciudad)))),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                'titleTableSrv'
            );

            $cell = $table->addCell(5400);
            for ($i = 0; $i < count($valor['description']); $i++) {
                $cell->addText(
                    htmlspecialchars(strip_tags($this->htmlDecode(trim('- ' . $valor['description'][$i])))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTableSrv'
                );
            }
        }

        if (count($service_optional) > 0) {
            //TITULO SERVICIOS - OPCIONALES
            $section->addText(
                htmlspecialchars(strtoupper($trad['lblServiceTotal']) . ' - ' . strtoupper($trad['optional'])),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );

            $phpWord->addTableStyle('servicios', $styleTable, $styleFirstRow);
            $table = $section->addTable('servicios');
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300, $styleCell)->addText(
                htmlspecialchars($trad['thdate']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTableSrv'
            );
            $table->addCell(2400, $styleCell)->addText(
                htmlspecialchars($trad['thCity']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTableSrv'
            );
            $table->addCell(5400, $styleCell)->addText(
                htmlspecialchars($trad['thDescrip']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTableSrv'
            );
            foreach ($service_optional as $clave => $valor) {
                $dateLarge = $this->date_text($this->convertDate($clave, '-', '/', 1), 2, $lang);
                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1300)->addText(
                    htmlspecialchars(ucwords(strtolower($dateLarge))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTableSrv'
                );
                for ($i = 0; $i < count($valor['CIUIN']); $i++) {
                    if ($i === 0) {
                        $ciuName = $valor['CIUIN'][$i];
                        $ciudad = trim($valor['CIUIN'][$i]);
                    } else {
                        $guion = ' - ';
                        if ($ciuName !== trim($valor['CIUIN'][$i])) {
                            $ciudad .= $guion . trim($valor['CIUIN'][$i]);
                        }
                    }
                }
                $table->addCell(2400)->addText(
                    htmlspecialchars(ucwords(strtolower(trim($ciudad)))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTableSrv'
                );

                $cell = $table->addCell(5400);
                for ($i = 0; $i < count($valor['description']); $i++) {
                    $cell->addText(
                        htmlspecialchars(strip_tags($this->htmlDecode(trim('- ' . $valor['description'][$i])))),
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                        'titleTableSrv'
                    );
                }
            }
        }


        ///------------ FIN RECORRIDO DE SERVICIOS

        /// ------------ INICIO RECORRIDO DE HOTELES

        if (count($listHotels) > 0) {
            $section->addTextBreak(1);
            //TITULO HOTELES
            $section->addText(htmlspecialchars($trad['tltHotel']), array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => true,
                'wrappingStyle' => 'infront'
            ), 'title');
            $phpWord->addTableStyle('tarifas', $styleTable, $styleFirstRow);
            $table = $section->addTable('tarifas');
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300, $styleCell)->addText(
                htmlspecialchars($trad['thDestiny']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(2800, $styleCell)->addText(
                htmlspecialchars($trad['thHotel']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(2000, $styleCell)->addText(
                htmlspecialchars($trad['thType']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(1500, $styleCell)->addText(
                htmlspecialchars($trad['thDel']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(1500, $styleCell)->addText(
                htmlspecialchars($trad['thAl']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $hotelUnique = collect($listHotels)->groupBy('key')->values();

            for ($i = 0; $i < count($hotelUnique); $i++) {
                $nameHotel = $hotelUnique[$i][0]->hotel->name;
                $_web = ($hotelUnique[$i][0]->hotel->web_site) ? htmlspecialchars(trim($hotelUnique[$i][0]->hotel->web_site)) : '-';
                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1300)->addText(
                    htmlspecialchars(ucwords(strtolower(trim($hotelUnique[$i][0]['CIUIN'])))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );

                $DYNAMIC_RATE = 3;
                $hasRateDynamic = false;
                $tiphab = '';
                $rowRooms = [];
                $room_ids = [];

                for ($h = 0; $h < count($hotelUnique[$i]); $h++) {

                    if (count($hotelUnique[$i][$h]->service_rooms) > 0) {

                        for ($i_h = 0; $i_h < count($hotelUnique[$i][$h]->service_rooms); $i_h++) {

                            $hasRateDynamic = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->rate_plan->rates_plans_type_id == $DYNAMIC_RATE;
                            $_occupation = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->room->room_type->occupation;

                            if (($_occupation == 1 and $hotelUnique[$i][$h]->single > 0) or
                                ($_occupation == 2 and $hotelUnique[$i][$h]->double > 0) or
                                ($_occupation == 3 and $hotelUnique[$i][$h]->triple > 0)
                            ) {
                                $_tiphab = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->room->translations[0]->value;
                                $tiphab = htmlspecialchars(trim($_tiphab));
                                $rowRooms[$_tiphab] = $_tiphab;
                                $room_ids[] = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->room->id;
                            }
                        }
                    }
                }

                $room_type_names = [];

                foreach ($room_ids as $room_id) {
                    $value = DB::table('translations')
                        ->where('slug', 'room_name')
                        ->where('object_id', $room_id)
                        ->where('language_id', $language_id)
                        ->value('value'); // devuelve solo la columna "value"

                    $room_type_names[$room_id] = $value;
                }



                $descriptionHasRateDynamic = $hasRateDynamic ? "Hotel con tarifa dinámica y sujeta a cambio." : "";

                $textRun = $table->addCell(2800)->addTextRun('titleTable');
                $textRun->addText(
                    htmlspecialchars(ucwords(strtolower(trim($nameHotel)))),
                    ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58']
                );
                $textRun->addTextBreak(1); // Salto de línea antes de la descripción destacada
                $textRun->addText(
                    $descriptionHasRateDynamic,
                    ['name' => 'Calibri', 'size' => 8, 'color' => '8d0a0d'] // Rojo
                );
                $textRun->addTextBreak(2); // Otro salto si quieres antes del enlace
                $textRun->addText(
                    htmlspecialchars("https://" . $_web),
                    ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58']
                );

                //$tiphab = implode("<w:br />", $rowRooms);
                $tiphab = implode("<w:br />", $room_type_names);

                $table->addCell(2000)->addText(
                    $tiphab,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(1500)->addText(
                    $hotelUnique[$i][0]->date_in,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(1500)->addText(
                    $hotelUnique[$i][0]->date_out,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
            }
            /// ------------ FIN RECORRIDO DE HOTELES
        }

        if (count($listHotelsOptional) > 0) {
            $section->addTextBreak(1);
            //TITULO HOTELES
            $section->addText(htmlspecialchars($trad['tltHotel'] . ' - ' . strtoupper($trad['optional'])), array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => true,
                'wrappingStyle' => 'infront'
            ), 'title');
            $phpWord->addTableStyle('tarifas', $styleTable, $styleFirstRow);
            $table = $section->addTable('tarifas');
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300, $styleCell)->addText(
                htmlspecialchars($trad['thDestiny']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(2800, $styleCell)->addText(
                htmlspecialchars($trad['thHotel']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(2000, $styleCell)->addText(
                htmlspecialchars($trad['thType']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(1500, $styleCell)->addText(
                htmlspecialchars($trad['thDel']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(1500, $styleCell)->addText(
                htmlspecialchars($trad['thAl']),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $hotelUnique = collect($listHotelsOptional)->groupBy('key')->values();
            for ($i = 0; $i < count($hotelUnique); $i++) {

                $nameHotel = $hotelUnique[$i][0]->hotel->name;
                $_web = ($hotelUnique[$i][0]->hotel->web_site) ? htmlspecialchars(trim($hotelUnique[$i][0]->hotel->web_site)) : '-';
                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1300)->addText(
                    htmlspecialchars(ucwords(strtolower(trim($hotelUnique[$i][0]['CIUIN'])))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );

                $DYNAMIC_RATE = 3;
                $hasRateDynamic = false;
                $rowRooms = [];
                $tiphab = '';
                for ($h = 0; $h < count($hotelUnique[$i]); $h++) {
                    if (count($hotelUnique[$i][$h]->service_rooms) > 0) {

                        for ($i_h = 0; $i_h < count($hotelUnique[$i][$h]->service_rooms); $i_h++) {

                            $hasRateDynamic = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->rate_plan->rates_plans_type_id == $DYNAMIC_RATE;
                            $_occupation = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->room->room_type->occupation;

                            if (($_occupation == 1 and $hotelUnique[$i][$h]->single > 0) or
                                ($_occupation == 2 and $hotelUnique[$i][$h]->double > 0) or
                                ($_occupation == 3 and $hotelUnique[$i][$h]->triple > 0)
                            ) {
                                $_tiphab = $hotelUnique[$i][$h]->service_rooms[$i_h]->rate_plan_room->room->translations[0]->value;
                                $tiphab = htmlspecialchars(trim($_tiphab) . '');

                                $rowRooms[$_tiphab] = $_tiphab;
                            }
                        }
                    }
                }

                $descriptionHasRateDynamic = $hasRateDynamic ? "Hotel con tarifa dinámica y sujeta a cambio. <w:br /> " : "";

                $textRun = $table->addCell(2800)->addTextRun('titleTable');
                $textRun->addText(
                    htmlspecialchars(ucwords(strtolower(trim($nameHotel)))),
                    ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58']
                );
                $textRun->addTextBreak(1); // Salto de línea antes de la descripción destacada
                $textRun->addText(
                    $descriptionHasRateDynamic,
                    ['name' => 'Calibri', 'size' => 8, 'color' => '8d0a0d'] // Rojo
                );
                $textRun->addTextBreak(2); // Otro salto si quieres antes del enlace
                $textRun->addText(
                    htmlspecialchars("https://" . $_web),
                    ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58']
                );


                $tiphab = implode("<w:br />", $rowRooms);

                $table->addCell(2000)->addText(
                    $tiphab,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(1500)->addText(
                    $hotelUnique[$i][0]->date_in,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(1500)->addText(
                    $hotelUnique[$i][0]->date_out,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
            }
            /// ------------ FIN RECORRIDO DE HOTELES
        }

        ///------------ INICIO RECORRIDO DE VUELOS

        //TITULO VUELOS
        if (count($listFlights) > 0) {
            $section->addText(
                htmlspecialchars(strtoupper($trad['lblFlights'])),
                array(
                    'name'          => 'Calibri',
                    'size'          => 10,
                    'color'         => '5a5a58',
                    'bold'          => true,
                    'wrappingStyle' => 'infront'
                ),
                'title'
            );

            foreach ($listFlights as $clave => $valor) {
                $codigo = (!empty($valor->CODIGO)) ? $trad['lbl' . $valor->CODIGO] : '';
                $section->addText(htmlspecialchars(
                    $codigo . ' ' .
                        ((ucwords(strtolower(trim($valor->ORIGIN))) != '') ? $trad['thDel'] : '') . ' ' .
                        ucwords(strtolower(trim($valor->ORIGIN))) .
                        ((ucwords(strtolower(trim($valor->DESTINY))) != '') ? $trad['thAl'] : '') . ' ' .
                        ucwords(strtolower(trim($valor->DESTINY)))
                ), array(
                    'name'          => 'Calibri',
                    'size'          => 10,
                    'color'         => '5a5a58',
                    'bold'          => false,
                    'wrappingStyle' => 'infront'
                ), 'title');
                $section->addTextBreak(1);
            }
        }

        $section->addTextBreak(1);
        //INICIO TEXTO "FIN DE SERVICIO"
        $phpWord->addParagraphStyle(
            'textend',
            array(
                'align' => 'center',
            )
        );
        $table = $section->addTable();
        $table->addRow(300, array('exactHeight' => true));
        $cell2 = $table->addCell(9500, array(
            'align'             => 'center',
            'borderTopSize'     => 15,
            'borderBottomSize'  => 15,
            'borderRightColor'  => 'ffffff',
            'borderLeftColor'   => 'ffffff',
            'borderTopColor'    => 'b3b182',
            'borderBottomColor' => 'b3b182',
            'bgColor'           => '#dad7c6'
        ));
        $cell2->addText(
            htmlspecialchars($trad['endService']),
            array('name' => 'Calibri', 'size' => 9, 'color' => 'b3b182', 'bold' => true),
            'textend'
        );
        //FIN TEXTO "FIN DE SERVICIO"
        $section->addTextBreak(1, 'space1');
        //----------------INICIO TABLA DE PRECIOS POR HOTEL + SERVICIOS
        //------TITULO
        $section->addText(htmlspecialchars(strtoupper($trad['tbltitleTotal'])), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => true,
            'wrappingStyle' => 'infront'
        ), 'title');
        $section->addLine($linestyle);

        $totalPax = $totalAdl + $totalChd;
        $label_commission = $has_commission_label ? $trad['with_commission'] : $trad['textRates_HotelService'];
        $section->addText(htmlspecialchars($label_commission . ' ' . $totalPax), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => false,
            'wrappingStyle' => 'infront'
        ), 'title');
        $section->addTextBreak(1, 'space1');
        if ($totalChd > 0) {
            $section->addText(htmlspecialchars($trad['remarkChild']), array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => false,
                'wrappingStyle' => 'infront'
            ), 'title');
            $section->addTextBreak(1, 'space1');
        }

        //------TABLA
        $styleTableHotel = array(
            'borderSize'  => 6,
            'borderColor' => 'A69F88',
            'cellMargin'  => 80,
            'align'       => 'center',
            'marginRight' => 10
        );
        $phpWord->addTableStyle('tarifasHotel', $styleTableHotel);
        $table = $section->addTable('tarifasHotel');

        $this->updateAmountAllServices($quote_id, $client_id);
        $_rates = $this->exportTableAmounts($quote_id, $category_id);

        if (count($_rates) > 0) {

            $table->addRow(340, array('exactHeight' => false));
            $table->addCell(1300)->addText(
                $trad['category'],
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                'titleTable'
            );

            if ($quote->operation == 'ranges') {

                foreach ($_rates[0]['ranges'] as $_range) {
                    $table->addCell(1300)->addText(
                        $_range['from'] . '-' . $_range['to'],
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                        'titleTable'
                    );
                }

                foreach ($_rates as $_rate) {

                    $_category = TypeClass::where('id', $_rate['type_class_id'])
                        ->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ])->first();

                    $table->addRow(340, array('exactHeight' => false));
                    $table->addCell(1300)->addText(
                        $_category->translations[0]->value,
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                        'titleTable'
                    );

                    foreach ($_rate['ranges'] as $_range) {
                        $finalPrice = $this->getPriceAmount(
                            $_range['amount'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(1300)->addText(
                            $finalPrice,
                            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                            'titleTable'
                        );
                    }
                }
            } else { // Paxs

                $query_log_editing_quote = QuoteLog::where('quote_id', $quote_id)->where('type', 'editing_quote')->orderBy('created_at', 'desc')->first(['object_id']);
                $quote_original_id = null;
                if ($query_log_editing_quote) {
                    $quote_original_id = $query_log_editing_quote->object_id;
                }

                $this->geneateExportPassenger($quote_id, $quote_original_id, $category_id, $client_id, $lang, $user_type_id);

                $results = QuoteCategoryRates::where('quote_category_id', $category_id)->where('optional', 0)->get();

                foreach ($results as $result) {
                    $table->addCell(1300)->addText('PAX - ' . $result['type'] . ' - ADL', array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true), 'titleTable');
                }


                $_category = TypeClass::where('id', QuoteCategory::find($category_id)->type_class_id)
                    ->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ])->first();

                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1300)->addText(
                    $_category->translations[0]->value,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                    'titleTable'
                );
                foreach ($results as $result) {
                    $finalPrice = $this->getPriceAmount(
                        $result['total_price'],
                        $client_commission_status,
                        $user_type_id,
                        $client_commission
                    );
                    $table->addCell(1300)->addText($finalPrice, array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
                }
            }

            $table->addRow(340, array('exactHeight' => false));
            $table->addCell(1300)->addText(
                $trad['optional'],
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                'titleTable'
            );

            $table->addRow(340, array('exactHeight' => false));
            $table->addCell(1300)->addText(
                $trad['category'],
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                'titleTable'
            );

            if ($quote->operation == 'ranges') {

                foreach ($_rates[0]['ranges_optional'] as $_range) {
                    $table->addCell(1300)->addText(
                        $_range['from'] . '-' . $_range['to'],
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                        'titleTable'
                    );
                }

                foreach ($_rates as $_rate) {

                    $_category = TypeClass::where('id', $_rate['type_class_id'])
                        ->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ])->first();

                    $table->addRow(340, array('exactHeight' => false));
                    $table->addCell(1300)->addText(
                        $_category->translations[0]->value,
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                        'titleTable'
                    );

                    foreach ($_rate['ranges_optional'] as $_range) {
                        $table->addCell(1300)->addText(
                            $_range['amount'],
                            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                            'titleTable'
                        );
                    }
                }
            } else { // Paxs
                $results = QuoteCategoryRates::where('quote_category_id', $category_id)->where('optional', 1)->get();

                foreach ($results as $result) {
                    $table->addCell(1300)->addText('PAX - ' . $result['type'] . ' - ADL', array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true), 'titleTable');
                }


                $_category = TypeClass::where('id', QuoteCategory::find($category_id)->type_class_id)
                    ->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ])->first();

                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1300)->addText(
                    $_category->translations[0]->value,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                    'titleTable'
                );
                foreach ($results as $result) {
                    $table->addCell(1300)->addText($result['total_price'], array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
                }
            }
        } else {
            $section->addText(
                '---',
                array(
                    'name'          => 'Calibri',
                    'size'          => 10,
                    'color'         => '5a5a58',
                    'bold'          => true,
                    'wrappingStyle' => 'infront'
                ),
                'title'
            );
        }


        //----------------FIN TABLA DE PRECIOS POR HOTEL + SERVICIOS
        $section->addTextBreak(1, 'space1');
        //-----------------INICIO INCLUYE
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad['titleInclude']),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        //-----------------INICIO TITULO ACOMODACION
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        $section->addText(
            htmlspecialchars($trad['acomodation']),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        //------------------FIN TITULO ACOMODACION
        //LISTA DE NOCHES POR CIUDAD
        $phpWord->addFontStyle(
            'StyleSquare',
            array('name' => 'Calibri', 'color' => '5a5a58', 'size' => 10, 'bold' => false)
        );
        $cantNigths = [];
        $nn = 0;
        $listHotels = $this->super_unique($listHotels, 'key');
        for ($i = 0; $i < count($listHotels); $i++) {
            if ($i == 0) {
                $cantNigths[$nn] = $listHotels[$i];
                $cantNigths[$nn]['CANT'] = ((int)$cantNigths[$nn]['nights']);
            } else {
                if (trim($listHotels[$i]['CIUIN']) == trim($cantNigths[$nn]['CIUIN'])) {
                    $cantNigths[$nn]['CANT'] += ((int)$listHotels[$i]['nights']);
                } else {
                    $nn++;
                    $cantNigths[$nn] = $listHotels[$i];
                    $cantNigths[$nn]['CANT'] = ((int)$cantNigths[$nn]['nights']);
                }
            }
        }
        $predefinedMultilevel = array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_SQUARE_FILLED);
        for ($i = 0; $i < count($cantNigths); $i++) {
            $cantDay = $cantNigths[$i]['CANT'];
            if ($cantDay == 1) {
                $textday = $trad['nightStay'];
            } else {
                $textday = $trad['nightStayp'];
            }
            $ciudad = strtolower(trim($cantNigths[$i]['CIUIN']));
            $section->addListItem(
                htmlspecialchars($cantDay . ' ' . $textday . ' ' . ucwords($ciudad)),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        }
        //FIN LISTA DE NOCHES POR CIUDAD
        $section->addTextBreak(1, 'space1');
        $serviceUnique = $this->super_unique($listServices, 'CODIGO');
        //-----------------INICIO TITULO TRASLADOS Y TOURS
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        $section->addText(
            htmlspecialchars($trad['titleTransfers']),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        for ($i = 0; $i < count($serviceUnique); $i++) {
            $section->addListItem(
                htmlspecialchars($this->htmlDecode($serviceUnique[$i]->service->service_translations[0]->name)),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        }
        //------------------FIN TITULO TRASLADOS Y TOURS
        //------------------FIN INCLUYE
        $section->addTextBreak(1, 'space1');
        //-----------------INICIO  TITULO NO INCLUYE
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad['titleNotInclude']),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        $phpWord->addFontStyle(
            'StyleSquare',
            array('name' => 'Calibri', 'color' => '5a5a58', 'size' => 10, 'bold' => false)
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line1']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line2']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line3']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line4']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line5']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line6']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad['textNotInclude_line7']),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        //-----------------FIN  TITULO NO INCLUYE
        $section->addTextBreak(1, 'space1');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        try {
            $objWriter->save(storage_path('helloWorld.docx'));
        } catch (\Exception $e) {
        }


        return response()->download(storage_path('helloWorld.docx'));
    }

    public function wordItinerary($quote_id, $category_id, Request $request)
    {
        $itineraries = [];
        $lang = strtolower($request->input('lang'));
        //$refPax = $request->input('refPax');
        $client_id = $request->input('client_id');
        $use_header = $request->input('use_header');
        $use_client_logo = $request->input('client_logo');
        //$cover = $request->input('cover');
        //$cover_client_logo = $request->input('cover_client_logo');
        //Add price commission based on client settings
        $user_type_id = null;
        $client_commission_status = 0;
        $client_commission = 0;
        $has_commission_label = false;

        if (Auth::check()) {
            $user_type_id = Auth::user()->user_type_id;

            if ($user_type_id == 4) {
                $client = Client::select('commission', 'commission_status')
                    ->where('id', $client_id)
                    ->first();

                if ($client) {
                    $client_commission_status = (int) $client->commission_status;
                    $client_commission = (float) $client->commission;

                    // aplica la lógica del flag
                    $has_commission_label = (
                        $client_commission_status === 1 &&
                        $client_commission > 0 &&
                        $user_type_id === 4
                    );
                }
            }
        }


        $urlPortada = $request->input('urlPortadaLogo'); //es una petición que nos traera la URL del itinerario seleccionado
        $language_id = Language::select('id')->where('iso', $lang)->first()->id;

        $dataLang = File::get(sprintf('%s/lang/%s/itinerary.json', resource_path(), $lang));
        $trad = json_decode($dataLang);

        $client = Client::find($client_id);
        $market_id = $client ? (int)$client->market_id : 0;

        $quote = Quote::find($quote_id);
        $all_quote_services = [];
        $category_id = (int) $category_id;

        if ($category_id == 0) {
            $all_categories = QuoteCategory::where('quote_id', '=', $quote_id)->pluck('id');

            foreach ($all_categories as $new_category_id) {
                $all_quote_services[$new_category_id] = QuoteService::where('quote_category_id', $new_category_id)
                    ->with([
                        'service.serviceDestination.state.translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ])
                    ->with([
                        'service' => function ($query) use ($language_id) {
                            $query->select(['id', 'aurora_code', 'name', 'service_sub_category_id', 'service_type_id']);
                            $query->with([
                                'serviceType' => function ($query) use ($language_id) {
                                    $query->select(['id', 'code']);
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select([
                                                'object_id',
                                                'value'
                                            ]);
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                },
                            ]);
                            $query->with([
                                'galleries' => function ($query) {
                                    $query->select(['object_id', 'url', 'position']);
                                },
                            ]);
                            $query->with([
                                'service_translations' => function ($query) use ($language_id) {
                                    $query->select([
                                        'service_id',
                                        'name',
                                        'description',
                                        'itinerary',
                                        'summary',
                                    ]);
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                            $query->with([
                                'inclusions' => function ($query) use ($language_id) {
                                    $query->select(['service_id', 'day', 'inclusion_id', 'include', 'see_client', 'order']);
                                    $query->where('include', 1);
                                    $query->where('see_client', 1);
                                    $query->with([
                                        'inclusions' => function ($query) use ($language_id) {
                                            $query->select([
                                                'id',
                                                'monday',
                                                'tuesday',
                                                'wednesday',
                                                'thursday',
                                                'friday',
                                                'saturday',
                                                'sunday'
                                            ]);
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->select([
                                                        'object_id',
                                                        'value'
                                                    ]);
                                                    $query->where('language_id', $language_id);
                                                }
                                            ]);
                                        }
                                    ]);
                                },
                            ]);
                            $query->with([
                                'serviceDestination' => function ($query) use ($language_id) {
                                    $query->select(['id', 'service_id', 'state_id']);
                                    $query->with([
                                        'state' => function ($query) use ($language_id) {
                                            $query->select(['id', 'iso']);
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->select([
                                                        'object_id',
                                                        'value'
                                                    ]);
                                                    $query->where('language_id', $language_id);
                                                }
                                            ]);
                                        },
                                    ]);
                                },
                            ]);
                            $query->with([
                                'serviceSubCategory' => function ($query) {
                                    $query->select(['id', 'service_category_id', 'order']);
                                },
                            ]);
                        },
                    ])
                    ->with([
                        'service_rooms.rate_plan_room.room' => function ($query) use ($language_id) {
                            $query->with(['room_type']);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                        }
                    ])
                    ->with([
                        'hotel' => function ($query) use ($language_id) {
                            $query->with([
                                "translations" => function ($query) use ($language_id) {
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                            $query->with([
                                "state.translations" => function ($query) use ($language_id) {
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                            $query->with([
                                "city.translations" => function ($query) use ($language_id) {
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                            $query->with('galeries');
                        }
                    ])
                    ->orderBy('date_in', 'asc')
                    ->orderBy('order', 'asc')
                    ->get()->toArray();
            }
        } else {
            $all_categories = [$category_id];

            $all_quote_services[$category_id] = QuoteService::where('quote_category_id', $category_id)
                ->with([
                    'service.serviceDestination.state.translations' => function ($query) use ($language_id) {
                        $query->where('language_id', $language_id);
                    }
                ])
                ->with([
                    'service' => function ($query) use ($language_id) {
                        $query->select(['id', 'aurora_code', 'name', 'service_sub_category_id', 'service_type_id']);
                        $query->with([
                            'serviceType' => function ($query) use ($language_id) {
                                $query->select(['id', 'code']);
                                $query->with([
                                    'translations' => function ($query) use ($language_id) {
                                        $query->select([
                                            'object_id',
                                            'value'
                                        ]);
                                        $query->where('language_id', $language_id);
                                    }
                                ]);
                            },
                        ]);
                        $query->with([
                            'galleries' => function ($query) {
                                $query->select(['object_id', 'url', 'position']);
                            },
                        ]);
                        $query->with([
                            'service_translations' => function ($query) use ($language_id) {
                                $query->select([
                                    'service_id',
                                    'name',
                                    'description',
                                    'itinerary',
                                    'summary',
                                ]);
                                $query->where('language_id', $language_id);
                            },
                        ]);
                        $query->with([
                            'inclusions' => function ($query) use ($language_id) {
                                $query->select(['service_id', 'day', 'inclusion_id', 'include', 'see_client', 'order']);
                                $query->where('include', 1);
                                $query->where('see_client', 1);
                                $query->with([
                                    'inclusions' => function ($query) use ($language_id) {
                                        $query->select([
                                            'id',
                                            'monday',
                                            'tuesday',
                                            'wednesday',
                                            'thursday',
                                            'friday',
                                            'saturday',
                                            'sunday'
                                        ]);
                                        $query->with([
                                            'translations' => function ($query) use ($language_id) {
                                                $query->select([
                                                    'object_id',
                                                    'value'
                                                ]);
                                                $query->where('language_id', $language_id);
                                            }
                                        ]);
                                    }
                                ]);
                            },
                        ]);
                        $query->with([
                            'serviceDestination' => function ($query) use ($language_id) {
                                $query->select(['id', 'service_id', 'state_id']);
                                $query->with([
                                    'state' => function ($query) use ($language_id) {
                                        $query->select(['id', 'iso']);
                                        $query->with([
                                            'translations' => function ($query) use ($language_id) {
                                                $query->select([
                                                    'object_id',
                                                    'value'
                                                ]);
                                                $query->where('language_id', $language_id);
                                            }
                                        ]);
                                    },
                                ]);
                            },
                        ]);
                        $query->with([
                            'serviceSubCategory' => function ($query) {
                                $query->select(['id', 'service_category_id', 'order']);
                            },
                        ]);
                    },
                ])
                ->with([
                    'service_rooms.rate_plan_room.room' => function ($query) use ($language_id) {
                        $query->with(['room_type']);
                        $query->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ]);
                    }
                ])
                ->with([
                    'hotel' => function ($query) use ($language_id) {
                        $query->with([
                            "translations" => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with([
                            "state.translations" => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with([
                            "city.translations" => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with('galeries');
                    }
                ])
                ->orderBy('date_in', 'asc')
                ->orderBy('order', 'asc')
                ->get()->toArray();
        }

        $quote_services = $all_quote_services[$all_categories[0]];

        //Generar Arreglo de dias
        $totalAdl = 0;
        $totalChd = 0;
        for ($i = 0; $i < count($quote_services); $i++) {

            if ($quote_services[$i]["adult"] > $totalAdl) {
                $totalAdl = $quote_services[$i]["adult"];
            }
            if ($quote_services[$i]["child"] > $totalChd) {
                $totalChd = $quote_services[$i]["child"];
            }

            if ($i == 0) {
                array_push($itineraries, [
                    "date" => [
                        "date"        => Carbon::createFromFormat('d/m/Y', $quote_services[$i]["date_in"])->format('Y-m-d'),
                        "day_of_week" => $trad->semana[Carbon::parse(Carbon::createFromFormat(
                            'd/m/Y',
                            $quote_services[$i]["date_in"]
                        )->format('Y-m-d'))->dayOfWeek]->name,
                        "day" => Carbon::parse(Carbon::createFromFormat(
                            'd/m/Y',
                            $quote_services[$i]["date_in"]
                        )->format('Y-m-d'))->day,
                        "month" => $trad->mes[Carbon::parse(Carbon::createFromFormat(
                            'd/m/Y',
                            $quote_services[$i]["date_in"]
                        )->format('Y-m-d'))->month - 1]->name,
                        "year" => Carbon::parse(Carbon::createFromFormat(
                            'd/m/Y',
                            $quote_services[$i]["date_in"]
                        )->format('Y-m-d'))->year
                    ],
                    "date_title" => "",
                    "destinies"  => [],
                    "services"   => []
                ]);
            } else {

                $find_date = false;
                foreach ($itineraries as $itinerary) {
                    if ($itinerary["date"]["date"] == Carbon::createFromFormat(
                        'd/m/Y',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d')) {
                        $find_date = true;
                    }
                }
                if ($find_date == false) {
                    array_push($itineraries, [
                        "date" => [
                            "date" => Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_services[$i]["date_in"]
                            )->format('Y-m-d'),
                            "day_of_week" => $trad->semana[Carbon::parse(Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_services[$i]["date_in"]
                            )->format('Y-m-d'))->dayOfWeek]->name,
                            "day" => Carbon::parse(Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_services[$i]["date_in"]
                            )->format('Y-m-d'))->day,
                            "month" => $trad->mes[Carbon::parse(Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_services[$i]["date_in"]
                            )->format('Y-m-d'))->month - 1]->name,
                            "year" => Carbon::parse(Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_services[$i]["date_in"]
                            )->format('Y-m-d'))->year
                        ],
                        "date_title" => "",
                        "destinies"  => [],
                        "services"   => []
                    ]);
                }
            }
        }

        $itineraries = [];
        $textSearch = "<p>###</p>";
        for ($i = 0; $i < count($quote_services); $i++) {
            $date_service = Carbon::createFromFormat('d/m/Y', $quote_services[$i]["date_in"])->format('Y-m-d');
            $name = '';
            if (!empty($quote_services[$i]["service"]["service_translations"]) and count($quote_services[$i]["service"]["service_translations"]) > 0) {
                $name = $quote_services[$i]["service"]["service_translations"][0]["itinerary"];
            }
            $parrafo = $name;
            $count = substr_count($parrafo, $textSearch);

            $pattern = '/^<\/p><p>/';
            $parrafo = preg_replace($pattern, '<p>', $parrafo);

            $pattern = '/<\/p><p>$/';
            $parrafo = preg_replace($pattern, '</p>', $parrafo);

            $textExplode = explode($textSearch, $parrafo);
            $_date_in = Carbon::createFromFormat('d/m/Y', $quote_services[$i]["date_in"])->format('Y-m-d');
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    if ($j > 0) {
                        // AUMENTAR UN DIA AL DATE
                        $fecha_add = Carbon::parse($_date_in)->addDays($j - 1)->format('Y-m-d');
                        array_push($itineraries, $fecha_add);
                    }
                }
            } else {
                array_push($itineraries, $date_service);
            }
        }
        $itinerary = array_flip($itineraries);
        foreach ($itinerary as $key => $date) {
            $itinerary[$key] = [];
        }

        for ($i = 0; $i < count($quote_services); $i++) {
            $date_service = Carbon::createFromFormat('d/m/Y', $quote_services[$i]["date_in"])->format('Y-m-d');
            if ($quote_services[$i]["type"] == "hotel") {
                for ($j = 0; $j < ((int)($quote_services[$i]["nights"])); $j++) {
                    $fecha_add = Carbon::parse($date_service)->addDays($j)->format('Y-m-d');
                    $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->dayOfWeek]->name;
                    $day = Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->day;
                    $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->month - 1]->name;
                    $year = Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->year;
                    if (!isset($itinerary[$fecha_add])) {
                        $itinerary[$fecha_add] = [];
                    }
                    $itinerary[$fecha_add][count($itinerary[$fecha_add])] = [
                        'key'         => 'hotel_' . $quote_services[$i]["hotel"]["id"] . '_' . $quote_services[$i]["nights"] . '_' . $fecha_add,
                        'date'        => $fecha_add,
                        "day_of_week" => $date_of_week,
                        "day"         => $day,
                        "month"       => $month,
                        "year"        => $year,
                        "date_title"  => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                        "type"        => "hotel",
                        "optional"    => $quote_services[$i]["optional"],
                        "services"    => [
                            "id"        => $quote_services[$i]["hotel"]["id"],
                            "name"      => $quote_services[$i]["hotel"]["name"],
                            "web"       => ($quote_services[$i]["hotel"]["web_site"]) ? htmlspecialchars(trim($quote_services[$i]["hotel"]["web_site"])) : "-",
                            "check_in"  => $quote_services[$i]["hotel"]["check_in_time"],
                            "check_out" => $quote_services[$i]["hotel"]["check_out_time"],
                            "destiny"   => $quote_services[$i]["hotel"]["state"]["translations"][0]["value"],
                            "image_"    => (count($quote_services[$i]["hotel"]["galeries"]) > 0)
                                ? $quote_services[$i]["hotel"]["galeries"][0]["url"]
                                : null,
                            "image" => (count($quote_services[$i]["hotel"]["galeries"]) > 0)
                                ? $this->verifyCloudinaryImg(
                                    $quote_services[$i]["hotel"]["galeries"][0]["url"],
                                    400,
                                    200,
                                    ''
                                )
                                : null,
                            "nights"  => $quote_services[$i]["nights"],
                            "date_in" => $fecha_add
                        ]
                    ];
                }
            }

            if ($quote_services[$i]["type"] == "service") {
                $parrafo = $this->htmlDecode(htmlspecialchars_decode($quote_services[$i]["service"]["service_translations"][0]["itinerary"]));
                $nameCommercial = strip_tags($this->htmlDecode(htmlspecialchars_decode($quote_services[$i]["service"]["service_translations"][0]["name"])));
                $count = substr_count($parrafo, $textSearch);
                $textExplode = explode($textSearch, $parrafo);
                $food = '';
                $inclusions = collect($quote_services[$i]['service']['inclusions'])->groupBy('day');
                if ($count > 0) {
                    for ($j = 0; $j <= $count; $j++) {
                        $service_name = $textExplode[$j];
                        $fecha_add = Carbon::parse($date_service)->addDays($j)->format('Y-m-d');
                        $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->dayOfWeek]->name;

                        $day = Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->day;
                        $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->month - 1]->name;
                        $year = Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->year;
                        if (!isset($itinerary[$fecha_add])) {
                            $itinerary[$fecha_add] = [];
                        }

                        if ($quote_services[$i]['hour_in'] == '' || $quote_services[$i]['hour_in'] == null) {
                            $service_hour_in = $this->get_hour_ini($quote_services[$i]['schedule_id'], $fecha_add, $quote_services[$i]["service"]["id"]);
                        } else {
                            $service_hour_in = $quote_services[$i]['hour_in'];
                        }

                        $itinerary[$fecha_add][count($itinerary[$fecha_add])] = [
                            'key'         => 'service_' . $quote_services[$i]["service"]["id"] . '_' . $fecha_add,
                            'date'        => $fecha_add,
                            "hour_in"     => $service_hour_in,
                            "day_of_week" => $date_of_week,
                            "day"         => $day,
                            "month"       => $month,
                            "year"        => $year,
                            "date_title"  => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                            "type"        => "service",
                            "optional"    => $quote_services[$i]["optional"],
                            "services"    => [
                                "id"              => $quote_services[$i]["service"]["id"],
                                "name"            => str_replace(" & ", " ", $nameCommercial),
                                "name_commercial" => str_replace(" & ", " ", $nameCommercial),
                                "itinerary"       => str_replace(" & ", " ", $service_name),
                                "image_"          => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                    ? $quote_services[$i]["service"]["galleries"][0]["url"]
                                    : null,
                                "image" => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                    ? $this->verifyCloudinaryImg(
                                        $quote_services[$i]["service"]["galleries"][0]["url"],
                                        400,
                                        200,
                                        ''
                                    )
                                    : null,

                                "destiny"      => $quote_services[$i]["service"]["service_destination"][0]["state"]["translations"][0]["value"],
                                "date_in"      => convertDate($quote_services[$i]["date_in"], "/", "-", 1),
                                "food"         => $food,
                                "inclusions"   => $inclusions,
                                "code"         => $quote_services[$i]["service"]["aurora_code"],
                                "service_type" => $quote_services[$i]["service"]['service_type']["translations"][0]["value"],
                                "isTour"       => $quote_services[$i]["service"]["service_sub_category"]['service_category_id'] == 9
                            ]
                        ];
                    }
                } else {
                    $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                        'd/m/Y',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->dayOfWeek]->name;
                    $day = Carbon::parse(Carbon::createFromFormat(
                        'd/m/Y',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->day;
                    $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                        'd/m/Y',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->month - 1]->name;
                    $year = Carbon::parse(Carbon::createFromFormat(
                        'd/m/Y',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->year;

                    if ($quote_services[$i]['hour_in'] == '' || $quote_services[$i]['hour_in'] == null) {
                        $service_hour_in = $this->get_hour_ini($quote_services[$i]['schedule_id'], $date_service, $quote_services[$i]["service"]["id"]);
                    } else {
                        $service_hour_in = $quote_services[$i]['hour_in'];
                    }

                    $itinerary[$date_service][count($itinerary[$date_service])] = [
                        'key'         => 'service_' . $quote_services[$i]["service"]["id"] . '_' . $date_service,
                        'date'        => $date_service,
                        "hour_in"     => $service_hour_in,
                        "day_of_week" => $date_of_week,
                        "day"         => $day,
                        "month"       => $month,
                        "year"        => $year,
                        "date_title"  => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                        "type"        => "service",
                        "optional"    => $quote_services[$i]["optional"],
                        "services"    => [
                            "id"              => $quote_services[$i]["service"]["id"],
                            "name"            => str_replace(" & ", " ", $nameCommercial),
                            "name_commercial" => str_replace(" & ", " ", $nameCommercial),
                            "itinerary"       => str_replace(" & ", " ", $parrafo),
                            "image_"          => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                ? $quote_services[$i]["service"]["galleries"][0]["url"]
                                : null,
                            "image" => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                ? $this->verifyCloudinaryImg(
                                    $quote_services[$i]["service"]["galleries"][0]["url"],
                                    400,
                                    200,
                                    ''
                                )
                                : null,
                            "destiny"      => $quote_services[$i]["service"]["service_destination"][0]["state"]["translations"][0]["value"],
                            "date_in"      => convertDate($quote_services[$i]["date_in"], "/", "-", 1),
                            "food"         => $food,
                            "inclusions"   => $inclusions,
                            "code"         => $quote_services[$i]["service"]["aurora_code"],
                            "service_type" => $quote_services[$i]["service"]['service_type']["translations"][0]["value"],
                            "isTour"       => $quote_services[$i]["service"]["service_sub_category"]['service_category_id'] == 9
                        ]
                    ];
                }
                //
            }

            if ($quote_services[$i]["type"] == "flight") {
                $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                    'd/m/Y',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->dayOfWeek]->name;
                $day = Carbon::parse(Carbon::createFromFormat(
                    'd/m/Y',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->day;
                $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                    'd/m/Y',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->month - 1]->name;
                $year = Carbon::parse(Carbon::createFromFormat(
                    'd/m/Y',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->year;
                $itinerary[$date_service][count($itinerary[$date_service])] = [
                    'key'         => $quote_services[$i]["code_flight"] . '_' . $date_service,
                    'date'        => $date_service,
                    "day_of_week" => $date_of_week,
                    "day"         => $day,
                    "month"       => $month,
                    "year"        => $year,
                    "date_title"  => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                    "type"        => "flight",
                    "optional"    => $quote_services[$i]["optional"],
                    "code_flight" => $quote_services[$i]["code_flight"],
                    "origin"      => $quote_services[$i]["origin"],
                    "destiny"     => $quote_services[$i]["destiny"]
                ];
            }
        }
        ksort($itinerary);
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('calibri');
        if ($lang == 'es') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::ES_ES));
        } elseif ($lang == 'en') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_US));
        } elseif ($lang == 'pt') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::PT_BR));
        } elseif ($lang == 'it') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::IT_IT));
        } else {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_US));
        }

        //Todo Creamos una pagina
        $section = $phpWord->addSection(array('bgColor' => '#363636'));
        $section->addTextBreak(10);

        //$use_client_logo = 2;

        if ($use_client_logo == 4) {
            $section->addTextBreak(18);
        } else {

            if (!empty($urlPortada)) {
                $src = public_path() . '/' . $urlPortada; // Traemos la url de la portada que está en public
                //Creamos la caja en la cual estará alojada la portada
                $section->addImage(
                    $src,
                    array(
                        'widthwordSkeleton' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(16),
                        'height'            => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(22.5),
                        'positioning'       => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                        'posHorizontal'     => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                        'posHorizontalRel'  => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                        'posVerticalRel'    => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                        'marginLeft'        => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(15.5),
                        'marginTop'         => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.55),
                        'wrappingStyle'     => \PhpOffice\PhpWord\Style\Image::WRAP_BEHIND,

                    )
                );
            }
        }

        // 1) Calcula el ancho utilizable (página – márgenes)
        $sectionStyle = $section->getStyle();
        $usableWidth = $sectionStyle->getPageSizeW()
            - $sectionStyle->getMarginLeft()
            - $sectionStyle->getMarginRight();

        $section->getPhpWord()->addTableStyle('Box', [
            'borderSize'  => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin'  => Converter::pixelToTwip(5),
            'bgColor'     => 'DAD7C6',                      // #dad7c6
            'unit'        => TblWidth::TWIP,                // medimos en twips
            'width'       => $usableWidth,                  // exactamente el ancho útil
            'layout'      => TableStyle::LAYOUT_FIXED,      // ¡clave!
        ]);

        // Creamos nueva pagina
        $section = $phpWord->addSection();
        //Encabezado de pagina
        $phpWord->addParagraphStyle(
            'encabezado',
            array(
                'align'         => 'right',
                'spaceAfter'    => 0,
                'wrappingStyle' => 'infront',
            )
        );
        $subsequent = $section->addHeader();
        $subsequent->addText(htmlspecialchars($trad->textHeader), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '#A81B20',
            'wrappingStyle' => 'infront',
            'align'         => 'right'
        ), 'encabezado');
        //-----------------INICIO TITULO LIMATOURS
        //ESTILO DE LINEA
        $linestyle = array(
            'width'      => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(12),
            'height'     => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(0),
            'weight'     => 2,
            'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1),
            'color'      => '#aea792',
        );
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
                'spaceAfter'    => 0,
            )
        );
        $section->addText(
            htmlspecialchars('LIMATOURS'),
            array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        //-----------------INICIO CONTENIDO TEXTO
        $phpWord->addParagraphStyle(
            'paragraft',
            array(
                'align'         => 'both',
                'spaceAfter'    => 0,
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->limatours_text),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'paragraft'
        );
        $section->addTextBreak(1);
        //-----------------FIN CONTENIDO TEXTO
        //-----------------FIN TITULO LIMATOURS

        //-----------------INICIO  TITULO DIA DIA
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->dayToday),
            array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        //        $section->addTextBreak(1);
        //------------------FIN  TITULO DIA DIA

        //-----------------INICIO ITINERARIO
        $phpWord->addParagraphStyle(
            'titleday',
            array(
                'align'         => 'left',
                'spaceAfter'    => 10,
                'wrappingStyle' => 'infront',
            )
        );
        $phpWord->addParagraphStyle(
            'fecha',
            array(
                'align'      => 'left',
                'spaceAfter' => 170,
            )
        );

        $predefinedMultilevel = array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_SQUARE_FILLED);
        $phpWord->addFontStyle(
            'myOwnStyle',
            array('name' => 'Calibri', 'color' => '#545454', 'size' => 9, 'bold' => true)
        );
        $phpWord->addFontStyle('myLinkStyle', array(
            'name'      => 'Calibri',
            'color'     => '0000FF',
            'size'      => 9,
            'bold'      => true,
            'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
        ));

        ///------------ INICIO RECORRIDO DE SERVICIOS
        $predefinedMultilevel = array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_SQUARE_FILLED);
        error_reporting(0);
        //        return json_encode($itineraries);
        $k = 1;
        $foodDays = [];
        $contents = [];
        $serviceOptionals = [];
        foreach ($itinerary as $date_key => $services) {

            $destinies_array = [];
            $foods_array = [];
            $foodsCollection = collect([]);
            // Obtengo los destinos
            foreach ($services as $service) {
                if (!empty($service["services"]["destiny"])) {
                    $destinies_array[] = $service["services"]["destiny"];
                }
                //Obtengo las comidas del día
                if ($service["type"] == "service") {
                    $serviceId = $service['services']['id'];

                    if (!isset($foodDays[$serviceId])) {
                        $foodDays[$serviceId] = 1;
                    } else {
                        $foodDays[$serviceId] = $foodDays[$serviceId] + 1;
                    }

                    $dayToCheck = $foodDays[$serviceId];

                    if ($service["services"]["inclusions"]->has($dayToCheck)) {
                        $inclusionsFood = $this->getServiceFoodText($service["services"]["inclusions"][$dayToCheck], $trad);

                        foreach ($inclusionsFood as $food) {
                            $foodsCollection->push($food);
                        }
                    }
                }
            }

            $foods_collection = $foodsCollection->sortBy('order')->map(function ($food) {
                return $food['value'];
            })->unique()->toArray();

            $foods_array = array_merge($foods_array, $foods_collection);
            $foods_array = array_diff($foods_array, [$trad->breakfast]); // eliminamos el desayuno
            $foods = implode(', ', array_unique($foods_array));

            $destinations = implode(' - ', array_unique($destinies_array));

            $section->addText(
                htmlspecialchars($trad->day . ' ' . ($k) . (($destinations > 0) ? (' | ' . $destinations) : '')),
                array('name' => 'Calibri', 'size' => 11, 'color' => '#b3b182', 'bold' => true),
                'titleday'
            );
            $section->addText(
                htmlspecialchars($services[0]["date_title"]),
                array('name' => 'Calibri', 'size' => 11, 'color' => '#b3b182', 'bold' => true),
                'title_date'
            );
            foreach ($services as $service) {

                if (count($itinerary[$date_key]) > 0) {
                    if ($service["type"] == "service") {

                        if ($service["optional"] == 1) {

                            $htmlContent = '
                            <div style="color:#808080;text-align:justify;text-transform:uppercase;background-color: #dad7c6;">
                                <strong><em><u>' . strtoupper($trad->optional_not_included) . '</u></em></strong>
                            </div>';

                            Html::addHtml($section, $htmlContent, false, false);
                        }

                        if (isset($service['services']["isTour"]) && $service['services']["isTour"]) {

                            if (isset($service['hour_in']) && !empty($service['hour_in'])) {
                                $date = DateTime::createFromFormat('H:i:s', $service['hour_in']);
                                if ($date === false) {
                                    $date = DateTime::createFromFormat('H:i', $service['hour_in']);
                                }
                                if ($date !== false) {
                                    $hourservice = $date->format('H:i') . " - ";
                                } else {
                                    $hourservice = '';
                                }
                            } else {
                                $hourservice = '';
                            }

                            $textrun_extra_texts = $section->addTextRun('spaceAfter');
                            $textrun_extra_texts->addText(
                                strip_tags(html_entity_decode(htmlspecialchars($hourservice . $service['services']["name_commercial"]))) . ' - ',
                                array(
                                    'name'          => 'Calibri',
                                    'size'          => 10,
                                    'color'         => '#000000',
                                    'wrappingStyle' => 'infront',
                                    'bgColor'       => '#ebebeb',
                                ),
                                'paragraft'
                            );
                            $textrun_extra_texts->addText(
                                '[' . $service['services']["service_type"] . ']',
                                array(
                                    'name'    => 'Calibri',
                                    'size'    => 10,
                                    'bold'    => true,
                                    'color'   => '#000000',
                                    'bgColor' => '#ebebeb',
                                ),
                                'paragraft'
                            );
                        }

                        if (!empty($service["services"]["itinerary"])) {
                            $content = $service["services"]["itinerary"];
                            $content = str_replace(['<h5', '</h5>'], ['<p style="font-size:11px;"', '</p>'], $content);
                            $content = str_replace(['<p>'], ['<p style="color:#808080;text-align:justify;">'], $content);
                            $content = str_replace(["<br>", "<br />", "</br>"], '&nbsp;', $content);

                            if (!empty($content)) {
                                // $content = str_replace(['&amp;'], '', $content);
                                if ($service["optional"] == 1) {
                                    $content = str_replace('###', '&nbsp;', $content);
                                    // 3) Crea la tabla y celda que actúan como “caja” contenedora
                                    $table = $section->addTable('Box');
                                    $table->addRow();
                                    $cell = $table->addCell($usableWidth, ['valign' => 'top']);
                                    Html::addHtml($cell, '<div style="text-align: justify;">' . $content . '</div>', false, true);
                                } else {
                                    $content = str_replace('###', '&nbsp;', $content);
                                    $htmlContent = '<div style="color:#808080;text-align:justify;">' . $content . '</div>';
                                    Html::addHtml($section, $htmlContent, false, false);
                                }
                            }
                        }
                    }
                    if ($service["type"] == "flight") {
                        $content = "";

                        if ($service["code_flight"] == "AEIFLT") {
                            if ($service["destiny"] == "LIM") {
                                $content = sprintf($trad->flight_international_to, $service["destiny"]);
                            }

                            if ($service["origin"] == "LIM") {
                                $content = sprintf($trad->flight_international_from, $service["origin"]);
                            }
                        }

                        if ($service["code_flight"] == "AECFLT") {
                            $content = sprintf($trad->flight_national, $service["origin"], $service["destiny"]);

                            if (empty($service['origin'])) {
                                $content = str_replace([' from ', ' de ', ' desde '], '', $content);
                            }

                            if (empty($service['destiny'])) {
                                $content = str_replace([' to ', ' a ', ' para '], '', $content);
                            }
                        }

                        if (!empty($content)) {
                            $htmlContent = '<p style="color:#808080;text-align:justify;">' . $content . '</p>';
                            Html::addHtml($section, $htmlContent, false, false);
                        }
                    }
                }
            }

            $unique_hotels = collect($services)->unique('key')->values();

            foreach ($unique_hotels as $service) {
                if ($service["type"] == "hotel") {
                    $listItemRunHotel = $section->addListItemRun(0, $predefinedMultilevel, 'P-Style');

                    if ($category_id === 0) {
                        $listItemRunHotel->addText(
                            htmlspecialchars($trad->accommodation . ' ' . $trad->hotel_selected),
                            'myOwnStyle'
                        );
                    } else {
                        $listItemRunHotel->addText(
                            htmlspecialchars($trad->accommodation . ' ' . ucwords(strtolower($service["services"]["name"]) . ' ')),
                            'myOwnStyle'
                        );
                        $listItemRunHotel->addLink(
                            "https://" . $service["services"]["web"], //fixed
                            '(' . $service["services"]["web"] . ')',
                            'myLinkStyle'
                        );
                        $section->addListItem(
                            htmlspecialchars('CHECK IN: ' . $service["services"]["check_in"] . ' CHECK OUT: ' . $service["services"]["check_out"]),
                            0,
                            'myOwnStyle',
                            $predefinedMultilevel,
                            'P-Style'
                        );
                    }
                    break;
                }
            }


            if ($k === 1) {
                if ($foods != '') {
                    $section->addListItem(
                        htmlspecialchars($trad->supplyIncluded . ': ' . ucwords($foods)),
                        0,
                        'myOwnStyle',
                        $predefinedMultilevel,
                        'P-Style'
                    );
                }
            }
            if ($k !== 1) {
                if ($foods != '') {
                    $food = $trad->breakfast . ', ' . $foods;
                } else {
                    $food = $trad->breakfast;
                }
                $section->addListItem(
                    htmlspecialchars($trad->supplyIncluded . ': ' . ucwords($food)),
                    0,
                    'myOwnStyle',
                    $predefinedMultilevel,
                    'P-Style'
                );
            }
            $section->addTextBreak(1, 'space1');
            $k++;
        }

        // return $contents;

        //INICIO TEXTO "FIN DE SERVICIO"
        $phpWord->addParagraphStyle(
            'textend',
            array(
                'align' => 'center',
            )
        );
        $table = $section->addTable();
        $table->addRow(300, array('exactHeight' => true));
        $cell2 = $table->addCell(9500, array(
            'align'             => 'center',
            'borderTopSize'     => 15,
            'borderBottomSize'  => 15,
            'borderRightColor'  => 'ffffff',
            'borderLeftColor'   => 'ffffff',
            'borderTopColor'    => 'b3b182',
            'borderBottomColor' => 'b3b182',
            'bgColor'           => '#dad7c6'
        ));
        $cell2->addText(
            htmlspecialchars($trad->endService),
            array('name' => 'Calibri', 'size' => 9, 'color' => 'b3b182', 'bold' => true),
            'textend'
        );
        //FIN TEXTO "FIN DE SERVICIO"
        $section->addTextBreak(1, 'space1');

        $this->updateAmountAllServices($quote_id, $client_id, true);
        ///------------ FIN RECORRIDO DE SERVICIOS

        $quote_services_prev = $quote_services;

        //----------------INICIO TABLA DE PRECIOS POR HOTEL + SERVICIOS
        //------TITULO
        $section->addText(htmlspecialchars(strtoupper($trad->tbltitleTotal)), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => true,
            'wrappingStyle' => 'infront'
        ), 'title');
        $section->addLine($linestyle);

        if (!$has_commission_label) {
            $totalPax = $totalAdl + $totalChd;
            $section->addText(htmlspecialchars($trad->textRates_HotelService . ' ' . $totalPax), array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => false,
                'wrappingStyle' => 'infront'
            ), 'title');
        }

        $label_commission = $has_commission_label ? $trad->with_commission : $trad->textRates_HotelService_comission;

        $section->addText(htmlspecialchars($label_commission), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => false,
            'wrappingStyle' => 'infront'
        ), 'title');
        $section->addTextBreak(1, 'space1');
        if ($totalChd > 0) {
            $section->addText(htmlspecialchars($trad->remarkChild), array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => false,
                'wrappingStyle' => 'infront'
            ), 'title');
            $section->addTextBreak(1, 'space1');
        }

        //------TABLA
        $styleTableHotel = array(
            'borderSize'  => 6,
            'borderColor' => 'A69F88',
            'cellMargin'  => 80,
            'align'       => 'center',
            'marginRight' => 10
        );
        $phpWord->addTableStyle('tarifasHotel', $styleTableHotel);
        $table = $section->addTable('tarifasHotel');
        $section->addTextBreak(1);

        foreach ($all_categories as $key_category => $category_id) {
            $_rates = $this->exportTableAmounts($quote_id, $category_id, true, $language_id);
            //        dd(json_encode($_rates));

            if (count($_rates) > 0) {

                // Lógica para cuando no son rangos, y los sgl/dbl/tpl se repitan juntarlos

                if ($_rates[0]['flags']['multiple_passengers'] == 1) {
                    $type_rates_render_by = "passengers";
                } else {
                    $type_rates_render_by = "passengers";
                    $type_rate_general = [];
                }

                if ($key_category === 0) {
                    $table->addRow(340, array('exactHeight' => false));
                    $table->addCell(1300)->addText(
                        $trad->category,
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                        'titleTable'
                    );
                }

                if ($quote->operation == 'ranges') {

                    if ($key_category === 0) {
                        foreach ($_rates[0]['ranges'] as $_range) {
                            $table->addCell(1300)->addText(
                                $_range['from'] . '-' . $_range['to'],
                                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                                'titleTable'
                            );
                        }
                    }

                    foreach ($_rates as $_rate) {

                        $_category = TypeClass::where('id', $_rate['type_class_id'])
                            ->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->where('language_id', $language_id);
                                }
                            ])->first();

                        $table->addRow(340, array('exactHeight' => false));
                        $table->addCell(1300)->addText(
                            $_category->translations[0]->value,
                            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                            'titleTable'
                        );

                        foreach ($_rate['ranges'] as $_range) {

                            $finalPrice = $this->getPriceAmount(
                                $_range['amount'],
                                $client_commission_status,
                                $user_type_id,
                                $client_commission
                            );


                            $table->addCell(1300)->addText(
                                $finalPrice,
                                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                                'titleTable'
                            );
                        }
                    }
                } else { // Paxs
                    $query_log_editing_quote = QuoteLog::where('quote_id', $quote_id)->where('type', 'editing_quote')->orderBy('created_at', 'desc')->first(['object_id']);
                    $quote_original_id = null;
                    if ($query_log_editing_quote) {
                        $quote_original_id = $query_log_editing_quote->object_id;
                    }

                    $this->geneateExportPassenger($quote_id, $quote_original_id, $category_id, $client_id, $lang, $user_type_id);

                    $results = QuoteCategoryRates::where('quote_category_id', $category_id)->where('optional', 0)->get();

                    if ($key_category === 0) {
                        foreach ($results as $result) {
                            //$table->addCell(1300)->addText('PAX - ' . $result['type'] . ' - ADL', array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true), 'titleTable');
                            $table->addCell(1300)->addText($trad->PAX_DBL_ADL, array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true), 'titleTable');
                        }
                    }

                    $_category = TypeClass::where('id', QuoteCategory::find($category_id)->type_class_id)
                        ->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ])->first();

                    $table->addRow(340, array('exactHeight' => false));
                    $table->addCell(1300)->addText(
                        $_category->translations[0]->value,
                        array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true),
                        'titleTable'
                    );
                    foreach ($results as $result) {
                        $finalPrice = $this->getPriceAmount(
                            $result['total_price'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(1300)->addText($finalPrice, array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
                    }
                }


                $optionalResults = QuoteService::join('quote_service_amounts', 'quote_services.id', '=', 'quote_service_amounts.quote_service_id')
                    ->where('quote_services.quote_category_id', $category_id)
                    ->where('quote_services.optional', 1)
                    ->select(
                        'quote_service_amounts.price_adult',
                        'quote_service_amounts.price_child',
                        'quote_services.type',
                        'quote_services.object_id'
                    )
                    ->get();
                // Crear una tabla con 2 columnas
                //$table = $section->addTable(array('borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80));
                if (count($optionalResults) > 0) {
                    $section->addTextBreak(1);

                    // Estilos de la tabla
                    $styleTableOptional = [
                        'borderSize'  => 6,
                        'borderColor' => 'A69F88',
                    ];

                    // Estilos de la primera fila (cabecera)
                    $styleFirstRowOptional = [
                        'bgColor' => 'A69F88'
                    ];

                    // Estilo de texto para la cabecera
                    $styleHeaderTextOptional = [
                        'name'  => 'Calibri',
                        'size'  => 10,
                        'color' => 'FFFFFF',
                        'bold'  => true
                    ];

                    // Registrar los estilos de la tabla
                    $phpWord->addTableStyle('tarifasOptional', $styleTableOptional, $styleFirstRowOptional);

                    // Crear la tabla
                    $table = $section->addTable('tarifasOptional');

                    // Fila de cabecera con altura mínima
                    if ($key_category === 0) {
                        $table->addRow(300, ['exactHeight' => true]);
                        $table->addCell(8000, ['bgColor' => 'A69F88', 'valign' => 'center'])
                            ->addText(ucfirst($trad->optional), $styleHeaderTextOptional, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                        $table->addCell(2000, ['bgColor' => 'A69F88', 'valign' => 'center'])
                            ->addText($trad->amount, $styleHeaderTextOptional, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    }

                    foreach ($optionalResults as $result) {
                        $table->addRow();

                        try {
                            $optionalText = "";

                            if ($result["type"] == 'service') {
                                $serviceTranslation = ServiceTranslation::where('service_id', $result["object_id"])
                                    ->where('language_id', $language_id)
                                    ->first();
                                $optionalText = $serviceTranslation ? $serviceTranslation->name : "Servicio #" . $result["object_id"];
                            } elseif ($result["type"] == 'hotel') {
                                $hotel = Hotel::find($result["object_id"]);
                                if ($hotel) {
                                    $optionalText = $hotel->name;

                                    // Si el nombre viene vacío, usar un valor por defecto
                                    if (empty(trim($optionalText))) {
                                        $optionalText = "Hotel #" . $result["object_id"];
                                    }
                                }
                            }

                            // Solo agregar fila si tenemos texto válido
                            if (!empty(trim($optionalText))) {
                                $table->addCell(8000, [
                                    'borderTopSize' => 6,
                                    'borderBottomSize' => 6,
                                    'borderLeftSize' => 6,
                                    'borderRightSize' => 6,
                                    'borderColor'   => 'A69F88',
                                    'valign' => 'center'
                                ])->addText(
                                    htmlspecialchars($optionalText),
                                    ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true]
                                );

                                // Columna "Amount" con manejo de valor nulo
                                $amountText = isset($result['price_adult']) ? roundLito($result['price_adult']) : '0.00';

                                $finalPrice = $this->getPriceAmount(
                                    $amountText,
                                    $client_commission_status,
                                    $user_type_id,
                                    $client_commission
                                );

                                $table->addCell(2000, [
                                    'borderTopSize' => 6,
                                    'borderBottomSize' => 6,
                                    'borderLeftSize' => 6,
                                    'borderRightSize' => 6,
                                    'borderColor'   => 'A69F88',
                                    'valign' => 'center',
                                    'align' => 'center'
                                ])->addText(
                                    $finalPrice,
                                    ['name'      => 'Calibri', 'size' => 10, 'color' => '5a5a58'],
                                    ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
                                );
                            }
                        } catch (\Exception $e) {
                            continue; // Continuar con el siguiente si hay error
                        }
                    }
                }
            } else {
                $section->addText(
                    '---',
                    array(
                        'name'          => 'Calibri',
                        'size'          => 10,
                        'color'         => '5a5a58',
                        'bold'          => true,
                        'wrappingStyle' => 'infront'
                    ),
                    'title'
                );
            }
        }

        //***************************************************************
        //----------------FIN TABLA DE PRECIOS POR HOTEL + SERVICIOS
        $section->addTextBreak(1, 'space1');
        $styleTable = array(
            'borderSize'  => 6,
            'borderColor' => 'A69F88',
            'cellMargin'  => 80,
            'align'       => 'center',
            'marginRight' => 10
        );
        $styleFirstRow = array('borderBottomColor' => 'A69F88', 'bgColor' => 'A69F88');
        $styleCell = array(
            'valign' => 'center',
            'color'  => '#ffffff',
            'name'   => 'Calibri',
            'size'   => 10,
            'bold'   => true
        );
        $phpWord->addParagraphStyle(
            'titleTable',
            array(
                'align'      => 'center',
                'spaceAfter' => 0
            )
        );
        //-----------------INICIO  OPCIONES DE ACOMODACION

        foreach ($all_categories as $key_category => $category_id) {

            $_category = TypeClass::where('id', QuoteCategory::find($category_id)->type_class_id)
                ->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->where('language_id', $language_id);
                    }
                ])->first();

            //TEXTO ACOMODACION
            $section->addText(
                htmlspecialchars($trad->titleOptionsAcom) . ' - ' . $_category->translations[0]->value,
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);
            //TABLA DE ACOMODACION
            $phpWord->addParagraphStyle(
                'cellDefault',
                array(
                    'align'      => 'center',
                    'spaceAfter' => 0
                )
            );
            $phpWord->addTableStyle('tarifas', $styleTable, $styleFirstRow);
            $table = $section->addTable('tarifas');
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300, $styleCell)->addText(
                htmlspecialchars($trad->thDestiny),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(2800, $styleCell)->addText(
                htmlspecialchars($trad->thHotel),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(2000, $styleCell)->addText(
                htmlspecialchars($trad->thType),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(3000, $styleCell)->addText(
                htmlspecialchars($trad->thWeb),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );

            $listHotels = [];
            $quote_services = $all_quote_services[$category_id];
            foreach ($quote_services as $key => $quote_service) {
                if ($quote_service["type"] == 'hotel') {
                    $total_accommodation = (int)$quote_service["single"] + (int)$quote_service["double"] + (int)$quote_service["triple"] + (int)$quote_service["double_child"] + (int)$quote_service["triple_child"];
                    if ($total_accommodation > 0) {
                        $quote_services[$key]['key'] = $quote_service["hotel"]["id"] . '_' . $quote_service["nights"] . '_' . convertDate($quote_service["date_in"], '/', '-', 1);
                        array_push($listHotels, $quote_services[$key]);
                    }
                }
            }

            $hotelUnique = $this->groupedArray($listHotels, 'key');

            for ($i = 0; $i < count($hotelUnique); $i++) {
                $tiphab = '';
                $rowRooms = [];
                for ($h = 0; $h < count($hotelUnique[$i]); $h++) {

                    if (count($hotelUnique[$i][$h]['service_rooms']) > 0) {

                        for ($i_h = 0; $i_h < count($hotelUnique[$i][$h]['service_rooms']); $i_h++) {

                            if (($hotelUnique[$i][$h]['service_rooms'][$i_h]['rate_plan_room']['room']['room_type']['occupation'] == 1
                                    && $hotelUnique[$i][$h]['single'] > 0) ||
                                ($hotelUnique[$i][$h]['service_rooms'][$i_h]['rate_plan_room']['room']['room_type']['occupation'] == 2
                                    && $hotelUnique[$i][$h]['double'] > 0) ||
                                ($hotelUnique[$i][$h]['service_rooms'][$i_h]['rate_plan_room']['room']['room_type']['occupation'] == 3
                                    && $hotelUnique[$i][$h]['triple'] > 0)
                            ) {
                                $_tiphab = $hotelUnique[$i][$h]['service_rooms'][$i_h]['rate_plan_room']['room']['translations'][0]['value'];
                                $tiphab = htmlspecialchars(trim($_tiphab) . '');
                                $rowRooms[$tiphab] = $tiphab;
                            }
                        }
                    }
                }

                $tiphab = implode("<w:br />", $rowRooms);

                $nameHotel = $this->htmlDecode($hotelUnique[$i][0]['hotel']['name']);
                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1300)->addText(
                    htmlspecialchars(ucwords(strtolower(trim($hotelUnique[$i][0]['hotel']['city']['translations'][0]['value'])))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(2800)->addText(
                    htmlspecialchars(ucwords(strtolower(trim($nameHotel)))),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(2000)->addText(
                    $tiphab,
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $_web = ($hotelUnique[$i][0]['hotel']['web_site']) ? htmlspecialchars(trim($hotelUnique[$i][0]['hotel']['web_site'])) : '-';
                $table->addCell(3000)->addLink(
                    "https://" . $_web,
                    $_web, // fixed
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
            }

            //-----------------FIN OPCIONES DE ACOMODACION
            $section->addTextBreak(1, 'space1');
        }

        $quote_services = $quote_services_prev;

        //-----------------INICIO INCLUYE
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->titleInclude),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        //-----------------INICIO TITULO ACOMODACION
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        $section->addText(
            htmlspecialchars($trad->acomodation),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        //------------------FIN TITULO ACOMODACION

        $cantNigths = [];
        $nn = 0;
        $listHotels = $this->super_unique($listHotels, 'key');
        for ($i = 0; $i < count($listHotels); $i++) {
            if ($i == 0) {
                $cantNigths[$nn] = $listHotels[$i];
                $cantNigths[$nn]['CANT'] = ((int)$cantNigths[$nn]['nights']);
            } else {
                if (trim($listHotels[$i]['hotel']['city']['translations'][0]['value']) == trim($cantNigths[$nn]['hotel']['city']['translations'][0]['value'])) {
                    $cantNigths[$nn]['CANT'] += ((int)$listHotels[$i]['nights']);
                } else {
                    $nn++;
                    $cantNigths[$nn] = $listHotels[$i];
                    $cantNigths[$nn]['CANT'] = ((int)$cantNigths[$nn]['nights']);
                }
            }
        }

        for ($i = 0; $i < count($cantNigths); $i++) {
            $cantDay = $cantNigths[$i]['CANT'];
            if ($cantDay == 1) {
                $textday = $trad->nightStay;
            } else {
                $textday = $trad->nightStayp;
            }
            $ciudad = strtolower(trim($cantNigths[$i]['hotel']['city']['translations'][0]['value']));
            $section->addListItem(
                htmlspecialchars($cantDay . ' ' . $textday . ' ' . ucwords($ciudad)),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        }
        //FIN LISTA DE NOCHES POR CIUDAD

        $section->addTextBreak(1, 'space1');

        //-----------------INICIO TITULO TRASLADOS Y TOURS
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        $section->addText(
            htmlspecialchars($trad->titleTransfers),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );

        $packageServiceCategoryId = 2;

        foreach ($quote_services as $quote_service) {
            if ($quote_service["type"] !== "service" || $quote_service['optional'] == 1) {
                continue;
            }

            $inclusions = collect($quote_service['service']['inclusions'])->groupBy('day');

            $section->addListItem(
                htmlspecialchars($quote_service["service"]["service_translations"][0]["name"]),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );

            if ($inclusions->isEmpty() || $quote_service['service']['service_sub_category']['service_category_id'] !== $packageServiceCategoryId) {
                continue;
            }

            $inclusionsTable = $section->addTable('tarifas');
            $inclusionsTable->addRow(340, array('exactHeight' => true));

            $days = $inclusions->keys();

            $highestNumberOfInclusionsForADay = $inclusions->map(function ($inclusions) {
                return count($inclusions);
            })->sort()->last();

            $cellWidth = intval(9000 / count($days));

            foreach ($days as $day) {
                $inclusionsTable->addCell($cellWidth, $styleCell)->addText(
                    htmlspecialchars("{$trad->day} $day"),
                    ['name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true],
                    'titleTable'
                );
            }

            foreach (range(0, $highestNumberOfInclusionsForADay - 1) as $index) {
                $inclusionsTable->addRow(200, ['exactHeight' => false]);

                foreach ($days as $day) {
                    if (isset($inclusions[$day][$index])) {
                        $inclusionsTable->addCell($cellWidth)->addText(
                            htmlspecialchars($inclusions[$day][$index]['inclusions']['translations'][0]['value']),
                            ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'],
                            'titleTable'
                        );
                    } else {
                        $inclusionsTable->addCell($cellWidth)->addText('');
                    }
                }
            }

            $section->addTextBreak(1, 'space1');
        }
        //------------------FIN TITULO TRASLADOS Y TOURS
        //------------------FIN INCLUYE
        $predefinedMultilevel = array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_SQUARE_FILLED); //temporalmente para probar
        $section->addTextBreak(1, 'space1');

        //-----------------INICIO  TITULO NO INCLUYE
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align'         => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->titleNotInclude),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        $phpWord->addFontStyle(
            'StyleSquare',
            array('name' => 'Calibri', 'color' => '5a5a58', 'size' => 10, 'bold' => false)
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line8),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        //-----------------FIN  TITULO NO INCLUYE
        $section->addTextBreak(1, 'space1'); //Espacio

        /*
         * Todo TERMINOS Y CONDICIONES
         */

        //TTITULO
        $section->addText(htmlspecialchars($trad->terminos_y_condiciones_title), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => true,
            'wrappingStyle' => 'infront'
        ), 'title');
        $section->addLine($linestyle);
        //ITEMS
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->terminos_y_condiciones_punto_8),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        //FIN TERMINOS Y CONDICIONES

        $section->addTextBreak(1, 'space1'); //Espacio

        /*
        * Todo CONDICIONES TERCERA PERSONA / NIÑO
        */

        //TTITULO
        $section->addText(htmlspecialchars($trad->title_condiciones_tercera_personas_nino), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => true,
            'wrappingStyle' => 'infront'
        ), 'title');
        $section->addLine($linestyle);
        //ITEMS
        $section->addListItem(
            htmlspecialchars($trad->condiciones_tercera_personas_nino_text_1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->condiciones_tercera_personas_nino_text_2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->condiciones_tercera_personas_nino_text_3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addTextBreak(1, 'space1'); //Espacio

        /*
         * Todo CONDICIONES DE CANCELACIÓN
         */

        //TITULO
        $section->addText(
            htmlspecialchars($trad->textCancelation_title),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle); // LINEA
        $phpWord->addFontStyle(
            'textBold',
            array('name' => 'Calibri', 'color' => '#5a5a58', 'size' => 10, 'bold' => true)
        );
        $phpWord->addFontStyle(
            'textNormal',
            array('name' => 'Calibri', 'color' => '5a5a58', 'size' => 10, 'bold' => false)
        );
        $phpWord->addParagraphStyle('P-Style', array('spaceAfter' => 5, 'marginLeft' => 360));
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
        //TEXTO
        $section->addText(
            htmlspecialchars($trad->textCancelation_text_1),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        // INICIO DE TABLA CONDICIONES DE CANCELACION
        $section->addTextBreak(1, 'space1');
        $table = $section->addTable('tarifas');
        //ENCABEZADO
        $table->addRow(340, array('exactHeight' => true));
        $table->addCell(4550, $styleCell)->addText(
            htmlspecialchars($trad->textCancelation_tbl_title1),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTable'
        );
        $table->addCell(4550, $styleCell)->addText(
            htmlspecialchars($trad->textCancelation_tbl_title2),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTable'
        );
        $table->addCell(4550, $styleCell)->addText(
            htmlspecialchars($trad->textCancelation_tbl_title3),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTable'
        );
        // FILAS 1
        $table->addRow(340, array('exactHeight' => true));
        $table->addCell(1300)->addText(
            htmlspecialchars($trad->textCancelation_tbl_line1_c1),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(2800)->addText(
            htmlspecialchars($trad->textCancelation_tbl_line1_c2),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(2800)->addText(
            htmlspecialchars($trad->textCancelation_tbl_line1_c3),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        // FILAS 2
        $table->addRow(340, array('exactHeight' => true));
        $table->addCell(1300)->addText(
            htmlspecialchars($trad->textCancelation_tbl_line2_c1),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(2800)->addText(
            htmlspecialchars($trad->textCancelation_tbl_line2_c2),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(2800)->addText(
            htmlspecialchars($trad->textCancelation_tbl_line1_c3),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );


        //Text Cancellation

        $section->addTextBreak(1, 'space1');
        $section->addText(
            htmlspecialchars($trad->textCancelation_list_0),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        // $section->addListItem(htmlspecialchars($trad->condiciones_tercera_personas_nino_text_4), 0, 'StyleSquare',
        // $predefinedMultilevel, 'P-Styleguiado');

        $section->addTextBreak(1, 'space1'); //Espacio

        /*
        //TEXTO
        $section->addText(htmlspecialchars($trad->textCancelation_text_2)
            , array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant');

        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textCancelation_list_7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addTextBreak(1, 'space1'); //Espacio

        $section->addText(
            htmlspecialchars($trad->titleinfoImportant),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        $section->addText(
            htmlspecialchars($trad->textinfoImportant),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant'
        );
        //FIN INFORMACIÓN SOBRE LA ENTRADA A PERÚ
        $section->addTextBreak(1, 'space1'); //Espacio
        /*
         * Todo RECOMENDACIONES GENERALES
         */
        $section->addText(
            htmlspecialchars($trad->titleGeneralImportant),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        // TEXTO IMPORTENTE
        $section->addText(
            htmlspecialchars($trad->textGeneralImportant),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        $textRun = $section->addTextRun(array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
        $textRun->addText(htmlspecialchars($trad->general_recommendations_text_2));
        $textRun->addLink($trad->general_recommendations_link, htmlspecialchars($trad->general_recommendations_here), array('bold' => true, 'color' => '0000FF'));

        $section->addTextBreak(1, 'space1');

        /* recomendacion para viajar cuidando el medio ambiente */
        $section->addText(
            htmlspecialchars($trad->title_recommendations_ambient),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list8),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list9),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->recommendations_ambient_list10),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addTextBreak(1, 'space1');
        //-----------------INICIO INFORMACI�N IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A MACHU PICCHU
        //TITULO IMPORTENTE
        $section->addText(
            htmlspecialchars($trad->titleImportant),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        $section->addText(
            htmlspecialchars($trad->textImportant),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant'
        );
        //ITEMS
        $section->addListItem(
            htmlspecialchars($trad->textImportant_data1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addListItem(
            htmlspecialchars($trad->textImportant_data3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addListItem(
            htmlspecialchars($trad->textImportant_data5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addText(
            htmlspecialchars($trad->textImportant_2),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant'
        );
        //ITEMS
        $section->addListItem(
            htmlspecialchars($trad->textImportant_data6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textImportant_data7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textImportant_data8),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textImportant_data9),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        //-----------------FIN INFORMACI�N IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A MACHU PICCHU

        $section->addTextBreak(1, 'space1');

        //-----------------INICIO IMAGEN MOCHILA
        $section->addImage(
            public_path() . '/images/word/mochila_new.png',
            array(
                'width'            => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.68),
                'height'           => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(2.28),
                'positioning'      => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
                'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                'posVerticalRel'   => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,
            )
        );

        $table = $section->addTable('tarifas');
        $table->addRow(300, array('exactHeight' => true));
        $table->addCell(1900, $styleCell)->addText(
            htmlspecialchars(''),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTable'
        );
        $table->addCell(1300, $styleCell)->addText(
            htmlspecialchars($trad->thPeso),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTable'
        );
        $table->addCell(3500, $styleCell)->addText(
            htmlspecialchars($trad->size),
            array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
            'titleTable'
        );

        $table->addRow(300, array('exactHeight' => true));
        $table->addCell(1900)->addText(
            htmlspecialchars('1 ' . $trad->tdbolso),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(1300)->addText(
            htmlspecialchars('8kg/17.6 lb'),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(3500)->addText(
            htmlspecialchars("62 inches/157cm"),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );

        $section->addTextBreak(1, 'space1');

        /*
         * Todo INICIO INFORMACION IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A MACHU PICCHU
         */
        //TITULO IMPORTENTE
        /*
        $section->addText(htmlspecialchars($trad->titleImportant)
            ,
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title');
        $section->addLine($linestyle);
        //TEXTO IMPORTENTE
        $section->addText(htmlspecialchars($trad->textImportant)
            , array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant');
        $section->addText(htmlspecialchars($trad->textImportant_2)
            , array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant');
        $section->addText(htmlspecialchars($trad->textImportant_3)
            , array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant');
        //-----------------FIN INFORMACION IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A MACHU PICCHU

        $section->addTextBreak(1, 'space1');

        //NOTA IMPORTANTE MACHU PICCHU
        $phpWord->addParagraphStyle('textImportant', array(
                'align' => 'both',
                'spaceAfter' => 170,
                'wrappingStyle' => 'infront',
            )
        );
        $table->addCell(1300)->addText(
            htmlspecialchars('5kg/11lb'),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );
        $table->addCell(3500)->addText(
            htmlspecialchars("62 inches/157cm"),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            'titleTable'
        );

        $section->addTextBreak(1, 'space1');

        /*
         * TODO MASI: UNA NUEVA PLATAFORMA DE ASISTENCIA PERSONALIZADA
         */

        //TITULO
        $section->addText(
            htmlspecialchars($trad->masi_title),
            array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => true,
                'wrappingStyle' => 'infront'
            ),
            'title'
        );
        $section->addLine($linestyle);
        //PARRAFO 1
        $section->addText(
            htmlspecialchars($trad->masi_parrafo_1),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        $section->addListItem(
            htmlspecialchars($trad->masi_texto_1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->masi_texto_2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->masi_texto_3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->masi_texto_4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->masi_texto_5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->masi_texto_6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addTextBreak(1, 'space1');
        //PARRAFO 2
        $section->addText(
            htmlspecialchars("**********************************************"),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant'
        );
        $section->addTextBreak(1, 'space1');

        $section->addText(htmlspecialchars($trad->masi_title_numbers), array(
            'name'          => 'Calibri',
            'size'          => 10,
            'color'         => '5a5a58',
            'bold'          => true,
            'wrappingStyle' => 'infront'
        ), 'title');

        if ($market_id == 6) { // 1️⃣ Estados Unidos y Canadá
            $section->addListItem(
                htmlspecialchars($trad->masi_numbers_1),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->masi_numbers_2),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        } elseif (in_array($market_id, [8, 7, 15])) { // 2️⃣ Europa y Asia Pacífico
            $section->addListItem(
                htmlspecialchars($trad->masi_numbers_3),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->masi_numbers_4),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        } else { // 3️⃣ LATAM, Italia, España y Portugal (Otros mercados)
            $section->addListItem(
                htmlspecialchars($trad->masi_numbers_5),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->masi_numbers_6),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        }

        //------------------FIN MASI: UNA NUEVA PLATAFORMA DE ASISTENCIA PERSONALIZADA
        $section->addTextBreak(1, 'space1');


        /*
         * Todo INICIO INFORMACION BANCARIA
         */

        //TITULO
        $section->addText(
            htmlspecialchars($trad->titleInformationBank),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        $section->addText(
            htmlspecialchars($trad->InformationBank_bold0),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        //BANCO

        $indentationStyle0 = array('indentation' => array('left' => 240));
        $noSpacing = array('spaceAfter' => 0, 'spaceBefore' => 0);
        $section->addText(
            htmlspecialchars($trad->InformationBank_bold1) . ' ' . htmlspecialchars($trad->InformationBank_data1),
            array('bold' => false, 'name' => 'Calibri', 'size' => 10, 'color' => '5a5a58',),
            array_merge($indentationStyle0, $noSpacing) // Aplicar sangría de 720 twips (~36 px)
        );
        $section->addText(
            htmlspecialchars($trad->InformationBank_bold3) . ' ' . htmlspecialchars($trad->InformationBank_data3),
            array('bold' => false, 'name' => 'Calibri', 'size' => 10, 'color' => '5a5a58',),
            array_merge($indentationStyle0, $noSpacing) // Aplicar sangría de 720 twips (~36 px)
        );
        $section->addText(
            htmlspecialchars($trad->InformationBank_bold4) . ' ' . htmlspecialchars($trad->InformationBank_data4),
            array('bold' => false, 'name' => 'Calibri', 'size' => 10, 'color' => '5a5a58',),
            array_merge($indentationStyle0, $noSpacing) // Aplicar sangría de 720 twips (~36 px)
        );
        $section->addText(
            htmlspecialchars($trad->InformationBank_bold5) . ' ' . htmlspecialchars($trad->InformationBank_data5),
            array('bold' => false, 'name' => 'Calibri', 'size' => 10, 'color' => '5a5a58',),
            array_merge($indentationStyle0, $noSpacing) // Aplicar sangría de 720 twips (~36 px)
        );

        $indentationStyle1 = array('indentation' => array('left' => 720)); // 240 twips (~12 px)

        $section->addText(
            htmlspecialchars($trad->InformationBank_text),
            array('bold' => false, 'name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
            array_merge($indentationStyle0, $noSpacing)
        );
        $section->addListItem(
            htmlspecialchars($trad->InformationBank_text_line1),
            0,
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), // Color negro en las viñetas
            $predefinedMultilevel,
            array_merge(
                array(
                    'spaceAfter' => 10, // Reducir el espacio después de la viñeta
                    'size'       => 11
                ),
                $indentationStyle1
            )
        );

        $section->addListItem(
            htmlspecialchars($trad->InformationBank_text_line2),
            0,
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), // Color negro
            $predefinedMultilevel,
            array_merge(
                array(
                    'spaceAfter' => 10, // Reducir el espacio después de la viñeta
                    'size'       => 11
                ),
                $indentationStyle1
            )
        );

        $section->addListItem(
            htmlspecialchars($trad->InformationBank_text_line3),
            0,
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), // Color negro
            $predefinedMultilevel,
            array_merge(
                array(
                    'spaceAfter' => 10, // Reducir el espacio después de la viñeta
                ),
                $indentationStyle1
            )
        );

        $section->addListItem(
            htmlspecialchars($trad->InformationBank_text_line3),
            0,
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), // Color negro
            $predefinedMultilevel,
            array_merge(
                array(
                    'spaceAfter' => 10, // Reducir el espacio después de la viñeta
                ),
                $indentationStyle1
            )
        );
        //-----------------FIN INFORMACION BANCARIA

        $section->addTextBreak(1, 'space1'); //Espacio


        $section->addText(
            htmlspecialchars($trad->InformationCardCredit_title),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addText(
            htmlspecialchars($trad->InformationCardCredit_item1),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );
        $section->addText(
            htmlspecialchars($trad->InformationCardCredit_item2),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );
        $section->addText(
            htmlspecialchars($trad->InformationCardCredit_item3),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        $section->addListItem(
            htmlspecialchars($trad->InformationCardCredit_list1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->InformationCardCredit_list2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->InformationCardCredit_list3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->InformationCardCredit_list4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );

        $section->addTextBreak(1, 'space1'); //Espacio
        $section->addText(
            htmlspecialchars($trad->InformationCardCredit_important),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );


        /*
         * Todo APORTA AL CUIDADO DEL PLANETA CON LIMATOURS
         */

        //        //TITULO
        //        $section->addText(htmlspecialchars($trad->iniciativa_verde_title),
        //            array(
        //                'name' => 'Calibri',
        //                'size' => 10,
        //                'color' => '5a5a58',
        //                'bold' => true,
        //                'wrappingStyle' => 'infront'
        //            ),
        //            'title');
        //        $section->addLine($linestyle);
        //        //PARRAFO 1
        //        $section->addText(htmlspecialchars($trad->iniciativa_verde_texto_1)
        //            , array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
        //            'textImportant');
        //        $section->addTextBreak(1, 'space1');
        //        $section->addText(htmlspecialchars($trad->iniciativa_verde_texto_2)
        //            , array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
        //            'textImportant');
        //        $section->addTextBreak(1, 'space1');
        //        $section->addText(htmlspecialchars($trad->iniciativa_verde_texto_3)
        //            , array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
        //            'textImportant');
        //        $section->addTextBreak(1, 'space1');
        //        $section->addText(htmlspecialchars($trad->iniciativa_verde_texto_4)
        //            , array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
        //            'textImportant');
        //        $section->addListItem(htmlspecialchars($trad->iniciativa_verde_punto_1), 0, 'StyleSquare',
        //            $predefinedMultilevel, 'P-Styleguiado');
        //        $section->addListItem(htmlspecialchars($trad->iniciativa_verde_punto_2), 0, 'StyleSquare',
        //            $predefinedMultilevel, 'P-Styleguiado');
        //        $section->addListItem(htmlspecialchars($trad->iniciativa_verde_punto_3), 0, 'StyleSquare',
        //            $predefinedMultilevel, 'P-Styleguiado');
        //        //FIN APORTA AL CUIDADO DEL PLANETA CON LIMATOURS
        //
        //        $section->addTextBreak(1, 'space1');//Espacio

        /*
         * Todo DISCLAIMER
         */
        $section->addText(
            htmlspecialchars($trad->disclaimer_title),
            array(
                'name'          => 'Calibri',
                'size'          => 10,
                'color'         => '5a5a58',
                'bold'          => true,
                'wrappingStyle' => 'infront'
            ),
            'title'
        );
        $section->addLine($linestyle);
        //PARRAFO 1
        $section->addText(
            htmlspecialchars($trad->disclaimer_text),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        try {
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save(storage_path('Itinerary.docx'));
        } catch (\Exception $e) {
            return $this->throwError($e);
        }

        return response()->download(storage_path('Itinerary.docx'));
    }

    private function getServiceFoodText(Collection $inclusions, $translations)
    {
        $foods = [];

        $hasBreakfast = $inclusions->filter(function ($inclusion) use ($translations) {
            return Str::contains(
                strtolower($inclusion['inclusions']['translations'][0]['value']),
                strtolower($translations->breakfast)
            );
        })->isNotEmpty();

        if ($hasBreakfast) {
            $foods[] = [
                'order' => 1,
                'value' => $translations->breakfast
            ];
        }

        $hasLunch = $inclusions->filter(function ($inclusion) use ($translations) {
            return Str::contains(
                strtolower($inclusion['inclusions']['translations'][0]['value']),
                strtolower($translations->lunch)
            );
        })->isNotEmpty();

        if ($hasLunch) {
            $foods[] = [
                'order' => 2,
                'value' => $translations->lunch
            ];
        }

        $hasDinner = $inclusions->filter(function ($inclusion) use ($translations) {
            return Str::contains(
                strtolower($inclusion['inclusions']['translations'][0]['value']),
                strtolower($translations->dinner)
            );
        })->isNotEmpty();

        if ($hasDinner) {
            $foods[] = [
                'order' => 3,
                'value' => $translations->dinner
            ];
        }

        return $foods;
    }

    public static function orderMultiDimensionalArray(
        $toOrderArray,
        $field,
        $inverse = false
    ) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key] = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }

        return $returnArray;
    }

    public static function groupedArray(
        $array,
        $value
    ) {
        $groupedArray = array();
        $paisArray = array();
        foreach ($array as $key => $valuesAry) {
            $pais = $valuesAry[$value];
            if (!in_array($pais, $paisArray)) {
                //si no existe, lo agrego
                $paisArray[] = $pais;
            }
            $paisIndex = array_search($pais, $paisArray);
            $groupedArray[$paisIndex][] = $valuesAry;
        }

        return $groupedArray;
    }

    public static function htmlDecode(
        $var
    ) {
        $text = html_entity_decode(trim($var), ENT_QUOTES, "UTF-8");
        $text = str_replace("\\", '', $text);

        return $text;
    }

    public static function array_flatten(
        $array
    ) {
        $results = [];
        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }
            $results = array_merge($results, $values);
        }

        return $results;
    }

    public static function convertDate(
        $var,
        $delimiter_from,
        $delimiter_to,
        $orientation
    ) {
        $explode = explode($delimiter_from, $var);
        $response = ($orientation)
            ? $explode[2] . $delimiter_to . $explode[1] . $delimiter_to . $explode[0]
            : $explode[0] . $delimiter_to . $explode[1] . $delimiter_to . $explode[2];

        return $response;
    }

    // Parámetros: string $data:  Fecha en formato dd/mm/aaaa o timestamp
    // int    $tipus: Tipo de fecha (0-timestamp, 1-dd/mm/aaaa)//// Retorna:string  Fecha en formato largo (x, dd mm de yyyy)
    public static function date_text(
        $data,
        $tipo = 1,
        $lang
    ) {
        $dataLang = File::get(database_path() . "/data/translations/Itinerary.json");
        $trad = json_decode($dataLang, true);
        $trad = $trad[$lang];
        if ($data != '') {
            $setmana = $trad['semana'];
            $mes = $trad['mes'];
            preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4})/', $data, $data);
            $data = mktime(0, 0, 0, $data[2], $data[1], $data[3]);
            if ($tipo == 1) {
                if (strtolower($lang) == 'es' or strtolower($lang) == 'pt') {
                    $dateString = $setmana[date('w', $data)]['name'] . ' ' . date(
                        'd',
                        $data
                    ) . ' ' . $trad['de'] . ' ' . $mes[date(
                        'm',
                        $data
                    ) - 1]['name'] . '' . $trad['del'] . ' ' . date('Y', $data);
                } else {
                    $dateString = $setmana[date('w', $data)]['name'] . ' ' . $mes[date(
                        'm',
                        $data
                    ) - 1]['name'] . ' ' . date(
                        'd',
                        $data
                    ) . ',' . ' ' . date('Y', $data);
                }
            } elseif ($tipo == 2) {
                $dateString = date('d', $data) . ' ' . ucwords(substr(
                    $mes[date('m', $data) - 1]['name'],
                    0,
                    3
                )) . ' ' . date(
                    'Y',
                    $data
                );
            } elseif ($tipo == 3) {
                $dateString = ucwords(substr($setmana[date('w', $data)]['name'], 0, 3)) . ' ' . date(
                    'd',
                    $data
                ) . ' ' . ucwords(substr($mes[date('m', $data) - 1]['name'], 0, 3));
            }

            return $dateString;
        } else {
            return 0;
        }
    }

    public static function super_unique(
        $array,
        $key
    ) {

        $temp_array = array();

        foreach ($array as &$v) {

            if (!isset($temp_array[$v[$key]])) {
                $temp_array[$v[$key]] = &$v;
            }
        }

        $array = array_values($temp_array);

        return $array;
    }

    public function copy(Request $request)
    {

        $quote_category_id_from = $request->input("quote_category_id_from");
        $quote_category_id_to = $request->input("quote_category_id_to");

        $quote_category_from = QuoteCategory::find($quote_category_id_from);
        $quote_category_to = QuoteCategory::find($quote_category_id_to);
        $type_class_id_from = $quote_category_from->type_class_id;
        $type_class_id_to = $quote_category_to->type_class_id;

        $services = QuoteService::where('quote_category_id', $quote_category_id_from);

        if ($services->count() == 0) {
            return Response::json(['success' => false, 'type' => '0']);
        } else {

            $services = $services
                ->orderBy('date_in', 'asc')
                ->orderBy('order', 'asc')->get();

            $services_ids = QuoteService::where(
                'quote_category_id',
                $quote_category_id_to
            )->pluck('id');
            $created_at = date("Y-m-d H:i:s");


            DB::transaction(function () use ($services, $quote_category_id_to, $services_ids, $created_at) {

                DB::table('quote_service_rooms')
                    ->whereIn('quote_service_id', $services_ids)
                    ->delete();
                DB::table('quote_service_rates')
                    ->whereIn('quote_service_id', $services_ids)
                    ->delete();
                DB::table('quote_service_amounts')
                    ->whereIn('quote_service_id', $services_ids)
                    ->delete();

                DB::table('quote_services')
                    ->where('quote_category_id', $quote_category_id_to)
                    ->delete();

                $array_quote_services_id_extensions = [];

                //Todo Buscamos las extensiones
                foreach ($services as $service) {
                    //Todo Si la cotizacion biene del metodo guardar como y la cotizacion ha tenido un file desbloqueo los servicios

                    if ($service->extension_id !== null && $service->parent_service_id == null) {
                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'type'              => $service["type"],
                            "quote_category_id" => $quote_category_id_to,
                            "object_id"         => $service["object_id"],
                            "order"             => $service["order"],
                            "date_in"           => convertDate($service["date_in"], '/', '-', 1),
                            "date_out"          => convertDate($service["date_out"], '/', '-', 1),
                            "nights"            => $service["nights"],
                            "adult"             => $service["adult"],
                            "child"             => $service["child"],
                            "infant"            => $service["infant"],
                            "single"            => $service["single"],
                            "double"            => $service["double"],
                            "triple"            => $service["triple"],
                            'optional'          => (int)$service["optional"],
                            "triple_active"     => $service["triple_active"],
                            "on_request"        => $service["on_request"],
                            'extension_id'      => $service["extension_id"],
                            'parent_service_id' => null,
                            'new_extension_id'  => $service["new_extension_id"],
                            'code_flight'       => $service["code_flight"],
                            'origin'            => $service["origin"],
                            'destiny'           => $service["destiny"],
                            'date_flight'       => $service["date_flight"],
                            'created_at'        => $created_at,
                            'updated_at'        => $created_at
                        ]);
                        foreach ($array_quote_services_id_extensions as $key => $quote_services_id_extension) {
                            if ($service->parent_service_id == $quote_services_id_extension["quote_service_id_old"]) {
                                $array_quote_services_id_extensions[$key]['quote_service_id_new'] = $new_service_id;
                            }
                        }

                        $array_quote_services_id_extensions[] = [
                            "quote_service_id_old" => $service->id,
                            "quote_service_id_new" => $new_service_id
                        ];
                    }
                }

                //Todo Recorro los servicios
                foreach ($services as $service) {
                    if ($service->extension_id == null && $service->parent_service_id !== null) {
                        $new_parent_service_id = null;
                        foreach ($array_quote_services_id_extensions as $quote_services_id_extension) {
                            if ($service->parent_service_id == $quote_services_id_extension["quote_service_id_old"]) {
                                $new_parent_service_id = $quote_services_id_extension["quote_service_id_new"];
                            }
                        }

                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'type'              => $service["type"],
                            "quote_category_id" => $quote_category_id_to,
                            "object_id"         => $service["object_id"],
                            "order"             => $service["order"],
                            "date_in"           => convertDate($service["date_in"], '/', '-', 1),
                            "date_out"          => convertDate($service["date_out"], '/', '-', 1),
                            "nights"            => $service["nights"],
                            "adult"             => $service["adult"],
                            "child"             => $service["child"],
                            "infant"            => $service["infant"],
                            "single"            => $service["single"],
                            "double"            => $service["double"],
                            "triple"            => $service["triple"],
                            'optional'          => (int)$service["optional"],
                            "triple_active"     => $service["triple_active"],
                            "on_request"        => $service["on_request"],
                            'extension_id'      => $service["extension_id"],
                            'parent_service_id' => $new_parent_service_id,
                            'new_extension_id'  => $service["new_extension_id"],
                            'code_flight'       => $service["code_flight"],
                            'origin'            => $service["origin"],
                            'destiny'           => $service["destiny"],
                            'date_flight'       => $service["date_flight"],
                            'created_at'        => $created_at,
                            'updated_at'        => $created_at
                        ]);
                    }
                    //Todo Verifico si el servicio no es una extension
                    if ($service->extension_id == null && $service->parent_service_id == null) {
                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'type'              => $service["type"],
                            "quote_category_id" => $quote_category_id_to,
                            "object_id"         => $service["object_id"],
                            "order"             => $service["order"],
                            "date_in"           => convertDate($service["date_in"], '/', '-', 1),
                            "date_out"          => convertDate($service["date_out"], '/', '-', 1),
                            "nights"            => $service["nights"],
                            "adult"             => $service["adult"],
                            "child"             => $service["child"],
                            "infant"            => $service["infant"],
                            "single"            => $service["single"],
                            "double"            => $service["double"],
                            "triple"            => $service["triple"],
                            'optional'          => (int)$service["optional"],
                            "triple_active"     => $service["triple_active"],
                            "on_request"        => $service["on_request"],
                            'extension_id'      => null,
                            'parent_service_id' => null,
                            'new_extension_id'  => $service["new_extension_id"],
                            'code_flight'       => $service["code_flight"],
                            'origin'            => $service["origin"],
                            'destiny'           => $service["destiny"],
                            'date_flight'       => $service["date_flight"],
                            'created_at'        => $created_at,
                            'updated_at'        => $created_at
                        ]);
                    }

                    if ($service->extension_id !== null && $service->parent_service_id == null) {
                        foreach ($array_quote_services_id_extensions as $quote_services_id_extension) {
                            if ($service->id == $quote_services_id_extension["quote_service_id_old"]) {
                                $new_service_id = $quote_services_id_extension["quote_service_id_new"];
                            }
                        }
                    }

                    // RATES
                    $service_rates_for_copy = DB::table('quote_service_rates')
                        ->where('quote_service_id', $service['id'])
                        ->get();

                    foreach ($service_rates_for_copy as $s_copy) {
                        DB::table('quote_service_rates')->insert([
                            'quote_service_id' => $new_service_id,
                            'service_rate_id'  => $s_copy->service_rate_id
                        ]);
                    }
                    // ROOMS
                    $service_rooms_for_copy = DB::table('quote_service_rooms')
                        ->where('quote_service_id', $service['id'])
                        ->get();

                    foreach ($service_rooms_for_copy as $r_copy) {
                        DB::table('quote_service_rooms')->insert([
                            'quote_service_id'  => $new_service_id,
                            'rate_plan_room_id' => $r_copy->rate_plan_room_id
                        ]);
                    }
                    // AMOUNTS
                    //                    $service_amounts_for_copy = DB::table('quote_service_amounts')
                    //                        ->where('quote_service_id', $service['id'])
                    //                        ->get();
                    //
                    //                    foreach ($service_amounts_for_copy as $a_copy) {
                    //                        DB::table('quote_service_amounts')->insert([
                    //                            'quote_service_id' => $new_service_id,
                    //                            'amount' => $a_copy->amount,
                    //                            'error_in_nights' => $a_copy->error_in_nights
                    //                        ]);
                    //                    }

                }
            });

            $this->store_history_logs($quote_category_from->quote_id, [
                [
                    "type"          => "store",
                    "slug"          => "copy_category",
                    "previous_data" => $type_class_id_from,
                    "current_data"  => $type_class_id_to,
                    "description"   => "Copió categoría"
                ]
            ]);

            return Response::json(['success' => true]);
        }
    }


    /**
     * Verifica si en la tabla QuoteServiceAmount existen precios de los servicios para que se pueda cotizar
     * @param $quote_id
     * @return JsonResponse
     */
    public function checkQuoteServicesAmounts($quote_id)
    {
        $response = false;
        $menssage = trans('quote.validation.quote_service_amount_not_found');
        $quote_category = QuoteCategory::where('quote_id', $quote_id)->withCount('services')->get();
        if ($quote_category->count() > 0) {
            $quote_services_id = QuoteService::where('quote_category_id', $quote_category[0]['id'])->get(['id']);
            $quote_service_amounts_count = QuoteServiceAmount::whereIn('quote_service_id', $quote_services_id->pluck('id'))->count();
            if ($quote_service_amounts_count > 0) {
                $response = true;
                $menssage = "Success";
            }
        }

        return Response::json(['success' => $response, 'message' => $menssage]);
    }

    public function imageCreate(Request $request)
    {

        $lang = strtolower($request->input('lang')); // retornamos el lenguaje (es = español o en = ingles)

        //retornamos los files en base al lenguaje dado y convertimos un string  codificado en Json
        $dataLang = File::get(resource_path() . "/lang/" . $lang . "/itinerary.json");
        $trad = json_decode($dataLang);
        //capturamos el nombre del cliente
        $refPax = $request->get('refPax');
        $text2 = mb_strtoupper(trim($refPax), $encoding = 'UTF-8');
        $src = '';
        $dst = '';
        $namePortada = $request->get('portadaName');
        $validador = $request->get('itinerary');
        $client_id = $request->get('clienteId');
        $num_file = $request->get('file');
        //Unimos 2 imagenes y un texto, la portada sin logo, el logo y el texto nombre del cliente
        if ($request->get('estado') == "1") {

            $portOrigin = false;

            $client_logo = Client::where('id', $client_id)->first()->logo;

            if (strtolower(substr($client_logo, -3)) === 'png') {

                $src = imagecreatefromjpeg('https://res.cloudinary.com/litodti/image/upload/aurora/portadas/cliente/' . 'cliente-' . $request->get('portada') . '.jpg');
                $dst = imagecreatefrompng($client_logo);
            } else {
                $src = imagecreatefromjpeg('https://res.cloudinary.com/litodti/image/upload/aurora/portadas/cliente/' . 'cliente-' . $request->get('portada') . '.jpg');
                $dst = imagecreatefromjpeg($client_logo);
            }

            list($ancho, $alto) = getimagesize($client_logo);

            $src = $this->getLogo($src, $dst, $ancho, $alto);
        } elseif ($request->get('estado') == "2") { // unimos la portada sin logo y un texto

            $src = imagecreatefromjpeg('https://res.cloudinary.com/litodti/image/upload/aurora/portadas/cliente/' . 'cliente-' . $request->get('portada') . '.jpg');
            $portOrigin = false;
        } else { // unimos la portada original y un texto

            $src = imagecreatefromjpeg('https://res.cloudinary.com/litodti/image/upload/aurora/portadas/' . $request->get('portada') . '.jpg');
            $portOrigin = true;
        }

        if (Auth::check()) {
            $user_type_id = Auth::user()->user_type_id;
            if ($user_type_id == 4) {
                $text2 = '';
            }
        }

        $fontsize = 35;
        $text = $text2;
        $top = 1520;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);


        $fontsize = 31;
        $text = $namePortada;
        $top = 1580;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);

        if ($validador == true) {
            $fontsize = 31;
            $text = 'File: ' . $num_file;
            $top = 1630;
            $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);
        }

        $images_test = md5(date('Y-m-d H:i:s.u'));

        imagepng($src, "images/portadas/create/" . $images_test . '.jpg', 0);
        imagedestroy($src);
        $portada_name = $images_test . '.jpg';


        return Response::json(['success' => true, "image" => "images/portadas/create/" . $images_test, 'portada' => $portada_name]);
    }

    public function imageCreatePackage(Request $request)
    {
        $lang = strtolower($request->input('lang')); // retornamos el lenguaje (es = español o en = ingles)
        //retornamos los files en base al lenguaje dado y convertimos un string  codificado en Json
        $dataLang = File::get(resource_path() . "/lang/" . $lang . "/itinerary.json");
        $trad = $this->toArray(json_decode($dataLang));
        //capturamos el nombre del cliente
        $title = $request->get('title');
        $title = mb_strtoupper(trim($title), 'UTF-8');
        $destinies = $request->get('destinies');
        $type_package = $request->get('type_package');
        $operations = $request->get('date_operations');
        $client_id = $request->get('clienteId');
        $portada = str_replace(["https:", "http:"], "", $request->get('portada'));
        $days = $request->get('days');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $with_client_logo = $request->get('estado') ?? $request->get('status') ?? 1;
        $code_user = strtolower(trim($request->get('code')));

        if (empty($portada)) {
            return Response::json([
                'success' => true,
                "image"   => "",
                'portada' => ""
            ]);
        }

        $date_operations = $trad["departure"] . ": ";
        $dates = explode(",", $operations);

        if (count($dates) == 7 || count($dates) == 0 || empty($operations)) {
            $date_operations .= $trad['daily'];
        } else {
            foreach ($dates as $key => $day) {
                $day = trim($day);
                $date_operations .= ($key == 0) ? '' : '-';

                $date_operations .= $trad["semana"][$day]["name"];
            }
        }

        //Unimos 2 imagenes y un texto, la portada sin logo, el logo y el texto nombre del cliente
        $portOrigin = true;
        $src = '';
        $dst = '';
        $client_logo = Client::where('id', $client_id)->first()->logo;

        if (strtolower(substr($client_logo, -3)) === 'png') {
            $dst = imagecreatefrompng($client_logo);
        } else {
            $dst = imagecreatefromjpeg($client_logo);
        }

        $src = imagecreatefromjpeg("https:" . $portada);
        list($ancho, $alto) = getimagesize($client_logo);

        if ($with_client_logo === 1 || $with_client_logo === 2) {
            $src = $this->getLogoPackage($src, $dst, $ancho, $alto);
        }

        $fontsize = 18;
        $text = $title . " - " . $days . " " . $trad['days'] . " " . ($days - 1) . " " . $trad['nights'];
        $top = 1420;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);

        $fontsize = 17;
        $text = str_replace(",", " - ", $destinies);
        $top = 1450;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);

        $fontsize = 17;
        $text = $trad['typeTour'] . ': ' . $type_package;
        $top = 1480;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);

        $fontsize = 17;
        $text = $date_operations;
        $top = 1510;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);

        if ($lang == 'es' || $lang == 'pt') {
            $_date_from = explode("-", $date_from);
            $text_date_from = (int)$_date_from[2] . " " . $trad['de'] . " " . $trad['mes'][((int)$_date_from[1]) - 1]['name'];

            $_date_to = explode("-", $date_to);
            $text_date_to = (int)$_date_to[2] . " " . $trad['de'] . " " . $trad['mes'][((int)$_date_to[1]) - 1]['name'] . " ";
        }

        if ($lang == 'en') {
            $_date_from = explode("-", $date_from);
            $text_date_from = $trad['mes'][((int)$_date_from[1]) - 1]['name'] . " " . (int)$_date_from[2];

            $_date_to = explode("-", $date_to);
            $text_date_to = $trad['mes'][((int)$_date_to[1]) - 1]['name'] . " " . (int)$_date_to[2];
        }

        $fontsize = 17;
        $text = $trad['validity'] . ':' . $trad['desde'] . $text_date_from . strtolower($trad['al']) . $text_date_to . $trad['del'] . " " . $_date_to[0];
        $top = 1540;
        $src = $this->getText($src, $text, $fontsize, $top, $portOrigin);

        $code = explode("/packages/", $portada);
        $code = str_replace("/frontpage.jpg", "", $code[1]);
        $images_test = $code . '-' . $lang . '-' . md5(date("Y-m-d H:i:s"));
        $folder = 'images/portadas/create/';

        imagepng($src, $folder . $images_test . '.jpg', 0);
        imagedestroy($src);
        $portada_name = $images_test . '.jpg';

        $url_cloudinary = url($folder . $portada_name);
        $cloudinary = false;

        /*
        if ($code_user == 'mlu' || $code_user == 'apa') {
            $url_cloudinary = $this->moveToCloudinary($folder . $portada_name, [
                'folder' => 'covers'
            ]);
            $cloudinary = true;
        }
        */

        return Response::json([
            'cloudinary' => $cloudinary,
            'success'    => true,
            "code"       => $code,
            "image"      => $url_cloudinary,
            'portada'    => $portada_name
        ]);
    }

    public function getText($source, $text, $fontsize, $top, $portOrigin)
    {

        $font = public_path() . '/fonts/calibri-bold.ttf';
        $angle = 0; //angle of your text

        $text = $text;
        $dimensions = imagettfbbox($fontsize, $angle, $font, $text);
        $textWidth = abs($dimensions[4] - $dimensions[0]) + 2;

        $imageWidth = $textWidth; //width of your image
        $imageHeight = 80; // height of your image
        $logoimg = imagecreatetruecolor($imageWidth, $imageHeight);

        //for transparent background
        imagealphablending($logoimg, false);
        imagesavealpha($logoimg, true);
        $col = imagecolorallocatealpha($logoimg, 255, 255, 255, 127);
        imagefill($logoimg, 0, 0, $col);
        //for transparent background
        $brown = imagecolorallocate($logoimg, 117, 116, 116); //for font color
        $white = imagecolorallocate($logoimg, 255, 255, 255); //for font color

        $x = 0; // x- position of your text
        $y = 50; // y- position of your text
        $angle = 0; //angle of your text
        //imagettftext($logoimg, $fontsize,$angle , $x, $y, $brown, $font, $text); //fill text in your image

        if ($portOrigin == false) {
            imagettftext($logoimg, $fontsize, $angle, $x, $y, $brown, $font, $text); //fill text in your image
            $x = imagesx($source) - $textWidth - 60;
        } else {
            imagettftext($logoimg, $fontsize, $angle, $x, $y, $white, $font, $text); //fill text in your image
            $x = imagesx($source) - $textWidth - 80;
        }

        imagecopy($source, $logoimg, $x, $top, 0, 0, $textWidth, $imageHeight);

        return $source;
    }


    public function getLogo($source, $logo, $ancho, $top)
    {

        $max_ancho = 270;
        $max_alto = 180;

        $x_ratio = $max_ancho / $ancho;
        $y_ratio = $max_alto / $top;

        if (($ancho <= $max_ancho) && ($top <= $max_alto)) {
            $ancho_final = $ancho;
            $alto_final = $top;
        } elseif (($x_ratio * $top) < $max_alto) {
            $alto_final = ceil($x_ratio * $top);
            $ancho_final = $max_ancho;
        } else {
            $ancho_final = ceil($y_ratio * $ancho);
            $alto_final = $max_alto;
        }

        if ($max_ancho > $ancho_final) {

            $ancho_img = round(($max_ancho - $ancho_final) / 2);
            $total = $ancho_final + $ancho_img;

            $rx1 = 914 - round(2480 / $ancho_final);
            $rx = round(2480 / $ancho_final) + $rx1;

            $y1 = 52 - round(3508 / $alto_final);
            $y = round(3508 / $alto_final) + $y1;

            imagecopyresampled($source, $logo, $rx, $y, 0, 0, $total, $max_alto, $ancho, $top);
        } else {

            $rx1 = 908 - round(2480 / $ancho_final);
            $rx = round(2480 / $ancho_final) + $rx1;

            $y1 = 80 - round(3508 / $alto_final);
            $y = round(3508 / $alto_final) + $y1;
            imagecopyresampled($source, $logo, $rx, $y, 0, 0, $ancho_final, $alto_final, $ancho, $top);
        }

        return $source;
    }

    public function getLogoPackage($source, $logo, $ancho, $top)
    {

        $max_ancho = 270;
        $max_alto = 130;

        $x_ratio = $max_ancho / $ancho;
        $y_ratio = $max_alto / $top;

        if (($ancho <= $max_ancho) && ($top <= $max_alto)) {
            $ancho_final = $ancho;
            $alto_final = $top;
        } elseif (($x_ratio * $top) < $max_alto) {
            $alto_final = ceil($x_ratio * $top);
            $ancho_final = $max_ancho;
        } else {
            $ancho_final = ceil($y_ratio * $ancho);
            $alto_final = $max_alto;
        }

        if ($max_ancho > $ancho_final) {

            $ancho_img = round(($max_ancho - $ancho_final) / 2);
            $total = $ancho_final + $ancho_img;

            $rx = round(2480 / $ancho_final) + 30;

            $y1 = 52 - round(3508 / $alto_final);
            $y = round(3508 / $alto_final) + $y1;

            imagecopyresampled($source, $logo, $rx, $y - 30, 0, 0, $total, $max_alto, $ancho, $top);
        } else {

            $rx = round(2480 / $ancho_final) + 30;

            $y1 = 80 - round(3508 / $alto_final);
            $y = round(3508 / $alto_final) + $y1;
            imagecopyresampled($source, $logo, $rx, $y - 30, 0, 0, $ancho_final, $alto_final, $ancho, $top);
        }

        return $source;
    }

    public function getPriceAmount($price, $client_commission_status, $user_type_id, $client_commission)
    {
        // Convertir el precio a float en caso de venir como string con coma o separadores
        $price = floatval(str_replace(',', '', $price));

        if (
            intval($client_commission_status) === 1 &&
            intval($user_type_id) === 4 &&
            floatval($client_commission) > 0
        ) {
            $commissionRate = floatval($client_commission) / 100;
            $priceWithCommission = $price * (1 + $commissionRate);
            return roundLito($priceWithCommission);
        }

        return $price;
    }
}
