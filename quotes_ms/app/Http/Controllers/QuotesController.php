<?php

namespace App\Http\Controllers;

use App\Enums\QuoteStatus;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Package;
use App\Models\Service;
use App\Models\Language;
use App\Models\QuoteLog;
use App\Models\QuoteNote;
use App\Models\TypeClass;
use App\Models\QuoteRange;
use App\Models\RatesPlans;
use App\Models\ShareQuote;
use App\Models\UserMarket;
use App\Http\Traits\Quotes;
use App\Models\HotelClient;
use App\Models\QuotePeople;
use App\Models\Reservation;
use App\Models\ServiceRate;
use App\Models\ServiceType;
use App\Models\ClientSeller;
use App\Models\Notification;
use App\Models\QuoteService;
use Illuminate\Http\Request;
use App\Models\PoliciesRates;
use App\Models\QuoteAgeChild;
use App\Models\QuoteCategory;
use App\Models\ServiceClient;
use App\Models\DateRangeHotel;
use App\Models\PackageService;
use App\Models\QuotePassenger;
use App\Models\QuoteHistoryLog;
use App\Models\RatesPlansRooms;
use App\Models\ServiceRatePlan;
use App\Mail\QuoteDiscountAlert;
use App\Models\PackagePlanRate;
use App\Models\QuoteDestination;
use App\Models\QuoteServiceRate;
use App\Models\QuoteServiceRoom;
use App\Models\ServiceInventory;
use App\Models\PhysicalIntensity;
use App\Models\QuoteDistribution;
use Illuminate\Http\JsonResponse;
use App\Http\Stella\StellaService;
use App\Models\PackageDestination;
use App\Models\PackageDynamicRate;
use App\Models\PackageServiceRate;
use App\Models\PackageServiceRoom;
use App\Models\PackageTranslation;
use App\Models\QuoteAccommodation;
use App\Models\QuoteServiceAmount;
use App\Models\ServiceDestination;
use App\Models\ServiceTranslation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\QuoteHistories;
use App\Models\RatePlanAssociation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Models\RatesPlansCalendarys;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationSharedQuote;
use App\Models\QuoteServicePassenger;
use App\Models\PackageDynamicRateCopy;
use App\Models\PackageDynamicSaleRate;
use App\Models\PackageRateSaleMarkup;
use App\Models\ServiceRateAssociation;
use App\Models\PackagePlanRateCategory;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\QuoteDistributionPassenger;
use App\Http\Traits\QuoteDetailsPriceRange;
use App\Http\Traits\Package as TraitPackage;
use App\Http\Traits\QuoteDetailsPricePassengers;
use App\Http\Traits\CalculateCancellationPolicies as TraitCalculateCancellationPolicies;
use App\Models\QuoteServiceRoomHyperguest;
use App\Http\Aurora\AuroraExternalApiService;
use App\Models\Room;
use Illuminate\Support\Facades\App;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Casts\Json;

class QuotesController extends Controller
{
    use Quotes;
    use TraitPackage;
    use QuoteHistories;
    use TraitCalculateCancellationPolicies;
    use QuoteDetailsPricePassengers;
    use QuoteDetailsPriceRange;
    protected StellaService $stellaService;
    protected AuroraExternalApiService $auroraService;
    protected $authorization;

    public function __construct(Request $request, StellaService $stellaService, AuroraExternalApiService $auroraService)
    {
        $this->stellaService = $stellaService;
        $this->auroraService = $auroraService;
        $this->auroraService->setAuthorization($request->header('authorization'));
    }

    public function index(Request $request)
    {
        try {
            $paging = $request->input('page') ? $request->input('page') : 1;
            $limit = $request->input('limit');
            $querySearch = $request->input('queryCustom');
            $lang = ($request->has('lang')) ? $request->input('lang') : 'en';
            $filterBy = $request->input('filterBy');
            $destinations = $request->input('destinations');
            $market = (int)$request->input('market');
            $executive = $request->input('executive');
            $client_id = $request->input('client'); // $this->getClientId($request);

            if (empty($client_id)) {
                $client_id = $this->getClientId($request);
            }

            $language_id = Language::where('iso', $lang)->first()->id;

            $quotes = Quote::with([
                'categories' => function ($query) use ($language_id) {
                    $query->select(['id', 'quote_id', 'type_class_id']);
                    $query->with([
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
                        }
                    ]);
                    $query->with([
                        'type_class' => function ($query) use ($language_id) {
                            $query->select(['id', 'code', 'order', 'color']);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                        }
                    ]);
                }
            ])->with([
                'log_user' => function ($query) {
                    $query->select(['id', 'quote_id', 'type', 'object_id', 'created_at']);
                    $query->with([
                        'user' => function ($query) {
                            $query->select(['id', 'name', 'email', 'code', 'photo']);
                        }
                    ]);
                }
            ])->with([
                'destinations' => function ($query) {
                    $query->select(['id', 'quote_id', 'state_id']);
                    $query->with([
                        'state' => function ($query) {
                            $query->select(['id', 'iso', 'country_id']);
                        }
                    ]);
                }
            ])->with([
                'service_type' => function ($query) {
                    $query->select(['id', 'code', 'abbreviation']);
                }
            ])->withCount([
                'logs as sent_logs_count' => function ($query) {
                    $query->where('type', 'copy_to');
                }
            ])->withCount([
                'logs as received_logs_count' => function ($query) {
                    $query->where('type', 'copy_from');
                }
            ])->with([
                'user' => function ($query) {
                    $query->select(['id', 'name', 'email', 'code', 'photo']);
                }
            ])->withCount(['history_logs'])
                ->with([
                    'permission' => function ($query) {
                        $query->with('user');
                        $query->with('client');
                        $query->with('seller');
                    }
                ])->with([
                    'reservation' => function ($query) {
                        $query->select(['file_code', 'object_id', 'entity']);
                    }
                ]);

            $_quotes = Quote::with(['user']);

            $users = [];
            $usersIn = [];
            $usersClientIn = [];

            if ($request->input('filterUserType') == 'C' and $client_id != '') {
                $usersClientIn = ClientSeller::where('client_id', '=', $client_id)->pluck('user_id');
                $quotes = $quotes->whereIn('user_id', $usersClientIn);
                $_quotes = $_quotes->whereIn('user_id', $usersClientIn);
                $executive = '';
            }

            if (!empty($querySearch)) {   //AND !is_numeric($querySearch)

                $_quotes = $_quotes->where('id', $querySearch);

                if (!empty($querySearch)) {

                    $logs = QuoteLog::orWhere(function ($query) use ($querySearch) {
                        $query->where('type', '=', 'editing_quote');
                        $query->where('quote_id', '=', $querySearch);
                    })->orWhere(function ($query) use ($querySearch) {
                        $query->where('type', '=', 'copy_self');
                        $query->where('object_id', '=', $querySearch);
                    })->get();

                    $quotes = $quotes->where(function ($query) use ($querySearch, $logs) {

                        $query->orWhere('id', $querySearch);

                        foreach ($logs as $log) {
                            $query->orWhere('id', '=', $log->quote_id);
                            $query->orWhere('id', '=', $log->object_id);
                        }
                        $query->orWhere('id', '=', $querySearch);
                        $query->orWhere('name', 'like', '%' . $querySearch . '%');

                        $query->orWhere(function ($q) use ($querySearch) {
                            $q->whereHas('reservation', function ($_q) use ($querySearch) {
                                $_q->where('file_code', 'like', '%' . trim($querySearch) . '%');
                            });
                        });
                    });
                }
            }


            if ($market > 0) {
                $_users = UserMarket::where('market_id', '=', $market)->get();

                foreach ($_users as $key => $value) {
                    $user = User::where('id', '=', $value->user_id)
                        ->where('user_type_id', '=', 3)->first();

                    if ($user != null) {
                        $users[$value->user_id] = $user;
                        $usersIn[] = $value->user_id;
                    }
                }
            }

            if ($executive != '') {

                if (Auth::user()->user_type_id == 4) {
                    $share_quotes_id = ShareQuote::where('seller_id', Auth::user()->id)->get()->pluck('quote_id');
                    if (count($share_quotes_id) > 0) {
                        // $quotes = $quotes->orWhereIn('id', $share_quotes_id);
                        $quotes = $quotes->where(function ($query) use ($share_quotes_id, $executive) {
                            $query->where('user_id', '=', $executive);
                            $query->orWhereIn('id', $share_quotes_id);
                        });
                    } else {
                        $quotes = $quotes->where('user_id', '=', $executive);
                    }
                } else {
                    $quotes = $quotes->where('user_id', '=', $executive);
                }
            } else {
                if ($market > 0) {
                    $quotes = $quotes->where(function ($query) use ($usersIn, $usersClientIn) {
                        $query->orWhere(function ($query) use ($usersIn, $usersClientIn) {
                            if (count($usersClientIn) > 0) {
                                $query->whereIn('user_id', $usersClientIn);
                            } else {
                                $query->whereIn('user_id', $usersIn);
                            }
                        });
                    });
                } else {
                    $quotes = $quotes->where('user_id', Auth::user()->id);
                    if (Auth::user()->user_type_id == 4) {

                        $share_quotes_id = ShareQuote::where('seller_id', Auth::user()->id)->get()->pluck('quote_id');

                        if (count($share_quotes_id) > 0) {

                            $quotes = $quotes->where(function ($query) use ($share_quotes_id) {
                                $query->orWhereIn('id', $share_quotes_id);
                            });
                        }
                    }
                }
            }

            $quotes->where('status', 1);
            // $quotes->where('deleted_at', null);

            if ($filterBy == 'activated') {
                $quotes->whereDate('date_in', ">=", date('Y-m-d'));
            }
            if ($filterBy == 'expired') {
                $quotes->whereDate('date_in', "<", date('Y-m-d'));
            }
            if ($filterBy == 'comingExpired') {
                $quotes->whereDate('date_in', "<=", date('Y-m-d', strtotime("+5 days")))
                    ->whereDate('date_in', ">=", date('Y-m-d'));
            }
            if ($filterBy == 'sent') {
                $quotes->having('sent_logs_count', '>', 0);
            }

            if ($filterBy == 'received') {
                $quotes->having('received_logs_count', '>', 0);
            }

            if ($filterBy == 'files') {
                $quotes->whereHas('reservation', function ($_q) use ($querySearch) {});
            }

            if ($filterBy == 'news') {
                if (Auth::user()->user_type_id == 4) {
                    $quotes->where('created_at', '>=', date('Y-m-d', strtotime("-7 days")));
                } else {
                    $quotes->where('created_at', '>=', date('Y-m-d', strtotime("-1 days")));
                }
            }


            if (!empty($destinations)) {
                $destinations = explode(',', $destinations);
                $quotes->withCount([
                    'destinations as destinations_count' => function ($query) use ($destinations) {
                        $query->whereIn('state_id', $destinations)->distinct();
                    }
                ])->whereHas(
                    'destinations',
                    function ($query) use ($destinations) {
                        $query->whereIn('state_id', $destinations);
                    }
                );
                $quotes->having('destinations_count', '=', count($destinations));
            }

            $count = $quotes->count();

            if ($paging === 1) {
                $quotes = $quotes->take($limit)
                    ->orderBy('show_in_popup', 'desc')
                    ->orderBy('created_at', 'desc')->get();
            } else {
                $quotes = $quotes->skip($limit * ($paging - 1))
                    ->orderBy('show_in_popup', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take($limit)->get();
            }

            $totals = [];

            if ($executive != '') {
                if (Auth::user()->user_type_id == 4) {
                    $share_quotes_id = ShareQuote::where('seller_id', Auth::user()->id)->get()->pluck('quote_id');


                    if (count($share_quotes_id) > 0) {
                        $_quotes = $_quotes->where(function ($query) use ($share_quotes_id, $executive) {
                            $query->where('user_id', '=', $executive);
                            $query->orWhereIn('id', $share_quotes_id);
                        });
                    } else {
                        $_quotes = $_quotes->where('user_id', '=', $executive);
                    }
                } else {
                    $_quotes = $_quotes->where('user_id', '=', $executive);
                }
            } else {
                if ($market > 0) {
                    $_quotes = $_quotes->orWhere(function ($query) use ($usersIn) {
                        $query->whereIn('user_id', $usersIn);
                    });
                } else {
                    $_quotes = $_quotes->where('user_id', Auth::user()->id);
                    if (Auth::user()->user_type_id == 4) {
                        $share_quotes_id = ShareQuote::where('seller_id', Auth::user()->id)->get()->pluck('quote_id');
                        if (count($share_quotes_id) > 0) {
                            $_quotes = $_quotes->orWhereIn('id', $share_quotes_id);
                        }
                    }
                }
            }
            $_quotes = $_quotes->where('status', 1);
            $_quote_clone = clone $_quotes;

            $totals['files'] = DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('entity', 'quote')
                ->whereIn('object_id', $_quote_clone->reorder()->select('id'))
                ->count();
            $columns_logs = ['id', 'quote_id', 'type', 'object_id', 'user_id'];
            $totals['total'] = $_quotes->count();
            $totals['expired'] = $_quotes->whereDate('date_in', "<", date('Y-m-d'))->count();
            $totals['activated'] = $totals['total'] - $totals['expired'];

            $days = 1;
            if (Auth::user()->user_type_id == 4) {
                $days = 7;
            }

            $totals['news'] = DB::table('quotes')
                ->whereNull('deleted_at')
                ->where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->where('created_at', '>=', date('Y-m-d', strtotime("-" . $days . " days")))->count();
            $totals['no_viewed'] = DB::table('quotes')
                ->whereNull('deleted_at')
                ->where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->where('show_in_popup', 1)->count();

            $quotes = $quotes->transform(function ($q) use ($columns_logs) {
                $q['logs'] = collect();
                $q['editing_quote_user'] = [
                    'editing' => false,
                    'user'    => null,
                ];
                //Todo obtengo el log de la cotizacion borrador, para obtener la cotizacion original
                $get_quote_editing = QuoteLog::where('object_id', $q['id'])
                    ->where('type', 'editing_quote')
                    ->with([
                        'user' => function ($query) {
                            $query->select(['id', 'name', 'code', 'email']);
                        }
                    ])
                    ->orderBy('created_at', 'asc')
                    ->first($columns_logs);
                if ($get_quote_editing and !empty($get_quote_editing->user)) {
                    $q['editing_quote_user'] = [
                        'editing' => true,
                        'user'    => $get_quote_editing->user,
                    ];
                }
                //              //TODO verificar proceso
                $query_log_copy_to = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'copy_to'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_copy_from = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'copy_from'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_copy_self = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'copy_self'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_from_quote = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'from_quote'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_from_package = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'from_package'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_quote_added = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'quote_added'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_extension_added = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'extension_added'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_booking = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'booking'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_editing_quote = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'editing_quote'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_editing_extension = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'editing_extension'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                $query_log_editing_package = QuoteLog::where('quote_id', $q['id'])->where(
                    'type',
                    'editing_package'
                )->orderBy('created_at', 'desc')->first($columns_logs);
                if ($query_log_copy_to) {
                    $q['logs']->add($query_log_copy_to);
                }

                if ($query_log_copy_from) {
                    $q['logs']->add($query_log_copy_from);
                }

                if ($query_log_copy_self) {
                    $q['logs']->add($query_log_copy_self);
                }

                if ($query_log_from_quote) {
                    $q['logs']->add($query_log_from_quote);
                }

                if ($query_log_from_package) {
                    $q['logs']->add($query_log_from_package);
                }

                if ($query_log_quote_added) {
                    $q['logs']->add($query_log_quote_added);
                }

                if ($query_log_extension_added) {
                    $q['logs']->add($query_log_extension_added);
                }

                if ($query_log_booking) {
                    $q['logs']->add($query_log_booking);
                }

                if ($query_log_editing_quote) {
                    $q['logs']->add($query_log_editing_quote);
                }

                if ($query_log_editing_extension) {
                    $q['logs']->add($query_log_editing_extension);
                }

                if ($query_log_editing_package) {
                    $q['logs']->add($query_log_editing_package);
                }

                return $q;
            });
            foreach ($quotes as $q) {
                if (count($q->destinations) == 0) {
                    $this->updateNightsAndCities($q->id);
                }
                $now = Carbon::parse(Carbon::now()->format('Y-m-d'));
                $q->loadingRow = false;
                $q->backRow = 'dark';
                if ($q->code != '' && $q->code != null && count($q->categories) == 0) {
                    $q->backRow = 'warning';
                }
                if ($now->diffInHours(Carbon::parse($q->created_at)) <= 24) {
                    $q->backRow = 'new';
                }

                $q->diff_days = $now->diffInDays(Carbon::parse($q->date_in), false);

                $q->when_it_starts = "Inicia hoy!";
                if ($q->diff_days < 0) {
                    $q->backRow = 'warning';
                    $q->when_it_starts = "Venció hace " . abs($q->diff_days) . " días";
                }
                if ($q->diff_days > 0) {
                    $q->when_it_starts = "Vence en " . $q->diff_days . " días";
                }

                $q->detail = '';
                foreach ($q->logs as $log) {
                    if ($log->type == "copy_self") {
                        $q->detail = "Duplicado de #" . $log->object_id;
                        break;
                    }
                    if ($log->type == "from_quote") {
                        $q->detail = "A partir de #" . $log->object_id;
                        break;
                    }
                }
                foreach ($q->log_user as $log) {
                    if ($log->type == "copy_from") {
                        $q->detail = "Compartido por " . $log->user->name;
                        break;
                    }
                }
            }

            $orders_related_ = array();
            $orders_related = array();

            foreach ($quotes as $q) {
                if ($q->file_number && !isset($orders_related_[$q->file_number])) {
                    $quote_id_related = DB::table("reservations")->where('entity', "Quote")
                        ->where("file_code", $q->file_number)->first();
                    if ($quote_id_related) {
                        $quote_related = Quote::find($quote_id_related->object_id);
                        if ($quote_related && $quote_related->order_related) {
                            $orders_related_[$q->file_number] = $quote_related->order_related;
                            array_push($orders_related, ["file" => (int)$q->file_number, "order" => $quote_related->order_related]);
                        }
                    }
                }
            }

            $data = [
                'success'        => true,
                'data'           => $quotes,
                'executive'      => $executive,
                'market'         => $market,
                'executives'     => $users,
                'orders_related' => $orders_related,
                'count'          => $count,
                'totals'         => $totals,
                'pages'          => ceil($count / $limit),
            ];

            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json([
                'type'    => 'error',
                'message' => $ex->getMessage(),
                'file'    => $ex->getFile(),
                'line'    => $ex->getLine(),
            ]);
        }
    }

    public function getDestinations()
    {
        $quote_ids = Quote::where('user_id', Auth::user()->id)->where('status', 1)->pluck('id');

        $destinations = QuoteDestination::select('state_id')
            ->whereIn('quote_id', $quote_ids)
            ->with([
                'state' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'state');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->distinct()
            ->get();

        $destinationsResponse = [];
        foreach ($destinations as $destination) {
            $stateId = $destination->state_id ?? null;
            $stateName = null;

            if (
                isset($destination['state']) &&
                isset($destination['state']['translations']) &&
                isset($destination['state']['translations'][0]['value'])
            ) {
                $stateName = $destination['state']['translations'][0]['value'];
            }

            array_push($destinationsResponse, [
                'id'   => $stateId,
                'name' => $stateName
            ]);
        }

        return Response::json(['success' => true, 'data' => $destinationsResponse]);
    }

    public function quoteMe(Request $request)
    {
        $client_id = $this->getClientId($request);
        $quote_id = $request->post('quote_id');
        $estimated_price = $this->updateAmountAllServices($quote_id, $client_id);

        // Actualizando los montos estimados en la tabla de quotes..
        $quote = Quote::find($quote_id);
        $quote->estimated_price = $estimated_price;
        $quote->save();

        return response()->json([
            "success" => true,
            "estimated_price" => $estimated_price,
            "message" => "cotizacion realizada",
        ], 200);
    }

    public function update_notes(Request $request, $quote_id_original)
    {
        $service = (object)$request->hotel;
        $quote_service = QuoteService::find($service->id);
        $quote_service->notes = $service->notes;
        $quote_service->save();

        return response()->json(['success' => true]);
    }

    public function searchByUserStatus($status, Request $request): JsonResponse
    {
        $lang = $request->input('lang');
        App::setLocale($lang);
        $language_id = Language::where('iso', $lang)->first()->id;
        $user_id = Auth::user()->id; //1
        $client_id = $this->getClientId($request);
        if (!$client_id) {
            throw new \Exception('client no selected');
        }

        $markets = UserMarket::where('user_id', $user_id)->pluck('market_id');

        //Todo Si no existe acomodacion lo crea
        $this->setAccommodationNotExist();

        $quote = Quote::where('user_id', $user_id)->where('status', $status)->first();
        if ($quote) {
            $this->updateAmountAllServices($quote->id, $client_id);

            if ($quote->status == 2) {
                $find_quote_original = DB::table('quote_logs')->where('quote_id', $quote->id)->where(
                    'type',
                    'editing_quote'
                )->first();
                if ($find_quote_original) {
                    $quote_original = Quote::find($find_quote_original->object_id);

                    // comentado por que el campo ya no existe
                    // $quote->quote_id_multi_region_to = $quote_original->quote_id_multi_region_to;
                    // $quote->quote_id_multi_region_from = $quote_original->quote_id_multi_region_from;

                    if ($quote_original && $quote_original->order_related) {
                        $quote->order_related = $quote_original->order_related;
                    }
                    $quote->save();
                }
            }
        }

        $quote = Quote::where('user_id', $user_id)
            ->where('status', $status)
            ->with([
                'categories' => function ($query) use ($lang, $language_id) {
                    $query->select(['id', 'quote_id', 'type_class_id']);
                    $query->with([
                        'type_class' => function ($query) use ($language_id) {
                            $query->select(['id', 'color']);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select(['id', 'value', 'object_id']);
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);
                    $query->with([
                        'services' => function ($query) use ($lang, $language_id) {
                            $query->select([
                                'id',
                                'quote_category_id',
                                'type',
                                'object_id',
                                'order',
                                'date_in',
                                'date_out',
                                'hour_in',
                                'nights',
                                'adult',
                                'child',
                                'infant',
                                'single',
                                'double',
                                'double_child',
                                'triple',
                                'triple_child',
                                'triple_active',
                                'locked',
                                'created_at',
                                'updated_at',
                                'on_request',
                                'extension_id',
                                'parent_service_id',
                                'new_extension_id',
                                'new_extension_parent_id',
                                'optional',
                                'code_flight',
                                'origin',
                                'destiny',
                                'date_flight',
                                'notes',
                                'schedule_id',
                                'is_file',
                                'file_itinerary_id',
                                'file_status',
                                'file_amount_sale',
                                'file_amount_cost',
                                'hyperguest_pull',
                                'markup_regionalization'
                            ]);
                            $query->with([
                                'flightOrign' => function ($query) use ($language_id) {
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select('object_id', 'value');
                                            $query->where('type', 'state');
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                },
                                'flightDestination' => function ($query) use ($language_id) {
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select('object_id', 'value');
                                            $query->where('type', 'state');
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                }
                            ]);
                            $query->with('amount');
                            $query->with([
                                'hotel' => function ($query) use ($lang) {
                                    $query->withTrashed();
                                    $query->with(['channel']);
                                    $query->with([
                                        'country.translations' => function ($query) use ($lang) {
                                            $query->where('type', 'country');
                                            $query->whereHas('language', function ($q) use ($lang) {
                                                $q->where('iso', $lang);
                                            });
                                        },
                                    ]);
                                    $query->with([
                                        'state.translations' => function ($query) use ($lang) {
                                            $query->where('type', 'state');
                                            $query->whereHas('language', function ($q) use ($lang) {
                                                $q->where('iso', $lang);
                                            });
                                        },
                                    ]);
                                    $query->with([
                                        'city.translations' => function ($query) use ($lang) {
                                            $query->where('type', 'city');
                                            $query->whereHas('language', function ($q) use ($lang) {
                                                $q->where('iso', $lang);
                                            });
                                        },
                                    ]);
                                    $query->with([
                                        'district.translations' => function ($query) use ($lang) {
                                            $query->where('type', 'district');
                                            $query->whereHas('language', function ($q) use ($lang) {
                                                $q->where('iso', $lang);
                                            });
                                        },
                                    ]);
                                    $query->with([
                                        'typeclass.translations' => function ($query) use ($lang) {
                                            $query->where('type', 'typeclass');
                                            $query->whereHas('language', function ($q) use ($lang) {
                                                $q->where('iso', $lang);
                                            });
                                        },
                                    ]);
                                },
                            ]);
                            $query->with(['category']);
                            $query->with(['package_extension.plan_rates.plan_rate_categories']);
                            $query->with([
                                'passengers' => function ($query) {
                                    $query->with('passenger.age_child');
                                    $query->orderBy('quote_passenger_id');
                                },
                            ]);
                            $query->with(['service_rate']);
                            $query->with([
                                'service' => function ($query) use ($language_id) {
                                    $query->select([
                                        'id',
                                        'aurora_code',
                                        'name',
                                        'equivalence_aurora',
                                        'qty_reserve_client',
                                        'qty_reserve',
                                        'allow_child',
                                        'allow_infant',
                                        'infant_min_age',
                                        'infant_max_age',
                                        'unit_id',
                                        'unit_duration_id',
                                        'service_type_id',
                                        'classification_id',
                                        'service_sub_category_id',
                                        'duration',
                                        'pax_min',
                                        'pax_max',
                                        'type',
                                        'status',
                                        'notes',
                                        'deleted_at',
                                    ]);
                                    $query->with([
                                        'serviceSubCategory' => function ($query) use ($language_id) {
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->where('type', 'servicesubcategory');
                                                    $query->where('language_id', $language_id);
                                                },
                                            ]);
                                            $query->with([
                                                'serviceCategories' => function ($query) use ($language_id) {
                                                    $query->with([
                                                        'translations' => function ($query) use ($language_id) {
                                                            $query->select('object_id', 'value');
                                                            $query->where('type', 'servicecategory');
                                                            $query->where('language_id', $language_id);
                                                        },
                                                    ]);
                                                },
                                            ]);
                                        },
                                    ]);
                                    $query->with([
                                        'serviceDestination' => function ($query) use ($language_id) {
                                            $query->with([
                                                'state.translations' => function ($query) use ($language_id) {
                                                    $query->select('object_id', 'value');
                                                    $query->where('type', 'state');
                                                    $query->where('language_id', $language_id);
                                                }
                                            ]);
                                            $query->select([
                                                'id',
                                                'service_id',
                                                'country_id',
                                                'state_id',
                                                'city_id',
                                                'zone_id'
                                            ]);
                                        }
                                    ]);
                                    $query->with([
                                        'serviceOrigin' => function ($query) use ($language_id) {
                                            $query->with([
                                                'state.translations' => function ($query) use ($language_id) {
                                                    $query->select('object_id', 'value');
                                                    $query->where('type', 'state');
                                                    $query->where('language_id', $language_id);
                                                }
                                            ]);
                                            $query->with([
                                                'country.translations' => function ($query) use ($language_id) {
                                                    $query->select('object_id', 'value');
                                                    $query->where('type', 'country');
                                                    $query->where('language_id', $language_id);
                                                }
                                            ]);
                                            $query->select([
                                                'id',
                                                'service_id',
                                                'country_id',
                                                'state_id',
                                                'city_id',
                                                'zone_id'
                                            ]);
                                        }
                                    ])->with([
                                        'unitDurations' => function ($query) use ($language_id) {
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->select('id', 'value', 'language_id', 'object_id');
                                                    $query->where('type', 'unitduration');
                                                    $query->where('language_id', $language_id);
                                                }
                                            ]);
                                        }
                                    ]);
                                    $query->with('children_ages');
                                    $query->with('galleries');
                                    $query->withTrashed();

                                    $query->with([
                                        'serviceType' => function ($query) use ($language_id) {
                                            $query->select(['id', 'code', 'abbreviation']);
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->select(['id', 'value', 'object_id']);
                                                    $query->where('language_id', $language_id);
                                                },
                                            ]);
                                        },
                                    ]);
                                    $query->with([
                                        'service_translations' => function ($query) use ($language_id) {
                                            $query->select([
                                                'id',
                                                'name',
                                                'name_commercial',
                                                'description',
                                                'description_commercial',
                                                'itinerary',
                                                'itinerary_commercial',
                                                'summary',
                                                'summary_commercial',
                                                'service_id',
                                            ]);
                                            $query->where('language_id', $language_id);
                                        },
                                    ]);
                                    $query->with([
                                        'schedules.servicesScheduleDetail',
                                    ]);
                                },
                            ]);
                            $query->with([
                                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use (
                                    $language_id
                                ) {
                                    $query->where('language_id', $language_id);
                                },
                                'service_rooms.rate_plan_room.room.room_type',
                                'service_rooms.rate_plan_room.room.translations' => function ($query) use (
                                    $language_id
                                ) {
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                            $query->with([
                                'service_rooms_hyperguest.rate_plan.meal.translations' => function ($query) use (
                                    $language_id
                                ) {
                                    $query->where('language_id', $language_id);
                                },
                                'service_rooms_hyperguest.room.room_type',
                                'service_rooms_hyperguest.room.translations' => function ($query) use (
                                    $language_id
                                ) {
                                    $query->where('language_id', $language_id);
                                },
                            ]);

                            // $query->where(function ($query)  {  // este bloque se agrego para que solo salgan los servicios que no esten bloqueados
                            //     $query->where('single','<>',0)
                            //             ->orWhere('double','<>',0)
                            //             ->orWhere('triple','<>',0);
                            // });

                            // $query->orderByRaw('-new_extension_id');
                            $query->where(function ($q) {
                                // Todos los servicios NO hotel pasan
                                $q->where('type', '<>', 'hotel')
                                    // Hoteles que sí tengan AL MENOS un room válido
                                    ->orWhereHas('service_rooms', function ($q2) {
                                        $q2->whereHas('rate_plan_room', function ($q3) {
                                            $q3->whereNull('deleted_at');
                                        });
                                    })
                                    ->orWhereHas('service_rooms_hyperguest', function ($q2) {
                                        $q2->whereHas('rate_plan', function ($q3) {
                                            $q3->whereNull('deleted_at');
                                        });
                                    });
                            });
                            $query->orderBy('date_in');
                            $query->orderBy('order');
                            $query->orderBy('type', 'desc');
                        },
                    ]);
                },
            ])->with([
                'ranges' => function ($query) {
                    $query->select(['id', 'from', 'to', 'quote_id']);
                    $query->orderBy('from');
                },
            ])->with([
                'people' => function ($query) {
                    $query->select(['id', 'adults', 'child', 'quote_id']);
                },
            ])->with([
                'passengers' => function ($query) {
                    $query->with('age_child');
                },
            ])->with([
                'notes' => function ($query) {
                    $query->select(['id', 'parent_note_id', 'comment', 'status', 'created_at', 'quote_id', 'user_id']);
                    $query->with([
                        'user' => function ($query) {
                            $query->select(['id', 'name', 'photo']);
                        },
                    ]);
                },
            ])->with([
                'age_child' => function ($query) {
                    $query->select(['id', 'age', 'quote_id']);
                },
            ])->with([
                'accommodation' => function ($query) {
                    $query->select(['id', 'quote_id', 'single', 'double', 'double_child', 'triple', 'triple_child']);
                },
            ])->get([
                'id',
                'code',
                'name',
                'date_in',
                'cities',
                'nights',
                'estimated_price',
                'user_id',
                'service_type_id',
                'status',
                'discount',
                'discount_detail',
                'discount_user_permission',
                'order_related',
                'order_position',
                'show_in_popup',
                'created_at',
                'updated_at',
                'operation',
                'markup',
                'shared',
                'estimated_travel_date',
                'data_type_passenger',
                'language_id',
                'file_id',
                'file_number',
                'file_total_amount',
                'clone_file_id',
                'clone_parameters',
                'clone_executed',
                'is_multiregion',
            ]);

        $columns_logs = ['id', 'quote_id', 'type', 'object_id', 'user_id'];
        $quote = $quote->transform(function ($q) use ($columns_logs) {
            $q['clone_parameters'] = $q['clone_parameters'] ? json_decode($q['clone_parameters']) : [];
            if (isset($q['clone_parameters']->lang)) {
                $lang_ = Language::where('iso', $q['clone_parameters']->lang)->first();
                if ($lang_) {
                    $q['clone_parameters']->lang_id = $lang_->id;
                }
            }

            $q['editing_quote_user'] = [
                'editing' => false,
                'user'    => null,
            ];
            //Todo obtengo el log de la cotizacion borrador, para obtener la cotizacion original
            $get_quote_editing = QuoteLog::where('quote_id', $q['id'])
                ->where('type', 'editing_quote')
                ->orderBy('created_at', 'asc')
                ->first($columns_logs);
            if ($get_quote_editing) {
                //Todo obtengo todos los logs de la cotizacion original, para obtener los que se estan editando
                $get_quotes_editing_users = QuoteLog::where('object_id', $get_quote_editing->object_id)
                    ->with([
                        'user' => function ($query) {
                            $query->select(['id', 'name', 'code', 'email']);
                        },
                    ])
                    ->where('type', 'editing_quote')
                    ->where('user_id', '<>', null)
                    ->orderBy('created_at', 'asc')
                    ->first($columns_logs);
                if ($get_quotes_editing_users and $get_quotes_editing_users->user_id !== Auth::id()) {
                    $q['editing_quote_user'] = [
                        'editing' => true,
                        'user'    => $get_quotes_editing_users->user,
                    ];
                }
            }

            $q['logs'] = collect();

            $q['reservation'] = [];
            if ($q['file_number'] != null) {
                $reservation_ = DB::table('reservations')->where('deleted_at', null)
                    ->where('file_code', $q['file_number'])->first();
                if ($reservation_) {
                    $q['reservation'] = $reservation_;
                }
            }

            $q['file'] = [
                'file_code'      => null,
                'file_reference' => null,
                'client'         => [
                    'id'   => null,
                    'code' => null,
                    'name' => null,
                ],
                'type_class_id' => null,
            ];
            //TODO verificar proceso
            $query_log_copy_to = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'copy_to'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_copy_from = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'copy_from'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_copy_self = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'copy_self'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_from_quote = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'from_quote'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_from_package = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'from_package'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_quote_added = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'quote_added'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_extension_added = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'extension_added'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_booking = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'booking'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_editing_quote = QuoteLog::where('quote_id', $q['id'])
                ->where('type', 'editing_quote')
                ->orderBy('created_at', 'desc')
                ->first($columns_logs);
            $query_log_editing_extension = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'editing_extension'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            $query_log_editing_package = QuoteLog::where('quote_id', $q['id'])->where(
                'type',
                'editing_package'
            )->orderBy('created_at', 'desc')->first($columns_logs);
            if ($query_log_copy_to) {
                $q['logs']->add($query_log_copy_to);
            }
            if ($query_log_copy_from) {
                $q['logs']->add($query_log_copy_from);
            }

            if ($query_log_copy_self) {
                $q['logs']->add($query_log_copy_self);
            }

            if ($query_log_from_quote) {
                $q['logs']->add($query_log_from_quote);
            }

            if ($query_log_from_package) {
                $q['logs']->add($query_log_from_package);
            }

            if ($query_log_quote_added) {
                $q['logs']->add($query_log_quote_added);
            }

            if ($query_log_extension_added) {
                $q['logs']->add($query_log_extension_added);
            }

            if ($query_log_booking) {
                $q['logs']->add($query_log_booking);
            }

            if ($query_log_editing_quote) {
                $q['logs']->add($query_log_editing_quote);
                //Todo buscado si la cotizacion que se esta editando tiene un file asignado
                $reservation = Reservation::where('entity', 'Quote')
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'name', 'code']);
                        },
                    ])
                    ->where('object_id', $query_log_editing_quote->object_id)
                    ->orderBy('id', 'desc')
                    ->first(['id', 'file_code', 'customer_name', 'client_id', 'type_class_id']);
                if ($reservation) {
                    if (!empty($reservation->type_class_id)) {
                        $q['file'] = [
                            'file_code'      => $reservation->file_code,
                            'file_reference' => $reservation->customer_name,
                            'client'         => [
                                'id'   => $reservation->client->id,
                                'code' => $reservation->client->code,
                                'name' => $reservation->client->name,
                            ],
                            'type_class_id' => $reservation->type_class_id,
                        ];
                    }
                }
            }

            if ($query_log_editing_extension) {
                $q['logs']->add($query_log_editing_extension);
            }

            if ($query_log_editing_package) {
                $q['logs']->add($query_log_editing_package);
            }

            $q['data_type_passenger'] = json_decode($q['data_type_passenger']);

            return $q;
        });
 
        $inventories = [];

        $quote = $quote->transform(function ($quote) use ($lang, $client_id, &$inventories) {
            $service_first_by_markup = 0;
            foreach ($quote['categories'] as $category) {
                foreach ($category['services'] as $key_service => $service) {
                    $flat_migrate = 0;
                    $service['new_extension'] = '';
                    if ($service['new_extension_id'] != '' and $service['new_extension_id'] != null) {
                        $service['new_extension'] = Package::where('id', $service['new_extension_id'])->with([
                            'translations' => function ($query) use ($lang) {
                                $query->whereHas('language', function ($q) use ($lang) {
                                    $q->where('iso', $lang);
                                });
                            },
                        ])->first();
                    }

                    $service['group'] = null;
                    $service['total_accommodations'] = 1;
                    if ($service['hour_in'] == '' || $service['hour_in'] == null) {
                        $date_in_ = convertDate($service['date_in'], '/', '-', 1);
                        $service['hour_in'] = $this->get_hour_ini(
                            $service['schedule_id'],
                            $date_in_,
                            $service['object_id']
                        );
                    }

                    //Todo Obtenemos la cantidad de hab.
                    if ($service['type'] == 'hotel') {
                        $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];
                        $service['total_accommodations'] = $total_accommodations;
                    }

                    $service['validations'] = collect();
                    if (!$service['locked'] and $service['total_accommodations'] > 0) {
                        $service['validations'] = $this->validationsQuoteServices(
                            $service,
                            $quote->operation,
                            $client_id,
                            $quote->ranges,
                            $quote->id
                        );
                    }

                    if ($service['type'] == 'hotel') {
                        if ($service_first_by_markup == 0) {
                            $service_first_by_markup = $this->getMarkupHotel($client_id, $service['object_id'], date('Y-m-d'));
                        }

                        $locked = ($service['locked']) ? 1 : 0;

                        $service['group'] = str_replace(
                            '/',
                            '',
                            $service['date_in']
                        ) . '_' . $service['nights'] . '_' . $service['object_id'] . '_' . $locked;;
                        $date_in_ = convertDate($service['date_in'], '/', '-', 1);
                        $date_out_ = convertDate($service['date_out'], '/', '-', 1);

                        foreach ($service['service_rooms'] as &$service_room) {

                            if (!empty($service_room['rate_plan_room']) && !empty($service_room['rate_plan_room']['id'])) {

                                $ratesPlansCalendars = RatesPlansCalendarys::where(
                                    'rates_plans_room_id',
                                    $service_room['rate_plan_room']['id']
                                )
                                    ->where(function ($query) use ($date_in_, $date_out_) {
                                        $query->where('date', '>=', $date_in_)
                                            ->where('date', '<=', $date_out_);
                                    })
                                    ->with('rate')
                                    ->get();
                                if ($ratesPlansCalendars->count() > 0) {
                                    $service_room['rate_plan_room']['calendarys'] = $ratesPlansCalendars;
                                }

                                $key_group_room = str_replace(
                                    '/',
                                    '',
                                    $service['date_in']
                                ) . '_' . $service['object_id'] . '_' . $service_room['rate_plan_room']['id'];

                                $inventory = $inventories[$key_group_room] ?? null;

                                if (is_null($inventory)) {
                                    /*
                                    $ratePlanRoomId = $service_room['rate_plan_room']['id'];
                                    $dateFrom = Carbon::parse($date_in_)->format('Y-m-d');
                                    $dateTo = Carbon::parse($date_out_)->format('Y-m-d');

                                    // Verificar disponibilidad de habitaciones
                                    $ratePlanRoom = RatesPlansRooms::find($ratePlanRoomId);
                                    if ($ratePlanRoom) {
                                        if ($ratePlanRoom->bag == 1) {
                                            $this->processInventoryBag($service, $ratePlanRoom, $hotelsOnRequest, $dateFrom, $dateTo);
                                        } else {
                                            $this->processInventoryWithoutBag($service, $ratePlanRoom, $hotelsOnRequest, $dateFrom, $dateTo);
                                        }
                                    }
                                    */

                                    $inventory = Inventory::where('rate_plan_rooms_id', $service_room['rate_plan_room']['id'])
                                        ->whereBetween('date', [$date_in_, $date_out_])->min('inventory_num') ?? 0;
                                }

                                //$service['on_request'] = ($inventory > 0) ? 0 : 1;

                                $inventories[$key_group_room] = ($inventory > 0) ? $inventory - 1 : $inventory;

                                if (isset($date_in_)) {
                                    $date_range_hotel = DateRangeHotel::where('hotel_id', $service['object_id'])
                                        ->whereDate('date_from', '<=', $date_in_)
                                        ->whereDate('date_to', '>=', $date_in_)
                                        ->where('rate_plan_room_id', $service_room['rate_plan_room']['id'])->first();

                                    if ($date_range_hotel) {
                                        if ($date_range_hotel['flag_migrate'] == 1) {
                                            $flat_migrate = 1;
                                        }
                                    }
                                }
                            }
                        }


                        if ($quote->operation === 'passengers') {

                            $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];
                            if ($total_accommodations > 0) {
                                $result_price_calculate = $this->calculatePriceRoom($service, $quote->id);

                                if ($result_price_calculate['price'] === 'Error' or $result_price_calculate['price'] === 0) {
                                    $service['import'] = 0;
                                    // dd($result_price_calculate);

                                    if (count($service['validations']) == 0) {
                                        $service['validations'] = collect();
                                    }
                                    $service['validations']->add([
                                        'error'      => $result_price_calculate['price_error'] ?? trans('validations.quotes.hotels.price_error'),
                                        'range'      => '',
                                        'validation' => true,
                                    ]);
                                } else {
                                    $price_ADL = 0;
                                    $price_CHL = 0;
                                    $service['import'] = $result_price_calculate['price'];
                                    $import_amount = [];
                                    foreach ($result_price_calculate['amount'] as $amount) {

                                        $importAdult = 0;
                                        $importChild = 0;
                                        foreach ($amount['passengers'] as $passenger) {
                                            if ($passenger['type'] == 'ADL') {
                                                $importAdult = $importAdult + $passenger['amount'];
                                                if ($price_ADL == 0) {
                                                    $price_ADL = $passenger['amount'];
                                                }
                                            }
                                            if ($passenger['type'] == 'CHD') {
                                                $importChild = $importChild + $passenger['amount'];
                                                if ($price_CHL == 0) {
                                                    $price_CHL = $passenger['amount'];
                                                }
                                            }
                                        }

                                        $import_amount[] = [
                                            'date'     => $amount['date_service'],
                                            'adult'    => $importAdult,
                                            'child'    => $importChild,
                                            'subTotal' => $importAdult + $importChild,
                                        ];
                                    }
                                    $total_pax = $result_price_calculate['adult'] + $result_price_calculate['child'];
                                    $total_pax = $total_pax == 0 ? 1 : $total_pax;
                                    $price_per_person = roundLito($result_price_calculate['price'] / $total_pax);

                                    $service['import_amount'] = [
                                        'price_ADL'        => ($price_ADL > 0 ? $price_ADL : $price_CHL),
                                        'adult'            => $result_price_calculate['adult'],
                                        'child'            => $result_price_calculate['child'],
                                        'deta'             => $import_amount,
                                        'subtotal'         => $result_price_calculate['price'],
                                        'taxes'            => 0,
                                        'total'            => $result_price_calculate['price'],
                                        'price_per_person' => $price_per_person,
                                    ];
                                }
                            } else {
                                $service['import'] = 0;
                            }
                        }

                        $service['hotel']['class'] = isset($service['hotel']['typeclass']['translations']) ? $service['hotel']['typeclass']['translations'][0]['value'] : '';
                        $service['hotel']['color_class'] = isset($service['hotel']['typeclass']) ? $service['hotel']['typeclass']['color'] : '';
                    }
                    if ($service['type'] == 'service') {
                        if ($service_first_by_markup == 0) {
                            $service_first_by_markup = $this->getMarkupService($client_id, $service['object_id'], date('Y-m-d'));
                        }
                        //Rate protection
                        $new_date_in = $service['date_in'];
                        $new_date_in = DateTime::createFromFormat('d/m/Y', $new_date_in);

                        if (isset($service['service_rate']['service_rate_id'])) {
                            $service_rate_plans = ServiceRatePlan::where('service_rate_id', $service['service_rate']['service_rate_id'])->where('date_from', '<=', $new_date_in->format('Y-m-d'))->where('date_to', '>=', $new_date_in->format('Y-m-d'))->first();

                            if ($service_rate_plans) {
                                if ($service_rate_plans['flag_migrate'] == 1) {
                                    $flat_migrate = 1;
                                }
                            }
                        }

                        //Todo Si no tiene textos en idioma q no sea ingles, poner en ingles por defecto.
                        if (($service['service']['service_translations'][0]['name'] === null ||
                                $service['service']['service_translations'][0]['name'] === '')
                            && strtoupper($lang) !== 'EN'
                        ) {
                            $service['service']['service_translations'][0] =
                                ServiceTranslation::where('service_id', $service['service']['id'])
                                ->where('language_id', 2)->first();
                        }

                        $service['import'] = '';
                        if ($quote->operation === 'passengers') {
                            $result_price_calculate = $this->calculatePriceServiceService($service, $quote->id);
                            $service['import'] = $result_price_calculate;
                        }

                        $min_date = date('Y-m-d');

                        if (Auth::user()->user_type_id == 4) { // Client..
                            $days = (int)$service['service']['qty_reserve_client'];

                            if ($days > 0) {
                                $min_date = date('Y-m-d', strtotime('+' . $days . ' days'));
                            }

                            $service['min_date'] = $min_date;
                            $service['date_in_new'] = convertDate($service['date_in'], '/', '-', 1);

                            $service['validations'] = $this->validationsQuoteServices(
                                $service,
                                $quote->operation,
                                $client_id,
                                $quote->ranges,
                                $quote->id
                            );
                        }
                    }

                    $service['flag_migrate'] = $flat_migrate;
                }

                //Add Price dynamic
                foreach ($category['services'] as $service) {
                    $service['price_dynamic'] = 0;

                    if ($service['type'] == 'service') {
                        $flag_exclude_client = 0;
                        $service_rates = ServiceRate::where('service_id', $service['service']['id'])->where('status', 1)->get();
                        foreach ($service_rates as $service_rate) {
                            if ($service_rate->price_dynamic == 1) {

                                foreach ($service_rates as $service_rates_client) {
                                    $service_rate_association = ServiceRateAssociation::where('service_rate_id', $service_rates_client->id)
                                        ->where('object_id', $client_id)
                                        ->where('entity', 'Client')
                                        ->where('except', 0)
                                        ->first();
                                    if ($service_rate_association) {
                                        $flag_exclude_client = 1;
                                    }
                                }
                                if ($flag_exclude_client == 0) {
                                    $service['price_dynamic'] = 1;
                                }
                            }
                        }
                    }
                    if ($service['type'] == 'hotel') {
                        $flag_exclude_client = 0;
                        $service_rates = RatesPlans::where('hotel_id', $service['hotel']['id'])->where('status', 1)->get();
                        foreach ($service_rates as $service_rate) {
                            if ($service_rate->price_dynamic == 1) {

                                foreach ($service_rates as $service_rates_client) {
                                    $service_rate_association = RatePlanAssociation::where('rate_plan_id', $service_rates_client->id)
                                        ->where('object_id', $client_id)
                                        ->where('entity', 'Client')
                                        ->where('except', 0)
                                        ->first();
                                    if ($service_rate_association) {
                                        $flag_exclude_client = 1;
                                    }
                                }

                                if ($flag_exclude_client == 0) {
                                    $service['price_dynamic'] = 1;
                                }
                            }
                        }
                    }

                    if ($service['markup_regionalization'] == 0 && ($quote['is_multiregion'] == true)) {
                        if ($service['type'] == 'hotel') {
                            $markup_hotel = $this->getMarkupHotel($client_id, $service['hotel']['id'], $service['date_in'], false);
                            $service['markup_regionalization'] = $markup_hotel ? $markup_hotel : 0;
                        } else if ($service['type'] == 'service') {
                            $markup_service = $this->getMarkupService($client_id, $service['service']['id'], $service['date_in'], false);
                            $service['markup_regionalization'] = $markup_service ? $markup_service : 0;
                        }
                    }
                }
            }
            
            
            if ($service_first_by_markup == 0) {                
                $quote['markup_readonly'] = $this->getMarkupDefaul($client_id);
            }else{
                $quote['markup_readonly'] = $service_first_by_markup;
            }            

            return $quote;
        });

        $quote = $quote->toArray();
        $notes = [];

        if (!empty($quote) and count($quote) > 0) {
            foreach ($quote[0]['notes'] as $note) {
                if (is_null($note['parent_note_id'])) {
                    $notes[] = [
                        'id'              => $note['id'],
                        'comment'         => $note['comment'],
                        'status'          => $note['status'],
                        'quote_id'        => $note['quote_id'],
                        'user_name'       => $note['user']['name'],
                        'user_id'         => $note['user_id'],
                        'user_photo'      => $note['user']['photo'],
                        'created_at'      => $note['created_at'],
                        'responses'       => [],
                        'edit'            => false,
                        'create_response' => false,
                    ];
                }
            }

            foreach ($quote[0]['notes'] as $response) {
                if ($response['parent_note_id'] != null) {

                    foreach ($notes as $index => $note) {
                        if ($note['id'] == $response['parent_note_id']) {
                            $notes[$index]['responses'][] = [
                                'id'             => $response['id'],
                                'parent_note_id' => $response['parent_note_id'],
                                'comment'        => $response['comment'],
                                'status'         => $response['status'],
                                'quote_id'       => $response['quote_id'],
                                'user_name'      => $response['user']['name'],
                                'user_id'        => $response['user_id'],
                                'user_photo'     => $response['user']['photo'],
                                'created_at'     => $response['created_at'],
                                'edit'           => false,
                            ];
                        }
                    }
                }
            }

            $quote[0]['notes'] = $notes;

            foreach ($quote[0]['categories'] as $category) {
                foreach ($category['services'] as $service) {
                    $service['showQuantityPassengers'] = false;
                }
            }

            foreach ($quote[0]['passengers'] as $passenger) {
                $passenger['checked'] = false;
            }

            $_c = 0;
            foreach ($quote[0]['categories'] as $c) {

                $_s = 0;
                foreach ($c['services'] as $key => $service) {
                    $count_adults = 1;
                    $count_child = 1;
                    $quote[0]['categories'][$_c]['services'][$_s]['passengers_front'] = $quote[0]['passengers'];
                    for ($_p = 0; $_p < count($quote[0]['categories'][$_c]['services'][$_s]['passengers_front']); $_p++) {
                        $quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['checked'] = false;

                        if ($quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['type'] == 'ADL') {
                            $quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['index'] = $count_adults;
                            $count_adults++;
                        }
                        if ($quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['type'] == 'CHD') {
                            $quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['index'] = $count_child;
                            $count_child++;
                        }
                        for ($p = 0; $p < count($quote[0]['categories'][$_c]['services'][$_s]['passengers']); $p++) {
                            if (
                                $quote[0]['categories'][$_c]['services'][$_s]['passengers'][$p]['quote_passenger_id'] ==
                                $quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['id']
                            ) {
                                $quote[0]['categories'][$_c]['services'][$_s]['passengers_front'][$_p]['checked'] = true;
                            }
                        }
                    }

                    if ($service['type'] == 'service') {
                        $totalPax = $service['adult'] + $service['child'] + $service['infant'];
                        $new_date_in = $service['date_in'];
                        $new_date_in = DateTime::createFromFormat('d/m/Y', $new_date_in);
                        if ($service['service_rate'] == null) {

                            $service_rate = Service::where('id', $service['object_id'])->with([
                                'service_rate' => function ($query) use ($new_date_in, $totalPax) {
                                    $query->with([
                                        'service_rate_plans' => function ($query) use ($new_date_in) {
                                            $query->where('date_from', '<=', $new_date_in->format('Y-m-d'));
                                            $query->where('date_to', '>=', $new_date_in->format('Y-m-d'));
                                        },
                                    ])->with([
                                        'inventory' => function ($query) use ($new_date_in, $totalPax) {
                                            $query->where('date', '>=', $new_date_in->format('Y-m-d'));
                                            $query->where('date', '<=', $new_date_in->format('Y-m-d'));
                                            $query->where('locked', '=', 0);
                                            $query->where('inventory_num', '>=', $totalPax);
                                        },
                                    ]);
                                },
                            ])->first();

                            if (count($service_rate->service_rate) > 0) {
                                $on_request = (count($service_rate->service_rate[0]->inventory) > 0) ? 0 : 1;
                                $created_at = Carbon::now();
                                $_data = [
                                    'quote_service_id' => $service['id'],
                                    'service_rate_id'  => $service_rate->service_rate[0]->id,
                                    'created_at'       => $created_at,
                                    'updated_at'       => $created_at,
                                ];
                                $quote_service_rate_id = DB::table('quote_service_rates')->insertGetId($_data);
                                $_data['id'] = $quote_service_rate_id;
                                $service['service_rate'] = $_data;
                                $quote[0]['categories'][$_c]['services'][$_s]['on_request'] = $on_request;
                            }
                        } else {
                            $on_request = 0;
                            if ($new_date_in) {
                                $date = $new_date_in->format('Y-m-d');
                                $inventory = ServiceInventory::where(
                                    'service_rate_id',
                                    $service['service_rate']['service_rate_id']
                                )
                                    ->where('date', '>=', $date)
                                    ->where('date', '<=', $date)
                                    ->where('locked', '=', 0)
                                    ->where('inventory_num', '>=', $totalPax)->count();
                                if ($inventory == 0) {
                                    $on_request = 1;
                                }
                            }
                            $quote[0]['categories'][$_c]['services'][$_s]['on_request'] = $on_request;
                        }
                    }
                    $_s++;
                }

                $_c++;
            }

            foreach ($quote[0]['categories'] as $index_category => $category) {
                if (count($quote[0]['categories'][$index_category]['type_class']['translations']) == 0) {
                    $quote[0]['categories'][$index_category]['type_class']['translations'][0]['value'] = '-';
                }
            }

            $quote[0]['markets'] = $markets;

            $quote = $this->set_grouped($quote);
        }

        return Response::json($quote);
    }

    public function searchById(Request $request): JsonResponse
    {
        try {
            $paging = $request->input('page') ? $request->input('page') : 1;
            $limit = $request->input('limit') ? $request->input('limit') : 10;
            $querySearch = $request->input('queryCustom');
            $lang = ($request->has('lang')) ? $request->input('lang') : 'en';
            $filterBy = $request->input('filterBy');
            $destinations = $request->input('destinations');
            $client_id = $request->input('client');
            // $original_quote_id = $request->input('original_quote_id');

            if (empty($client_id)) {
                $client_id = $this->getClientId($request);
            }

            $language_id = Language::where('iso', $lang)->first()->id;

            // Base query with only the fields we need
            $quotes = Quote::select([
                'id',
                'date_in',
                'nights',
                'created_at',
                'markup',
                'user_id',
                'status',
                'estimated_price',
                'file_total_amount'
            ])->with([
                'user' => function ($query) {
                    $query->select(['id', 'name', 'email', 'code']);
                },
                'categories'
            ])->with([
                'destinations' => function ($query) use ($language_id) {
                    $query->select(['id', 'quote_id', 'state_id']);
                    $query->with([
                        'state' => function ($query) use ($language_id) {
                            $query->select(['id', 'iso', 'country_id']);
                            $query->with([
                                'country' => function ($query) use ($language_id) {
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select(['id', 'value', 'object_id']);
                                            $query->where('type', 'country');
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                }
            ]);

            $quotes->whereIn('status', [1, 3]);
            // $quotes->where('id', '!=', $original_quote_id);
            $quotes->where('deleted_at', null);

            if ($request->input('filterUserType') == 'C' and $client_id != '') {
                $usersClientIn = ClientSeller::where('client_id', '=', $client_id)->pluck('user_id');
                $quotes = $quotes->whereIn('user_id', $usersClientIn);
            }

            if (!empty($querySearch)) {
                // Búsqueda por ID que contenga los dígitos pasados
                $quotes = $quotes->where('id', 'like', $querySearch . '%');

                // También buscar por nombre que contenga el texto
                // $quotes = $quotes->orWhere('name', 'like', '%' . $querySearch . '%');

                // También buscar por código de reserva si existe
                // $quotes = $quotes->orWhere(function ($q) use ($querySearch) {
                //     $q->whereHas('reservation', function ($_q) use ($querySearch) {
                //         $_q->where('file_code', 'like', '%' . trim($querySearch) . '%');
                //     });
                // });
            }

            if ($filterBy == 'sent') {
                $quotes = $quotes->whereHas('logs', function ($query) {
                    $query->where('type', 'copy_to');
                });
            }

            if ($filterBy == 'received') {
                $quotes = $quotes->whereHas('logs', function ($query) {
                    $query->where('type', 'copy_from');
                });
            }

            if (!empty($destinations)) {
                $quotes = $quotes->whereHas('destinations', function ($query) use ($destinations) {
                    $query->whereIn('state_id', $destinations);
                });
            }

            $quotes = $quotes->orderBy('created_at', 'desc');

            $count = $quotes->count();

            if ($limit) {
                $quotes = $quotes->skip(($paging - 1) * $limit)->take($limit)->get();
            } else {
                $quotes = $quotes->get();
            }

            // Format the response with only the required fields
            $formattedQuotes = $quotes->map(function ($quote) {
                $country = null;
                $country_iso = null;
                if ($quote->destinations && $quote->destinations->isNotEmpty()) {
                    $destination = $quote->destinations->first();
                    if ($destination->state && $destination->state->country) {
                        $country = $destination->state->country->translations->first()->value;
                        $country_iso = $destination->state->country->iso;
                    }
                }

                // Calcular fecha de vencimiento basada en date_in
                $now = Carbon::now();
                $diff_days = $now->diffInDays(Carbon::parse($quote->date_in), false);

                $expiration_status = "Inicia hoy!";
                if ($diff_days < 0) {
                    $expiration_status = "Venció hace " . abs($diff_days) . " días";
                }
                if ($diff_days > 0) {
                    $expiration_status = "Vence en " . $diff_days . " días";
                }

                return [
                    'id' => $quote->id,
                    'number' => $quote->id,
                    'type_class_id' => $quote->categories[0]->type_class_id,
                    'user_name' => $quote->user ? $quote->user->name : null,
                    'user_code' => $quote->user ? $quote->user->code : null,
                    'date_in' => $quote->date_in ? Carbon::parse($quote->date_in)->format('d/m/Y') : null,
                    'nights' => $quote->nights,
                    'created_at' => $quote->created_at ? Carbon::parse($quote->created_at)->format('d/m/Y') : null,
                    'expiration_date' => $expiration_status,
                    'markup' => $quote->markup,
                    'total' => $quote->estimated_price ?? 0,
                    'country' => $country,
                    'country_iso' => $country_iso,
                    'status' => $quote->status
                ];
            });

            $data = [
                'success' => true,
                'data' => $formattedQuotes,
                'count' => $count,
                'pages' => $limit ? ceil($count / $limit) : 1,
                'current_page' => $paging,
                'per_page' => $limit
            ];

            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json([
                'type' => 'error',
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ]);
        }
    }

    /**
     * @param string $operation passengers | ranges
     * @param null $quote_id
     * @return Collection
     */
    public function validationsQuoteServices(QuoteService $service, string $operation, string $client_id, Collection $ranges, $quote_id = null)
    {

        $validations = collect();
        $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];
        //Todo: Estos datos se validan para cualquier hotel sin importar la operacion
        if ($service['type'] == 'hotel') {
            //Todo: Verifico si el hotel ha sido elimiado
            if (!empty($service['hotel']['deleted_at'])) {
                $validations->add([
                    'error'      => trans('validations.quotes.hotels.hotel_deleted'),
                    'range'      => '',
                    'validation' => true,
                    'error_gtm'  => trans('validations.quotes.hotels.hotel_deleted', [], 'en'),
                ]);
            }
            //Todo: Verifico si el hotel ha sido desactivado
            if ($service['hotel']['status'] == 0) {
                $validations->add([
                    'error'      => trans('validations.quotes.hotels.hotel_status_disabled'),
                    'range'      => '',
                    'validation' => true,
                    'error_gtm'  => trans('validations.quotes.hotels.hotel_status_disabled', [], 'en'),
                ]);
            }

            if (!empty($client_id)) {
                $year = Carbon::createFromFormat('d/m/Y', $service['date_in'])->format('Y');
                $service_looked = HotelClient::where('client_id', $client_id)
                    ->where('hotel_id', $service['hotel']['id'])
                    ->where('period', $year)
                    ->limit(1)
                    ->get(['id']);
                if ($service_looked->count() > 0) {
                    $validations->add([
                        'error'      => trans('validations.quotes.hotels.hotel_locked_client'),
                        'range'      => '',
                        'validation' => true,
                        'error_gtm'  => trans('validations.quotes.hotels.hotel_locked_client', [], 'en'),
                    ]);
                }
            }

            if (!$service->hyperguest_pull) {

                //Todo: Si el hotel no cuenta con tarifas de habitaciones
                if ($service->service_rooms->count() == 0) {
                    $validations->add([
                        'error'     => trans('validations.quotes.hotels.hotel_room_not_found'),
                        'range'     => '',
                        'error_gtm' => trans('validations.quotes.hotels.hotel_room_not_found', [], 'en'),
                    ]);
                }

                //Todo Validamos las tarifas de los hoteles y las habitaciones
                foreach ($service->service_rooms as $service_room) {
                    //Todo Validamos si la tarifa esta eliminada
                    if ($service_room->rate_plan_room === null) {
                        $validations->add([
                            'error' => trans(
                                'validations.quotes.hotels.hotel_rate_deleted',
                                ['room_id' => $service_room['rate_plan_room_id']]
                            ),
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.hotels.hotel_rate_deleted', [], 'en'),
                        ]);
                    } else {
                        //Todo Validamos si la tarifa esta desactivada
                        if ($service_room->rate_plan_room->rate_plan->status === 0) {
                            $validations->add([
                                'error' => trans(
                                    'validations.quotes.hotels.hotel_rate_status_disabled',
                                    ['room_id' => $service_room['rate_plan_room_id']]
                                ),
                                'range'      => '',
                                'validation' => true,
                                'error_gtm'  => trans('validations.quotes.hotels.hotel_rate_status_disabled', [], 'en'),
                            ]);
                        }

                        //Todo Validamos si la habitacion esta desactivada
                        if ($service_room->rate_plan_room->room->status === 0) {
                            $validations->add([
                                'error' => trans(
                                    'validations.quotes.hotels.hotel_room_status_disabled',
                                    ['room_id' => $service_room['rate_plan_room_id']]
                                ),
                                'range'      => '',
                                'validation' => true,
                                'error_gtm'  => trans('validations.quotes.hotels.hotel_room_status_disabled', [], 'en'),
                            ]);
                        }

                        //Todo Validamos la cantidad minima de noches
                        $date_in = Carbon::createFromFormat('d/m/Y', $service['date_in']);
                        $date_out = Carbon::createFromFormat('d/m/Y', $service['date_out']);
                        $date_to_minus_one = $date_out->copy()->subDay();
                        $calendars = RatesPlansCalendarys::where(
                            'rates_plans_room_id',
                            $service_room['rate_plan_room_id']
                        )
                            ->where('date', '>=', $date_in->format('Y-m-d'))
                            ->where('date', '<=', $date_to_minus_one->format('Y-m-d'))
                            ->get(['id', 'date', 'rates_plans_room_id', 'policies_rate_id']);
                        if ($calendars->count() > 0) {
                            $first_date = $calendars->min('date');
                            $last_date = $calendars->max('date');
                            if (count($calendars) != (int)$service['nights']) {
                                $validations->add([
                                    'error' => trans(
                                        'validations.quotes.hotels.hotel_rate_duplicate_in_calendary',
                                        [
                                            'rates_plans_room_id' => $service_room['rate_plan_room_id'],
                                            'first_date'       => Carbon::parse($first_date)->format('d/m/Y'),
                                            'last_date'        => Carbon::parse($last_date)->format('d/m/Y'),
                                        ]
                                    ),
                                    'range'      => '',
                                    'validation' => true,
                                    'error_gtm'  => trans(
                                        'validations.quotes.hotels.hotel_rate_duplicate_in_calendary',
                                        [
                                            'rates_plans_room_id' => $service_room['rate_plan_room_id'],
                                            'first_date'       => Carbon::parse($first_date)->format('d/m/Y'),
                                            'last_date'        => Carbon::parse($last_date)->format('d/m/Y'),
                                        ],
                                        'en'
                                    ),
                                ]);
                            }

                            $rates_plans_room_ids = $calendars->pluck('policies_rate_id');
                            $policies_rates = PoliciesRates::whereIn('id', $rates_plans_room_ids)
                                ->get(['id', 'name', 'min_length_stay']);
                            if ($policies_rates->count() > 0) {
                                //Todo Variable para agregar las noches minimas
                                $collection_minimum_nights = collect();
                                foreach ($policies_rates as $policies_rate) {
                                    $min_nights = $policies_rate['min_length_stay'];
                                    $collection_minimum_nights->add($min_nights);
                                }
                                //Todo Obtenemos el minimo de noches mayor
                                $min_nights_reservation = $collection_minimum_nights->max();
                                //Todo si la noches que se esta reservando es menor a la cantidad minima
                                if ((int)$service['nights'] < $min_nights_reservation) {
                                    $room_name = count($service_room['rate_plan_room']['room']['translations']) > 0 ? $service_room['rate_plan_room']['room']['translations'][0]['value'] : '';
                                    $room_and_rate_name = $room_name . ' (' . $service_room['rate_plan_room']['rate_plan']['name'] . ')';
                                    $validations->add([
                                        'error' => trans(
                                            'validations.quotes.hotels.hotel_minimum_number_nights_required',
                                            ['nights' => $min_nights_reservation, 'room_rate_name' => $room_and_rate_name]
                                        ),
                                        'range'      => '',
                                        'validation' => true,
                                        'error_gtm'  => trans(
                                            'validations.quotes.hotels.hotel_minimum_number_nights_required',
                                            ['nights' => $min_nights_reservation, 'room_rate_name' => $room_and_rate_name],
                                            'en'
                                        ),
                                    ]);
                                }
                            }
                        } else {
                            $validations->add([
                                'error' => trans(
                                    'validations.quotes.hotels.hotel_rate_no_in_calendary',
                                    ['rates_plans_room_id' => $service_room['rate_plan_room_id']]
                                ),
                                'range'      => '',
                                'validation' => true,
                                'error_gtm'  => trans(
                                    'validations.quotes.hotels.hotel_rate_no_in_calendary',
                                    ['rates_plans_room_id' => $service_room['rate_plan_room_id']],
                                    'en'
                                ),
                            ]);
                        }
                    }
                }
            } else {

                //Todo: Si el hotel no cuenta con tarifas de habitaciones
                if ($service->service_rooms_hyperguest->count() == 0) {
                    $validations->add([
                        'error'     => trans('validations.quotes.hotels.hotel_room_not_found'),
                        'range'     => '',
                        'error_gtm' => trans('validations.quotes.hotels.hotel_room_not_found', [], 'en'),
                    ]);
                } else {

                    if ($service->amount === null) {
                        $validations->add([
                            'error' => trans(
                                'validations.quotes.hotels.hotel_rate_deleted',
                                ['room_id' => $service->service_rooms_hyperguest[0]->rate_plan_id]
                            ),
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.hotels.hotel_rate_deleted', [], 'en'),
                        ]);
                    } else {

                        //Todo Validamos si la tarifa esta desactivada
                        if ($service->service_rooms_hyperguest[0]->rate_plan->status === 0) {
                            $validations->add([
                                'error' => trans(
                                    'validations.quotes.hotels.hotel_rate_status_disabled',
                                    ['room_id' => $service->service_rooms_hyperguest[0]->rate_plan_id]
                                ),
                                'range'      => '',
                                'validation' => true,
                                'error_gtm'  => trans('validations.quotes.hotels.hotel_rate_status_disabled', [], 'en'),
                            ]);
                        }

                        //Todo Validamos si la habitacion esta desactivada
                        if ($service->service_rooms_hyperguest[0]->room->status === 0) {
                            $validations->add([
                                'error' => trans(
                                    'validations.quotes.hotels.hotel_room_status_disabled',
                                    ['room_id' => $service->service_rooms_hyperguest[0]->room_id]
                                ),
                                'range'      => '',
                                'validation' => true,
                                'error_gtm'  => trans('validations.quotes.hotels.hotel_room_status_disabled', [], 'en'),
                            ]);
                        }
                    }
                }
            }
        }

        //Todo: Estos datos se validan para cualquier servicio sin importar la operacion
        if ($service['type'] == 'service') {
            //Todo: Verifico si el servicio ha sido eliminado
            if ($service['service']['deleted_at']) {
                $validations->add([
                    'error'      => trans('validations.quotes.services.service_deleted'),
                    'range'      => '',
                    'validation' => true,
                    'error_gtm'  => trans('validations.quotes.services.service_deleted', [], 'en'),
                ]);
            }
            //Todo: Verifico si el servicio ha sido desactivado
            if ($service['service']['status'] == 0) {
                $validations->add([
                    'error'      => trans('validations.quotes.services.service_status_disabled'),
                    'range'      => '',
                    'validation' => true,
                    'error_gtm'  => trans('validations.quotes.services.service_status_disabled', [], 'en'),
                ]);
            }

            // TODO: Verificar si el servicio está disponible en la fecha mínima.. (Sólo para clientes..)
            if ($service['min_date'] > $service['date_in_new']) {
                $validations->add([
                    'error' => trans('validations.quotes.services.service_min_date_error', [
                        'min_date' => $service['min_date'],
                    ]),
                    'range'      => '',
                    'validation' => true,
                    'error_gtm'  => trans('validations.quotes.services.service_min_date_error', [], 'en'),
                ]);
            }

            if (!empty($client_id)) {
                $year = Carbon::createFromFormat('d/m/Y', $service['date_in'])->format('Y');
                $service_looked = ServiceClient::where('client_id', $client_id)
                    ->where('service_id', $service['service']['id'])
                    ->where('period', $year)
                    ->limit(1)
                    ->get(['id']);
                if ($service_looked->count() > 0) {
                    $validations->add([
                        'error'      => trans('validations.quotes.services.service_locked_client'),
                        'range'      => '',
                        'validation' => true,
                        'error_gtm'  => trans('validations.quotes.services.service_min_date_error', [], 'en'),
                    ]);
                }
            }

            if ($service['hour_in'] == '' || $service['hour_in'] === null) {
                $date = Carbon::createFromFormat('d/m/Y', $service['date_in']);
                $day_name = $date->locale(App::getLocale())->dayName;
                $day_name_en = $date->clone()->locale('en')->dayName;

                // Simple pluralization for Spanish and Portuguese to fit "los/aos"
                if (App::getLocale() == 'es') {
                    if ($day_name == 'sábado' || $day_name == 'domingo') {
                        $day_name .= 's';
                    }
                } elseif (App::getLocale() == 'pt') {
                    if (str_contains($day_name, '-feira')) {
                        $day_name = str_replace('-feira', 's-feiras', $day_name);
                    } else {
                        $day_name .= 's';
                    }
                }

                $validations->add([
                    'error'      => trans('validations.quotes.services.service_schedule_empty', ['day' => $day_name]),
                    'range'      => '',
                    'validation' => false,
                    'error_gtm'  => trans('validations.quotes.services.service_schedule_empty', ['day' => $day_name_en], 'en'),
                ]);
            }
        }

        //Todo: Estos datos se validan para cualquier vuelo sin importar la operacion
        if ($service['type'] == 'flight') {
            //Todo: Verifico si el vuelo tiene el tipo (domestico o internacional)
            if (empty($service['code_flight'])) {
                $validations->add([
                    'error'      => trans('validations.quotes.flights.flight_type_empty'),
                    'range'      => '',
                    'validation' => true,
                    'error_gtm'  => trans('validations.quotes.flights.flight_type_empty', [], 'en'),
                ]);
            }
        }

        //Todo Cuando la operacion es por pasajeros, se necesita la cantidad de adultos y niños
        if ($operation === 'passengers') {
            if ($service['type'] == 'hotel') {

                if (!$service['hyperguest_pull']) {
                    //todo: Validaciones en la asignacion de cantidad de pasajeros de la habitacion

                    if (count($service['service_rooms']) == 0) {
                        $validations->add([
                            'error'      => 'validations.quotes.hotels.hotel_has_no_assigned_rate',
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.hotels.hotel_has_no_assigned_rate', [], 'en'),
                        ]);
                    }

                    //todo: end Validaciones en la asignacion de cantidad de pasajeros de la habitacion
                    //todo: Validacion de que no tiene asignado los pasajeros al hotel
                    if (count($service['passengers']) == 0) {
                        $validations->add([
                            'error'      => trans('validations.quotes.hotels.hotel_room_assign_passengers_to_the_room'),
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.hotels.hotel_room_assign_passengers_to_the_room', [], 'en'),
                        ]);
                    }
                    //todo: Validaciones en la asignacion de pasajeros
                    if (count($service['passengers']) > 0) {

                        $count_child = 0;
                        $count_adults = 0;
                        $quote_passengers = QuotePassenger::with('age_child')->where('quote_id', $quote_id)->get();

                        foreach ($quote_passengers as $quote_passenger) {

                            foreach ($service['passengers'] as $index => $passenger) {
                                if ($quote_passenger['id'] == $passenger['quote_passenger_id']) {
                                    $service['passengers'][$index]['type'] = $quote_passenger['type'];
                                    $service['passengers'][$index]['age'] = $quote_passenger['age_child'] ? $quote_passenger['age_child']['age'] : 0;
                                    $service['passengers'][$index]['selected'] = false;
                                }
                            }
                        }

                        foreach ($service['passengers'] as $index => $passenger) {
                            if ($passenger['type'] == 'ADL') {
                                $count_adults++;
                            }
                            if ($passenger['type'] == 'CHD') {
                                $count_child++;
                            }
                        }

                        //todo: Validacion si la edad del niño es mayor a la edad maxima de niño configurada en el hotel
                        // todo: el niño es tomado como adulto y ocupa un espacio en la habitacion
                        if ($count_child > 0) {

                            foreach ($service['passengers'] as $passenger) {
                                if ($passenger['type'] == 'CHD') {

                                    $edadMaximaNinos = 0;

                                    if ($service['hotel']['max_age_child'] > 0) {
                                        $edadMaximaNinos = $service['hotel']['max_age_child'];
                                    } else {
                                        if ($service['hotel']['max_age_teenagers'] > 0) {
                                            $edadMaximaNinos = $service['hotel']['max_age_teenagers'];
                                        }
                                    }

                                    if ($passenger['age'] > $edadMaximaNinos) {  //if ($passenger["age"] > $service["hotel"]["max_age_child"]) {

                                        // Log::debug("EDAD: ".$passenger["age"] .'>'. $edadMaximaNinos);

                                        $count_adults++;
                                        $count_child--;
                                    }
                                }
                            }
                        }

                        $num_adultos_validate = $count_adults;
                        $num_child_validate = $count_child;


                        if (count($service['service_rooms']) > 0) {
                            $max_adulto = $service['service_rooms'][0]['rate_plan_room']['room']['max_adults'];
                            if ($num_adultos_validate < $max_adulto) {

                                $num_adultos_faltantes = $max_adulto - $num_adultos_validate;

                                if ($num_child_validate > $num_adultos_faltantes) {

                                    $num_adultos_validate = $num_adultos_validate + $num_adultos_faltantes;

                                    $num_child_validate = ($num_child_validate - $num_adultos_faltantes);
                                } else {

                                    $num_adultos_validate = $num_adultos_validate + $num_child_validate;
                                    $num_child_validate = 0;
                                }
                            }
                        }

                        if (count($service['service_rooms']) > 0) {
                            //todo: Validacion de habitacion cantidad de adultos
                            if ($service['service_rooms'][0]['rate_plan_room']['room']['max_adults'] < $num_adultos_validate) {  //$count_adults
                                $max_adults = $service['service_rooms'][0]['rate_plan_room']['room']['max_adults'];
                                $validations->add([
                                    'error' => trans(
                                        'validations.quotes.hotels.hotel_room_max_adults',
                                        ['max_adults' => $max_adults]
                                    ),
                                    'range'      => '',
                                    'validation' => true,
                                    'error_gtm'  => trans('validations.quotes.hotels.hotel_room_max_adults', ['max_adults' => $max_adults], 'en'),
                                ]);
                            }
                            //todo: Validacion de habitacion cantidad de niños
                            if ($service['service_rooms'][0]['rate_plan_room']['room']['max_child'] < $num_child_validate) {  //$count_child
                                $max_children = $service['service_rooms'][0]['rate_plan_room']['room']['max_child'];
                                $validations->add([
                                    'error' => trans(
                                        'validations.quotes.hotels.hotel_room_max_children',
                                        ['max_children' => $max_children]
                                    ),
                                    'range'      => '',
                                    'validation' => true,
                                    'error_gtm'  => trans('validations.quotes.hotels.hotel_room_max_children', ['max_children' => $max_children], 'en'),
                                ]);
                            }
                            //todo: Validacion la cantidad de pasajeros asignados debe de ser menor o igual a la capacidad maxima de la habitacion
                            if (($num_adultos_validate + $num_child_validate) > $service['service_rooms'][0]['rate_plan_room']['room']['max_capacity']) {  //$count_adults + $count_child
                                $max_people = $service['service_rooms'][0]['rate_plan_room']['room']['max_capacity'];
                                $validations->add([
                                    'error' => trans(
                                        'validations.quotes.hotels.hotel_room_maximum_capacity_people',
                                        ['max_people' => $max_people]
                                    ),
                                    'range'      => '',
                                    'validation' => true,
                                    'error_gtm'  => trans('validations.quotes.hotels.hotel_room_maximum_capacity_people', ['max_people' => $max_people], 'en'),
                                ]);
                            }
                            //todo: Validaciones de la asignacion de ocupacion en el hotel
                            //todo: Validacion de ocupacion en simple
                            if ($service['service_rooms'][0]['rate_plan_room']['room']['room_type']['occupation'] == 1) {
                                if ($service['double'] > 0 || $service['triple'] > 0) {
                                    $validations->add([
                                        'error'      => trans('validations.quotes.hotels.hotel_occupancy_invalid'),
                                        'range'      => '',
                                        'validation' => true,
                                        'error_gtm'  => trans('validations.quotes.hotels.hotel_occupancy_invalid', [], 'en'),
                                    ]);
                                }
                            }
                            //todo: Validacion de ocupacion en double
                            if ($service['service_rooms'][0]['rate_plan_room']['room']['room_type']['occupation'] == 2) {
                                if ($service['single'] > 0 || $service['triple'] > 0) {
                                    $validations->add([
                                        'error'      => trans('validations.quotes.hotels.hotel_occupancy_invalid'),
                                        'range'      => '',
                                        'validation' => true,
                                        'error_gtm'  => trans('validations.quotes.hotels.hotel_occupancy_invalid', [], 'en'),
                                    ]);
                                }
                            }
                            //todo: Validacion de ocupacion en triple
                            if ($service['service_rooms'][0]['rate_plan_room']['room']['room_type']['occupation'] == 3) {
                                if ($service['single'] > 0 || $service['double'] > 0) {
                                    $validations->add([
                                        'error'      => trans('validations.quotes.hotels.hotel_occupancy_invalid'),
                                        'range'      => '',
                                        'validation' => true,
                                        'error_gtm'  => trans('validations.quotes.hotels.hotel_occupancy_invalid', [], 'en'),
                                    ]);
                                }
                            }
                        } else {
                            $validations->add([
                                'error'      => trans('validations.quotes.hotels.hotel_has_no_assigned_rate'),
                                'range'      => '',
                                'validation' => true,
                                'error_gtm'  => trans('validations.quotes.hotels.hotel_has_no_assigned_rate', [], 'en'),
                            ]);
                        }
                    }
                } else {

                    if (count($service['passengers']) == 0) {
                        $validations->add([
                            'error'      => trans('validations.quotes.hotels.hotel_room_assign_passengers_to_the_room'),
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.hotels.hotel_room_assign_passengers_to_the_room', [], 'en'),
                        ]);
                    }
                }
            }
            //Todo: Validamos los servicios
            if ($service['type'] == 'service') {
                //Todo: Verifico si el servicio tiene la capacidad de pasajeros permitido
                if ($service['adult'] > $service['service']['pax_max']) {
                    $validations->add([
                        'error' => trans(
                            'validations.quotes.services.service_maximum_pax_exceeded',
                            ['pax' => $service['service']['pax_max']]
                        ),
                        'range'      => '',
                        'validation' => true,
                        'error_gtm'  => trans('validations.quotes.services.service_maximum_pax_exceeded', ['pax' => $service['service']['pax_max']], 'en'),
                    ]);
                }

                if ($service['adult'] < $service['service']['pax_min']) {
                    $validations->add([
                        'error' => trans(
                            'validations.quotes.services.service_minimum_pax_exceeded',
                            ['pax' => $service['service']['pax_min']]
                        ),
                        'range'      => '',
                        'validation' => true,
                        'error_gtm'  => trans(
                            'validations.quotes.services.service_minimum_pax_exceeded',
                            ['pax' => $service['service']['pax_min']],
                            'en'
                        ),

                    ]);
                }

                //Todo: Verifico si el servicio permite niños
                if ($service['child'] > 0 && $service['service']['allow_child'] === 0) {
                    $validations->add([
                        'error'      => trans('validations.quotes.services.service_child_not_allowed'),
                        'range'      => '',
                        'validation' => true,
                        'error_gtm'  => trans('validations.quotes.services.service_child_not_allowed', [], 'en'),
                    ]);
                }

                $service['alerta_change_children_ages'] = false;

                //Todo: Verifico si el servicio permite niños
                if ($service['child'] > 0) {

                    // dd($service['passengers']->toArray());

                    if (count($service['passengers']) > 0) {
                        foreach ($service['passengers'] as $passenger) {

                            if ($passenger['passenger']['type'] == 'CHD') {

                                $services_age_children = $service['service']['children_ages'];

                                if ($service['child'] > 0 and count($services_age_children) > 0 and ($service['service']['allow_child'] === 1 or $service['service']['allow_infant'] === 1)) {
                                    if (count($services_age_children) === 0) {
                                        $validations['errors']->add([
                                            'error'     => trans('validations.quotes.services.service_child_not_insert'),
                                            'error_gtm' => trans('validations.quotes.services.service_child_not_insert', [], 'en'),
                                        ]);
                                    } else {
                                        $children_ages = $services_age_children[0];

                                        $age = $passenger['passenger']['age_child']; // edad del niño que se ha reseevado

                                        if (isset($age['age'])) {
                                            if (($children_ages->min_age <= $age['age'] and $children_ages->max_age >= $age['age'] and $service['service']['allow_child'] === 1) or
                                                ($service['service']['infant_min_age'] <= $age['age'] and $service['service']['infant_max_age'] >= $age['age'] and $service['service']['allow_infant'] === 1)
                                            ) {
                                                // $validate_age_child = false;
                                                $service['alerta_change_children_ages'] = false;
                                            } else {
                                                // $validate_age_child = true;
                                                $service['alerta_change_children_ages'] = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (isset($service['service_rate']['service_rate_id'])) {
                    $totalPax = $service['adult'] + $service['child'] + $service['infant'];
                    $date_in = Carbon::createFromFormat('d/m/Y', $service['date_in'])->format('Y-m-d');
                    $serviceRate = ServiceRate::where('id', $service['service_rate']['service_rate_id'])
                        ->with([
                            'service_rate_plans' => function ($query) use ($date_in, $totalPax) {
                                $query->select(['id', 'service_rate_id', 'price_adult', 'price_child']);
                                $query->where('date_from', '<=', $date_in)->where('date_to', '>=', $date_in);
                                $query->where('pax_from', '<=', $totalPax)->where('pax_to', '>=', $totalPax);
                                $query->where('status', 1);
                            },
                        ])
                        ->with([
                            'inventory' => function ($query) use ($date_in) {
                                $query->select([
                                    'id',
                                    'service_rate_id',
                                    'day',
                                    'date',
                                    'inventory_num',
                                    'total_booking',
                                    'total_canceled',
                                    'locked',
                                ]);
                                $query->where('date', '>=', $date_in);
                                $query->where('date', '<=', $date_in);
                            },
                        ])
                        ->get(['id'])
                        ->first();

                    //Todo: Verifico si el servicio tiene tarifa para el rango de pax
                    $rate_plan = $serviceRate->service_rate_plans->count();
                    if ($rate_plan === 0) {
                        $validations->add([
                            'error'      => trans('validations.quotes.services.service_not_found_rate'),
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.services.service_not_found_rate', [], 'en'),

                        ]);
                    }

                    $day_inventory_locked = false;
                    if ($serviceRate->inventory->count() > 0) {
                        if ($serviceRate->inventory[0]->locked == 1) {
                            $day_inventory_locked = true;
                        }
                    }

                    if ($day_inventory_locked) {
                        $validations->add([
                            'error' => trans(
                                'validations.quotes.services.service_day_availability',
                                ['date' => $date_in]
                            ),
                            'range'      => '',
                            'validation' => true,
                            'error_gtm'  => trans('validations.quotes.services.service_day_availability', [], 'en'),

                        ]);
                    }
                }
            }
        } else {

            //Todo: Validamos los servicios
            if ($service['type'] == 'service') {
                $date_in = Carbon::parse(Carbon::createFromFormat('d/m/Y', $service['date_in']))->format('Y-m-d');
                //Todo Cuando la operacion es por rangos, se recorre todos los rangos y se valida
                foreach ($ranges as $range) {
                    for ($i = $range['from']; $i <= $range['to']; $i++) {
                        //Todo: Verifico si el servicio tiene la capacidad de pasajeros permitido
                        if ($i > $service['service']['pax_max']) {
                            $validations->add([
                                'error' => trans(
                                    'validations.quotes.services.service_maximum_pax_exceeded',
                                    ['pax' => $service['service']['pax_max']]
                                ),
                                'range'      => $i . ' - ' . $i,
                                'validation' => true,
                                'error_gtm'  => trans('validations.quotes.services.service_maximum_pax_exceeded', ['pax' => $service['service']['pax_max']], 'en'),
                            ]);
                        }

                        if (isset($service['service_rate']['service_rate_id'])) {
                            $totalPax = $i;
                            $serviceRate = ServiceRate::where('id', $service['service_rate']['service_rate_id'])
                                ->with([
                                    'service_rate_plans' => function ($query) use (
                                        $date_in,
                                        $totalPax
                                    ) {
                                        $query->select(['id', 'service_rate_id', 'price_adult', 'price_child']);
                                        $query->where('date_from', '<=', $date_in)->where('date_to', '>=', $date_in);
                                        $query->where('pax_from', '<=', $totalPax)->where('pax_to', '>=', $totalPax);
                                        $query->where('status', 1);
                                    },
                                ])
                                ->with([
                                    'inventory' => function ($query) use ($date_in) {
                                        $query->select([
                                            'id',
                                            'service_rate_id',
                                            'day',
                                            'date',
                                            'inventory_num',
                                            'total_booking',
                                            'total_canceled',
                                            'locked',
                                        ]);
                                        $query->where('date', '>=', $date_in);
                                        $query->where('date', '<=', $date_in);
                                    },
                                ])
                                ->get(['id'])
                                ->first();

                            //Todo: Verifico si el servicio tiene tarifa para el rango de pax
                            $rate_plan = $serviceRate->service_rate_plans->count();
                            if ($rate_plan === 0) {
                                $validations->add([
                                    'error'      => trans('validations.quotes.services.service_not_found_rate'),
                                    'range'      => $i . ' - ' . $i,
                                    'validation' => true,
                                    'error_gtm'  => trans('validations.quotes.services.service_not_found_rate', [], 'en'),
                                ]);
                            }

                            $day_inventory_locked = false;
                            if ($serviceRate->inventory->count() > 0) {
                                if ($serviceRate->inventory[0]->locked == 1) {
                                    $day_inventory_locked = true;
                                }
                            }

                            if ($day_inventory_locked) {
                                $validations->add([
                                    'error' => trans(
                                        'validations.quotes.services.service_day_availability',
                                        ['date' => $date_in]
                                    ),
                                    'range'      => $i . ' - ' . $i,
                                    'validation' => true,
                                    'error_gtm'  => trans('validations.quotes.services.service_day_availability', [], 'en'),
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return $validations;
    }

    public function calculatePriceServiceService($service, $quote_id)
    {

        $service = $service->toArray();
        $passengers = QuotePassenger::with('age_child')->where('quote_id', $quote_id)->get()->toArray();
        foreach ($passengers as $id => $passenger) {

            $passengers[$id]['age'] = $passenger['age_child'] ? $passenger['age_child']['age'] : '';
        }

        $service = $this->calculatePriceServiceHere($service, $passengers);
        $price_ADL = 0;
        $total_amount_adult = 0;
        $total_amount_child = 0;
        $import_childres = [];
        foreach ($service['passengers'] as $passenger) {
            if ($passenger['type'] == 'ADL') {
                $total_amount_adult = $total_amount_adult + (float)roundLito($passenger['amount']);
                if ($passenger['amount'] > 0) {
                    $price_ADL = roundLito($passenger['amount']);
                }
            }

            if ($passenger['type'] == 'CHD') {
                $total_amount_child = $total_amount_child + (float)roundLito($passenger['amount']);
                $import_childres[] = [
                    'age'   => $passenger['age'],
                    'price' => $passenger['amount'],
                ];
            }
        }

        $sub_total = $total_amount_adult + $total_amount_child;

        return [
            'price_ADL'          => $price_ADL,
            'total_amount_adult' => $total_amount_adult,
            'total_amount_child' => $total_amount_child,
            'import_childres'    => $import_childres,
            'sub_total'          => $sub_total,
            'total_taxes'        => 0,
            'total_amount'       => $sub_total,
            'price_per_person'   => 0,
            // 'price_per_person' => roundLito($sub_total / count($service["passengers"]))
        ];
    }

    private function calculatePriceServiceHere($service, $passengers)
    {
        // dd($service);
        $price_adult = count($service['amount']) > 0 ? $service['amount'][0]['price_adult'] : 0;
        $price_child = count($service['amount']) > 0 ? $service['amount'][0]['price_child'] : 0;

        foreach ($service['passengers'] as $index => $passenger) {
            if ($passenger['passenger']['type'] == 'ADL') {
                $service['passengers'][$index]['amount'] = $price_adult;
            }
            if ($passenger['passenger']['type'] == 'CHD' and isset($service['service']['children_ages'][0])) {
                $age_child = $this->getAgeChild($passenger['passenger']['id'], $passengers);

                if (
                    $age_child >= $service['service']['children_ages'][0]['min_age'] &&
                    $age_child <= $service['service']['children_ages'][0]['max_age']
                ) {
                    $service['passengers'][$index]['amount'] = $price_child;
                }
                if ($age_child < $service['service']['children_ages'][0]['min_age']) {
                    $service['passengers'][$index]['amount'] = 0;
                }
                if ($age_child > $service['service']['children_ages'][0]['max_age']) {
                    $service['passengers'][$index]['amount'] = $price_adult;
                }
            }
        }

        foreach ($passengers as $index_passenger => $passenger) {
            $passengers[$index_passenger]['amount'] = 0;
            foreach ($service['passengers'] as $index_service_passenger => $service_passenger) {

                if (($passenger['id'] == $service_passenger['passenger']['id']) and isset($service_passenger['amount'])) {
                    $passengers[$index_passenger]['amount'] = $service_passenger['amount'];
                }
            }
        }
        $service['passengers'] = $passengers;

        return $service;
    }

    private function getAgeChild($id_passenger, $passengers)
    {
        foreach ($passengers as $passenger) {
            if ($passenger['id'] == $id_passenger) {
                return $passenger['age'];
            }
        }
    }

    public function calculatePriceRoom($service, $quote_id)
    {

        $service = $service->toArray();
        $passengers = QuotePassenger::with('age_child')->where('quote_id', $quote_id)->get()->toArray();

        $rates = NULL;
        // if($service['hyperguest_pull'] == 1)
        // {
        //     //  $rates = $this->calculatePriceHotelRoomHyperguestPull($service, $passengers);
        //     //  $rates = $this->calculatePriceHotelRoom($service, $passengers);
        //     //  dd($rates);
        // }else{
        //     $rates = $this->calculatePriceHotelRoom($service, $passengers);
        //     // dd(json_encode($rates));
        // }

        $rates = $this->calculatePriceHotelRoom($service, $passengers);

        return $rates;
    }

    public function set_grouped($quote)
    {
        $grouped_object = [];
        $grouped_array = [];
        foreach ($quote[0]['categories'] as $c_ => $category) {
            foreach ($category['services'] as $s_ => $service) {
                $quote[0]['categories'][$c_]['services'][$s_]['grouped_code'] = null;
                $quote[0]['categories'][$c_]['services'][$s_]['grouped_show'] = true;
                $quote[0]['categories'][$c_]['services'][$s_]['grouped_type'] = null;
                if ($service['type'] === 'hotel') {
                    $locked = ($service['locked']) ? 1 : 0;
                    $quote[0]['categories'][$c_]['services'][$s_]['grouped_code'] = $service['quote_category_id'] . '_' . str_replace(
                        '/',
                        '',
                        $service['date_in']
                    ) . '_' //. $service['nights'] . '_'
                        . $service['object_id'] . '_' . $locked;
                    $quote[0]['categories'][$c_]['services'][$s_]['grouped_show'] = false;
                    $quote[0]['categories'][$c_]['services'][$s_]['grouped_type'] = 'row';
                    if (!isset($grouped_object[$quote[0]['categories'][$c_]['services'][$s_]['grouped_code']])) {
                        $grouped_array[] = [
                            'code'              => $quote[0]['categories'][$c_]['services'][$s_]['grouped_code'],
                            'quote_service_ids' => [$service['id']],
                        ];
                        $grouped_object[$quote[0]['categories'][$c_]['services'][$s_]['grouped_code']] = count($grouped_array) - 1;
                    } else {
                        $grouped_array[$grouped_object[$quote[0]['categories'][$c_]['services'][$s_]['grouped_code']]]['quote_service_ids'][] = $service['id'];
                    }
                }
            }
        }

        // dd($quote[0]['operation']);
        $peoples = 0;
        $people_adults = 0;
        $people_child = 0;
        if ($quote[0]['operation'] == 'passengers') {
            $peoples = $quote[0]['people'][0]['adults'] + $quote[0]['people'][0]['child'];
            $people_adults = $quote[0]['people'][0]['adults'];
            $people_child = $quote[0]['people'][0]['child'];
        }
        $accommodations = $quote[0]['accommodation']['single'] + $quote[0]['accommodation']['double'] + $quote[0]['accommodation']['triple'];

        foreach ($quote[0]['categories'] as $c_ => $category) {
            $new_services = [];
            foreach ($category['services'] as $s_ => $service) {
                if ($service['type'] === 'hotel') {
                    if (isset($grouped_object[$service['grouped_code']])) {
                        //Todo si el primero coincide hay que crear la cabeza del grupo
                        if (count($grouped_array[$grouped_object[$service['grouped_code']]]['quote_service_ids']) > 0) {
                            if ($grouped_array[$grouped_object[$service['grouped_code']]]['quote_service_ids'][0] === $service['id']) {
                                //Todo dibujar la cabeza
                                $header_service_ = $service;
                                //Todo el id del grupo para evitar errore del front en los nodos:
                                $header_service_['id'] = $grouped_object[$service['grouped_code']] + 1;
                                $header_service_['type'] = 'group_header';
                                $header_service_['grouped_show'] = false;
                                $header_service_['selected'] = false;
                                $header_service_['grouped_type'] = 'header';
                                $header_service_['group_quote_service_id'] = $service['id'];
                                //Todo acomodo
                                $header_service_['single'] = 0;
                                $header_service_['double'] = 0;
                                $header_service_['triple'] = 0;
                                $header_service_['double_child'] = 0;
                                $header_service_['triple_child'] = 0;
                                $header_service_['total_accommodations'] = 1;
                                //Todo Rooms
                                $header_service_['service_rooms'] = [];
                                $header_service_['validations'] = [];

                                $count_sgl = 0;
                                $count_dbl = 0;
                                $count_tpl = 0;

                                $count_passengers = 0;
                                $count_adults = 0;
                                $count_childs = 0;
                                $text_passengers = '';
                                //Todo Recorrido de hijos
                                foreach ($grouped_array[$grouped_object[$service['grouped_code']]]['quote_service_ids'] as $quote_service_id_) {
                                    $count_validations = 0;
                                    foreach ($category['services'] as $service_) {
                                        if ($quote_service_id_ === $service_['id']) {
                                            // Acomodos
                                            $header_service_['single'] += (int)$service_['single'];
                                            $header_service_['double'] += (int)$service_['double'];
                                            $header_service_['triple'] += (int)$service_['triple'];
                                            $count_sgl += (int)$service_['single'];
                                            $count_dbl += (int)$service_['double'];
                                            $count_tpl += (int)$service_['triple'];
                                            $header_service_['double_child'] += (int)$service_['double_child'];
                                            $header_service_['triple_child'] += (int)$service_['triple_child'];

                                            if ((int)$service_['single'] > 0 || (int)$service_['double'] > 0 || (int)$service_['triple'] > 0) {
                                                $count_passengers += (int)$service_['adult'] + (int)$service_['child'];
                                                $count_adults += (int)$service_['adult'];
                                                $count_childs += (int)$service_['child'];
                                                $text_passengers .= $service_['single'] . '-' . $service_['double'] . '-' . $service_['triple'] . '<br>';
                                            }

                                            if (count($service_['validations']) > 0) {
                                                $count_validations += count($service_['validations']);
                                            }
                                            // Rooms
                                            foreach ($service_['service_rooms'] as $service_room_) {
                                                $room_already = 0;
                                                foreach ($header_service_['service_rooms'] as $header_service_room_) {
                                                    if ($header_service_room_['rate_plan_room_id'] == $service_room_['rate_plan_room_id']) {
                                                        $room_already++;
                                                    }
                                                }
                                                if ($room_already == 0) {
                                                    $header_service_['service_rooms'][] = $service_room_;
                                                }
                                            }
                                            // Rooms Hyperguest
                                            foreach ($service_['service_rooms_hyperguest'] as $service_room_) {
                                                $room_already = 0;
                                                foreach ($header_service_['service_rooms_hyperguest'] as $header_service_room_) {
                                                    if ($header_service_room_['room_id'] == $service_room_['room_id']) {
                                                        $room_already++;
                                                    }
                                                }
                                                if ($room_already == 0) {
                                                    $header_service_['service_rooms_hyperguest'][] = $service_room_;
                                                }
                                            }
                                            break;
                                        }
                                    }
                                    //Todo Si hay validaciones en los hijos agrego a la cabecera
                                    if ($count_validations > 0) {
                                        $header_service_['validations'][] = [
                                            'error'      => trans('validations.quotes.hotels.hotel_has_observations_in_rooms'),
                                            'range'      => '',
                                            'validation' => true,
                                        ];
                                    }
                                }
                                if (($count_sgl + $count_dbl + $count_tpl) == 0) {
                                    $header_service_['validations'][] = [
                                        'error'      => trans('validations.quotes.hotels.hotel_does_not_have_assigned_accommodation'),
                                        'range'      => '',
                                        'validation' => true,
                                    ];
                                }

                                // if (($quote[0]['operation'] == "passengers") and ($count_passengers != $peoples)) {
                                //     $header_service_['validations'][] = [
                                //         'error' => trans('validations.quotes.hotels.hotel_does_not_have_assigned_total_passengers'),  //." {$count_passengers} !=  {$peoples} => {$text_passengers}"
                                //         'range' => '',
                                //         'validation' => true
                                //     ];
                                // }

                                // if (($quote[0]['operation'] == "passengers") and ($count_adults != $people_adults)) {
                                //     $header_service_['validations'][] = [
                                //         'error' => trans('validations.quotes.hotels.hotel_not_total_adultos'),  //." {$count_passengers} !=  {$peoples} => {$text_passengers}"
                                //         'range' => '',
                                //         'validation' => true
                                //     ];
                                // }

                                if (($quote[0]['operation'] == 'passengers') and ($count_childs != $people_child)) {
                                    $header_service_['validations'][] = [
                                        'error' => trans('validations.quotes.hotels.hotel_not_total_children'),
                                        //." {$count_passengers} !=  {$peoples} => {$text_passengers}"
                                        'range'      => '',
                                        'validation' => true,
                                    ];
                                }

                                if (($quote[0]['operation'] == 'ranges') and ($accommodations != ($count_sgl + $count_dbl + $count_tpl))) {
                                    $header_service_['validations'][] = [
                                        'error'      => trans('validations.quotes.hotels.hotel_needs_to_complete_accommodation'),
                                        'range'      => '',
                                        'validation' => true,
                                    ];
                                }

                                $new_services[] = $header_service_;
                            }
                        } else {
                            $service['grouped_code'] = null;
                            $service['grouped_show'] = true;
                            $service['grouped_type'] = null;
                        }
                    }
                }
                $new_services[] = $service;
            }

            $quote[0]['categories'][$c_]['services'] = $new_services;
        }

        return $quote;
    }


    public function updateDateInService(Request $request)
    {
        $index_service = $request->post('index_service');
        $quote_id = $request->post('quote_id');
        $quote_service_ids = $request->post('quote_service_ids');
        $date_in = $request->post('date_in');
        $move_services = (int) $request->post('move_services') ?? 0;
        $days = $request->post('days');

        //Todo Validamos la fecha
        if (!Carbon::createFromFormat('Y-m-d', $date_in)->isCurrentDay() and Carbon::createFromFormat(
            'Y-m-d',
            $date_in
        )->isPast()) {
            return response()->json(trans('validations.quotes.date_must_be_greater_the_current'), 500);
        }

        //Todo Obtenemos el cliente segun el tipo de usuario
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first()->client_id;
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        foreach ($quote_service_ids as $quote_service_id) {
            DB::transaction(function () use ($quote_service_id, $quote_id, $date_in, $index_service, $move_services, $days) {
                //Todo buscamos la cotizacion
                $quote_ = Quote::find($quote_id);
                if ($index_service == 0) {
                    $service_initial = DB::table('quote_services')->where('id', $quote_service_id)->first();

                    //Todo Agregamos log a la cotizacion
                    $description = "Actualizó la fecha general y de todo el itinerario de: " . $quote_->date_in . " a: " . $date_in;
                    $this->store_history_logs($quote_id, [
                        [
                            "type"          => "update",
                            "slug"          => "update_date_general",
                            "previous_data" => $quote_->date_in,
                            "current_data"  => $date_in,
                            "description"   => $description
                        ]
                    ]);

                    $quote_->date_in = $this->getMinDateQuoteServices(
                        $quote_service_id,
                        $service_initial->quote_category_id,
                        $date_in
                    );
                    if ($quote_->date_in) {
                        $quote_->save();
                        $date_initial = $service_initial->date_in;
                        $services = DB::table('quote_services')->where(
                            'quote_category_id',
                            $service_initial->quote_category_id
                        )
                            ->where('parent_service_id', null)
                            ->where('locked', 0)
                            ->orderBy('date_in')
                            ->orderBy('order')
                            ->get();
                        for ($i = 0; $i < count($services); $i++) {
                            if ($i == 0) {
                                if ($services[$i]->extension_id != null) {
                                    $this->updateDateInExtension($services[$i]->id, $date_in, $services[$i]->order);
                                } else {
                                    if ($services[$i]->type == 'service' or $services[$i]->type == 'flight') {
                                        DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                            'date_in'  => $date_in,
                                            'date_out' => $date_in
                                        ]);
                                    }
                                    if ($services[$i]->type == 'hotel') {
                                        DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                            'date_in'  => $date_in,
                                            'date_out' => Carbon::parse($date_in)->addDays($services[$i]->nights)->format('Y-m-d')
                                        ]);
                                    }
                                }
                            } else {
                                if ($move_services === 1) {
                                    $date_out_service = DB::table('quote_services')->where(
                                        'id',
                                        $services[$i]->id
                                    )->first()->date_in;
                                    $days_difference = Carbon::parse($date_initial)->diffInDays(Carbon::parse($date_out_service));
                                    $date_in_service = Carbon::parse($date_in)->addDays($days_difference)->format('Y-m-d');
                                    if ($services[$i]->extension_id != null) {
                                        $this->updateDateInExtension(
                                            $services[$i]->id,
                                            $date_in_service,
                                            $services[$i]->order
                                        );
                                    } else {
                                        if ($services[$i]->type == 'service' or $services[$i]->type == 'flight') {
                                            DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                                'date_in'  => $date_in_service,
                                                'date_out' => $date_in_service
                                            ]);
                                        }
                                        if ($services[$i]->type == 'hotel') {
                                            DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                                'date_in'  => $date_in_service,
                                                'date_out' => Carbon::parse($date_in_service)->addDays($services[$i]->nights)->format('Y-m-d')
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $service = DB::table('quote_services')->where('id', $quote_service_id)->first();

                    if ($move_services === 1) {
                        $date_in = Carbon::parse(strtotime($service->date_in))->addDays($days)->format("Y-m-d");
                    }

                    $quote_->date_in = $this->getMinDateQuoteServices(
                        $quote_service_id,
                        $service->quote_category_id,
                        $date_in
                    );
                    if ($quote_->date_in) {
                        $quote_->save();
                    }

                    if ($service->extension_id != null) {
                        $service_code_ = '';
                        $extension_ = Package::find($service->extension_id);
                        if ($extension_) {
                            $service_code_ = $extension_->code;
                        }

                        $type_class_id_ = QuoteCategory::find($service->quote_category_id)->type_class_id;

                        $this->store_history_logs($quote_id, [
                            [
                                "type"          => "update",
                                "slug"          => "update_date",
                                "previous_data" => $service->date_in,
                                "current_data"  => json_encode([
                                    "type_class_id" => $type_class_id_,
                                    "type_service"  => "extension",
                                    "date_in"       => $date_in,
                                    "object_id"     => $service->extension_id,
                                    "service_code"  => $service_code_,
                                ]),
                                "description" => "Actualizó fecha del servicio"
                            ]
                        ]);

                        $this->updateDateInExtension($service->id, $date_in, $service->order);
                    } else {
                        if ($service->type == 'service' or $service->type == 'flight') {

                            $service_code_ = '';
                            if ($service->type === 'flight') {
                                $service_code_ = $service->origin . ' > ' . $service->destiny;
                            } else {
                                $service_ = Service::find($service->object_id);
                                if ($service_) {
                                    $service_code_ = $service_->aurora_code;
                                }
                            }
                            $type_class_id_ = QuoteCategory::find($service->quote_category_id)->type_class_id;
                            $this->store_history_logs($quote_id, [
                                [
                                    "type"          => "update",
                                    "slug"          => "update_date",
                                    "previous_data" => $service->date_in,
                                    "current_data"  => json_encode([
                                        "type_class_id" => $type_class_id_,
                                        "type_service"  => $service->type,
                                        "date_in"       => $date_in,
                                        "object_id"     => $service->object_id,
                                        "service_code"  => $service_code_,
                                    ]),
                                    "description" => "Actualizó fecha del servicio"
                                ]
                            ]);

                            DB::table('quote_services')->where('id', $quote_service_id)->update([
                                'date_in'  => $date_in,
                                'date_out' => $date_in
                            ]);
                        }
                        if ($service->type == 'hotel') {

                            $service_code_ = '';
                            $hotel_ = Hotel::where('id', $service->object_id)->with('channel')->first();
                            if ($hotel_) {
                                $service_code_ = $hotel_->channel[0]->code;
                            }
                            $type_class_id_ = QuoteCategory::find($service->quote_category_id)->type_class_id;
                            $this->store_history_logs($quote_id, [
                                [
                                    "type"          => "update",
                                    "slug"          => "update_date",
                                    "previous_data" => $service->date_in,
                                    "current_data"  => json_encode([
                                        "type_class_id" => $type_class_id_,
                                        "type_service"  => "hotel",
                                        "date_in"       => $date_in,
                                        "object_id"     => $service->object_id,
                                        "service_code"  => $service_code_,
                                    ]),
                                    "description" => "Actualizó fecha del servicio"
                                ]
                            ]);

                            DB::table('quote_services')->where('id', $quote_service_id)->update([
                                'date_in'  => $date_in,
                                'date_out' => Carbon::parse($date_in)->addDays($service->nights)->format('Y-m-d')
                            ]);
                        }
                    }
                    // Si es extensión y toca reemplazar el padre de ese grupo dado el cambio de fecha.
                    // 2020-08-05
                    if ($service->parent_service_id != '' && $service->parent_service_id != null) {
                        $_service_parent = QuoteService::find($service->parent_service_id);
                        if (strtotime($date_in) <= strtotime(convertDate($_service_parent->date_in, '/', '-', 1))) {
                            DB::table('quote_services')->where('id', $service->id)
                                ->update([
                                    "extension_id"      => $_service_parent->extension_id,
                                    "parent_service_id" => null
                                ]);

                            $_service_parent->extension_id = null;
                            $_service_parent->parent_service_id = $service->id;
                            $_service_parent->save();
                            DB::table('quote_services')->where('parent_service_id', $_service_parent->id)
                                ->update([
                                    "parent_service_id" => $service->id
                                ]);
                        }
                    }
                }
                $service_rate = DB::table('quote_service_rates')->where('quote_service_id', $quote_service_id);

                if ($service_rate->count() > 0) {
                    $service_rate = $service_rate->first();
                    $available = DB::table('service_inventories')->where(
                        'service_rate_id',
                        $service_rate->service_rate_id
                    )->where('date', '>=', $date_in)
                        ->where('date', '<=', $date_in)
                        ->where('locked', '=', 0)
                        ->where('inventory_num', '>', 1)->get();

                    if ($available->count() == 0) {
                        DB::table('quote_services')->where('id', $quote_service_id)->update([
                            'on_request' => 1,
                        ]);
                    } else {
                        DB::table('quote_services')->where('id', $quote_service_id)->update([
                            'on_request' => 0,
                        ]);
                    }
                }
            });

            // $quote = Quote::where('id', $quote_id)->first();

            // if ($quote->operation == "passengers") {
            //     $this->updateAmountService($quote_service_id, $client_id, $quote_id);
            // }
        }


        $quote = Quote::where('id', $quote_id)->first();

        if ($quote->operation == "passengers") {
            $this->updateAmountAllServices($quote_id, $client_id);
        }

        return response()->json("fecha de servicio Actualizada", 200);
    }

    public function updateNightsService(Request $request)
    {
        $nights = $request->post('nights');
        $quote_service_ids = $request->post('quote_service_ids');
        $quote_id = $request->post('quote_id');
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first()->client_id;
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }
        foreach ($quote_service_ids as $quote_service_id) {
            DB::transaction(function () use ($quote_service_id, $nights) {
                $service = DB::table('quote_services')->where('id', $quote_service_id)->first();
                DB::table('quote_services')->where('id', $quote_service_id)->update([
                    'nights'   => $nights,
                    'date_out' => Carbon::parse($service->date_in)->addDays($nights)
                ]);
            });
            // $this->updateAmountService($quote_service_id, $client_id, $quote_id);
        }

        // $this->updateAmountAllServices($quote_id, $client_id);
        $quote = Quote::where('id', $quote_id)->first();
        if ($quote->operation == "passengers") {
            $this->updateAmountAllServices($quote_id, $client_id);
        }
    }

    public function updateNameQuote(Request $request)
    {
        $quote_id = $request->post('quote_id');
        $name = $request->post('name');

        DB::transaction(function () use ($quote_id, $name) {
            $quote_ = Quote::find($quote_id);

            $this->store_history_logs($quote_id, [
                [
                    "type"          => "update",
                    "slug"          => "update_name",
                    "previous_data" => $quote_->name,
                    "current_data"  => $name,
                    "description"   => "Actualizó el nombre"
                ]
            ]);

            $quote_->name = $name;
            $quote_->save();
        });

        return Response::json(['success' => true, 'message' => 'Nombre de cotizacion actualizado']);
    }

    public function saveAs(Request $request)
    {
        $quote_id = $request->post('quote_id');
        $new_name_quote = $request->post('new_name_quote');
        DB::transaction(function () use ($quote_id, $new_name_quote) {
            $quote_editing = DB::table('quotes')->where('id', $quote_id)->first();
            DB::table('quotes')->where('id', $quote_id)->update([
                'name'       => $new_name_quote,
                'updated_at' => Carbon::now(),
            ]);
            $quote_new_id = DB::table('quotes')->insertGetId([
                'code'            => $quote_editing->code,
                'name'            => $new_name_quote,
                'date_in'         => $quote_editing->date_in,
                'cities'          => $quote_editing->cities,
                'nights'          => $quote_editing->nights,
                'user_id'         => $quote_editing->user_id,
                'service_type_id' => $quote_editing->service_type_id,
                'status'          => 1,
                'discount'        => $quote_editing->discount,
                'discount_detail' => $quote_editing->discount_detail,
                'created_at'      => Carbon::now(),
                'operation'       => $quote_editing->operation,
                'language_id'     => $quote_editing->language_id
            ]);

            $quote_log = QuoteLog::where('quote_id', $quote_id)->where('type', 'editing_quote')->first();
            if ($quote_log) {
                $this->move_history_logs($quote_id, $quote_log->object_id);

                $quote_log->object_id = $quote_new_id;
                $quote_log->save();
            }

            $this->replaceQuote($quote_id, $quote_new_id, true);
        });


        return Response::json(['success' => true]);
    }

    public function forcefullyDestroy($id)
    {
        DB::transaction(function () use ($id) {
            $categories = QuoteCategory::where('quote_id', $id)->get();
            foreach ($categories as $c) {
                DB::table('quote_dynamic_sale_rates')->where('quote_category_id', $c->id)->delete();
                $services = QuoteService::where('quote_category_id', $c->id)->get();
                foreach ($services as $s) {
                    DB::table('quote_service_rates')->where('quote_service_id', $s->id)->delete();
                    DB::table('quote_service_rooms')->where('quote_service_id', $s->id)->delete();
                    DB::table('quote_service_amounts')->where('quote_service_id', $s->id)->delete();
                    DB::table('quote_service_passengers')->where('quote_service_id', $s->id)->delete();
                    $s->delete();
                }
                $c->delete();
            }


            $quote_distributions = QuoteDistribution::where('quote_id', $id)->get();
            foreach ($quote_distributions as $quote_distribution) {
                // DB::table('quote_distribution_passengers')->where('quote_distribution_id', $quote_distribution->id)->delete();
                QuoteDistributionPassenger::where('quote_distribution_id', $quote_distribution->id)->delete();
            }
            // DB::table('quote_distributions')->where('quote_id', $id)->delete();
            QuoteDistribution::where('quote_id', $id)->delete();

            DB::table('quote_passenger_examples')->where('quote_id', $id)->delete();


            DB::table('quote_passengers')->where('quote_id', $id)->delete();


            DB::table('quote_people')->where('quote_id', $id)->delete();
            DB::table('quote_notes')->where('quote_id', $id)->delete();
            DB::table('quote_ranges')->where('quote_id', $id)->delete();
            DB::table('quote_logs')->where('quote_id', $id)->delete();
            DB::table('quote_history_logs')->where('quote_id', $id)->delete();
            DB::table('quote_destinations')->where('quote_id', $id)->delete();
            DB::table('quote_accommodations')->where('quote_id', $id)->delete();
            DB::table('quote_age_child')->where('quote_id', $id)->delete();
            $quote = Quote::find($id);
            if ($quote) {
                $quote->forceDelete();
            }
        });

        $response = ['success' => true];

        return Response::json($response);
    }


    public function updateDateInQuote(Request $request)
    {
        $quote_id = $request->post('quote_id');
        $date_in = $request->post('date_in');

        if (!Carbon::createFromFormat('Y-m-d', $date_in)->isCurrentDay() and Carbon::createFromFormat(
            'Y-m-d',
            $date_in
        )->isPast()) {
            return response()->json(trans('validations.quotes.date_must_be_greater_the_current'), 500);
        }

        DB::transaction(function () use ($quote_id, $date_in) {

            $quote_ = Quote::find($quote_id);
            $previous_date_in = $quote_->date_in;
            $quote_->date_in = $date_in;
            $quote_->save();

            $this->updateDateInServicesInAllCategories($quote_id);

            $description = "Actualizó la fecha general y de todo el itinerario de: " . $previous_date_in . " a: " . $date_in;
            $this->store_history_logs($quote_id, [
                [
                    "type"          => "update",
                    "slug"          => "update_date_general",
                    "previous_data" => $previous_date_in,
                    "current_data"  => $date_in,
                    "description"   => $description
                ]
            ]);
        });

        return Response::json(['success' => true, 'message' => 'Fecha de cotizacion actualizada']);
    }

    public function updatePeople(Request $request)
    {
        try {
            $people = $request->post('people');
            $quote_id = $request->post('quote_id');
            $passengers = $request->post('passengers');
            $client_id = $this->getClientId($request);


            $quoteDistributions = QuoteDistribution::with('passengers')->where(
                "quote_id",
                $quote_id
            )->orderBy('occupation')->get();
            //validamos si se esta haciendo cambios de cantidad de pasajeros
            if (count($quoteDistributions) > 0) {
                //validamos si se ha cambiado la cantidad adultos o niños
                $adultSave = $quoteDistributions->sum('adult');
                $childSave = $quoteDistributions->sum('child');
                if ($people["adults"] != $adultSave or $people["child"] != $childSave) {
                    QuoteDistribution::where("quote_id", $quote_id)->delete();
                    $quoteDistributions = [];
                }
            }


            $_paxs = 0;
            foreach ($passengers as $key => $value) {
                if ($value['id'] > 0) {
                    $_paxs += 1;
                }
            }

            $items = [
                'first_name',
                'last_name',
                'gender',
                'birthday',
                'document_number',
                'doctype_iso',
                'country_iso',
                'email',
                'phone',
                'notes'
            ];

            $quantity = 0;

            if (count($passengers) == 0) {
                for ($i = 0; $i < (int)$people['adults']; $i++) {
                    $passengers[$quantity]['id'] = null;

                    foreach ($items as $k => $v) {
                        $passengers[$quantity][$v] = '';
                    }

                    $quantity++;
                }

                for ($i = 0; $i < (int)$people['child']; $i++) {
                    $passengers[$quantity]['id'] = null;

                    foreach ($items as $k => $v) {
                        $passengers[$quantity][$v] = '';
                    }

                    $quantity++;
                }
            }

            DB::transaction(function () use ($people, $passengers, $quote_id, $client_id, $_paxs, $quoteDistributions) {

                $histories = [];
                if ($people["adults"] == 0) {    // or $_paxs == 0
                    DB::table('quote_passengers')->where('quote_id', $quote_id)->delete();
                    if ($people["adults"] == 0) {
                        $categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();
                        foreach ($categories as $category) {
                            // DB::table('quote_services')->where('quote_category_id', $category->id)->where('type',
                            //     'hotel')->update([
                            //     'single' => 1,
                            //     'double' => 1,
                            //     'triple' => 1
                            // ]);
                            $quote_services = DB::table('quote_services')->where(
                                'quote_category_id',
                                $category->id
                            )->get();

                            foreach ($quote_services as $service) {
                                DB::table('quote_service_amounts')->where('quote_service_id', $service->id)->delete();
                                DB::table('quote_service_passengers')->where(
                                    'quote_service_id',
                                    $service->id
                                )->delete();
                            }
                        }

                        DB::table('quotes')->where('id', $quote_id)->update([
                            'operation' => 'ranges'
                        ]);


                        $quote_ranges_count = QuoteRange::where('quote_id', $quote_id)->count();
                        if ($quote_ranges_count === 0) {
                            $new_quote_range = new QuoteRange();
                            $new_quote_range->quote_id = $quote_id;
                            $new_quote_range->from = 1;
                            $new_quote_range->to = 1;
                            $new_quote_range->save();
                        }
                        array_push(
                            $histories,
                            [
                                "type"          => "update",
                                "slug"          => "update_type_pax",
                                "previous_data" => "Paxs",
                                "current_data"  => "Ranges",
                                "description"   => "Actualizó el tipo de cotización de Paxs a Rangos"
                            ]
                        );
                    }

                    if ($_paxs == 0 and $people["adults"] > 0) {
                        DB::table('quotes')->where('id', $quote_id)->update([
                            'operation' => 'passengers'
                        ]);
                    }
                } else {
                    $category_ids = DB::table('quote_categories')->where('quote_id', $quote_id)->pluck('id');
                    DB::table('quote_dynamic_sale_rates')->whereIn('quote_category_id', $category_ids)->delete();
                    DB::table('quote_ranges')->where('quote_id', $quote_id)->delete();

                    $quote_ = Quote::find($quote_id);

                    if ($quote_->operation !== 'passengers') {
                        array_push(
                            $histories,
                            [
                                "type"          => "update",
                                "slug"          => "update_type_pax",
                                "previous_data" => "Ranges",
                                "current_data"  => "Paxs",
                                "description"   => "Actualizó el tipo de cotización de Rangos a Paxs"
                            ]
                        );
                    }

                    $quote_->operation = 'passengers';
                    $quote_->save();
                }

                $quote_age_child_list = [];
                $exist = DB::table('quote_people')->where('quote_id', $quote_id)->count();
                if ($exist > 0) {

                    $quote_people_ = QuotePeople::where('quote_id', $quote_id)->first();

                    if ($quote_people_->adults !== (int)$people["adults"]) {
                        array_push(
                            $histories,
                            [
                                "type"          => "update",
                                "slug"          => "update_general_adults",
                                "previous_data" => $quote_people_->adults,
                                "current_data"  => $people["adults"],
                                "description"   => "Actualizó la cantidad de adultos general"
                            ]
                        );
                    }
                    if ($quote_people_->child !== (int)$people["child"]) {
                        array_push(
                            $histories,
                            [
                                "type"          => "update",
                                "slug"          => "update_general_childs",
                                "previous_data" => $quote_people_->child,
                                "current_data"  => $people["child"],
                                "description"   => "Actualizó la cantidad de niños general"
                            ]
                        );
                    }

                    DB::table('quote_people')->where('quote_id', $quote_id)->update([
                        'adults'     => (int)$people["adults"],
                        'child'      => (int)$people["child"],
                        'quote_id'   => $quote_id,
                        "updated_at" => \Carbon\Carbon::now()
                    ]);

                    $ages = DB::table('quote_age_child')->where('quote_id', $quote_id)->get();
                    $limite = (int)$people['child'] - count($ages);

                    foreach ($ages as $k => $v) {
                        if ($k >= (int)$people['child']) {
                            $age_child = DB::table('quote_age_child')->where('id', $v->id)->first();
                            DB::table('quote_passengers')->where('quote_id', $quote_id)->where('quote_age_child_id', $age_child->id)->update(['quote_age_child_id' => null]);
                            DB::table('quote_age_child')->where('id', $v->id)->delete();
                        } else {

                            if ($people["ages_child"] and isset($people["ages_child"][$k])) {
                                $age = $people["ages_child"][$k]['age'];
                                DB::table('quote_age_child')->where('id', $v->id)->update(['age' => $age]);
                            }

                            array_push($quote_age_child_list, $v->id);
                        }
                    }

                    // DB::table('quote_age_child')->where('quote_id', $quote_id)->delete();

                    if ($limite > 0) {
                        for ($i = 0; $i < $limite; $i++) {
                            $age = 0;
                            if ($people["ages_child"] and isset($people["ages_child"][$i])) {
                                $age = $people["ages_child"][$i]['age'];
                            }

                            $quote_age_child_id = DB::table('quote_age_child')->insertGetId([
                                'age'      => $age,
                                'quote_id' => $quote_id
                            ]);
                            array_push($quote_age_child_list, $quote_age_child_id);
                        }
                    }
                    // else{
                    //     if($people["ages_child"] and is_array($people["ages_child"])){

                    //     }

                    // }
                } else {

                    array_push(
                        $histories,
                        [
                            "type"          => "store",
                            "slug"          => "store_general_adults",
                            "previous_data" => 0,
                            "current_data"  => $people["adults"],
                            "description"   => "Agregó cantidad general de adultos"
                        ]
                    );
                    if ($people["child"] > 0) {
                        array_push(
                            $histories,
                            [
                                "type"          => "store",
                                "slug"          => "store_general_childs",
                                "previous_data" => 0,
                                "current_data"  => $people["child"],
                                "description"   => "Agregó cantidad general de niños"
                            ]
                        );
                    }

                    DB::table('quote_people')->where('quote_id', $quote_id)->insert([
                        'adults'     => (int)$people["adults"],
                        'child'      => (int)$people["child"],
                        'quote_id'   => $quote_id,
                        "created_at" => \Carbon\Carbon::now()
                    ]);
                    if ((int)$people["child"] > 0) {
                        for ($i = 0; $i < (int)$people['child']; $i++) {
                            $quote_age_child_id = DB::table('quote_age_child')->insertGetId([
                                'age'      => 0,
                                'quote_id' => $quote_id
                            ]);
                            array_push($quote_age_child_list, $quote_age_child_id);
                        }
                    }
                }

                $categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();

                $remaining_adults = (int)$people['adults'];
                $remaining_children = (int)$people['child'];
                foreach ($passengers as $key => $passenger) {
                    $p_type = strtoupper($passenger['type'] ?? '');
                    $should_keep = false;
                    $type = $p_type;
                    $quote_age_child_id = null;

                    if ($p_type === 'ADL') {
                        if ($remaining_adults > 0) {
                            $should_keep = true;
                            $remaining_adults--;
                            $type = 'ADL';
                        }
                    } elseif ($p_type === 'CHD') {
                        if ($remaining_children > 0) {
                            $should_keep = true;
                            $remaining_children--;
                            $type = 'CHD';
                            $quote_age_child_id = array_splice($quote_age_child_list, 0, 1); // asignamos la edad del ni??o
                            $quote_age_child_id = $quote_age_child_id ? $quote_age_child_id[0] : null;
                        }
                    } elseif ($p_type === '') {
                        // Fallback: fill adults first, then children.
                        if ($remaining_adults > 0) {
                            $should_keep = true;
                            $remaining_adults--;
                            $type = 'ADL';
                        } elseif ($remaining_children > 0) {
                            $should_keep = true;
                            $remaining_children--;
                            $type = 'CHD';
                            $quote_age_child_id = array_splice($quote_age_child_list, 0, 1);
                            $quote_age_child_id = $quote_age_child_id ? $quote_age_child_id[0] : null;
                        }
                    } else {
                        // Preserve other types (e.g., INF) without affecting adult/child counts.
                        $should_keep = true;
                    }

                    if ($should_keep) {
                        $birthday = $passenger["birthday"] ? $passenger["birthday"] : null;
                        if ($birthday == '0000-00-00') {
                            $birthday = null;
                        }

                        if ($passenger["id"] != null) {
                            DB::table('quote_passengers')->where('quote_id', $quote_id)->where(
                                'id',
                                $passenger["id"]
                            )->update([
                                'first_name'         => $passenger["first_name"],
                                'last_name'          => $passenger["last_name"],
                                'gender'             => $passenger["gender"] ? $passenger["gender"] : null,
                                'birthday'           => $birthday,
                                'document_number'    => $passenger["document_number"],
                                'doctype_iso'        => $passenger["doctype_iso"],
                                'country_iso'        => $passenger["country_iso"],
                                'email'              => $passenger["email"],
                                'phone'              => $passenger["phone"],
                                'notes'              => $passenger["notes"],
                                'quote_id'           => $quote_id,
                                'type'               => $type,
                                "updated_at"         => \Carbon\Carbon::now(),
                                "quote_age_child_id" => $quote_age_child_id
                            ]);
                        } else {
                            DB::table('quote_passengers')->insert([
                                'first_name'         => $passenger["first_name"],
                                'last_name'          => $passenger["last_name"],
                                'gender'             => $passenger["gender"] ? $passenger["gender"] : null,
                                'birthday'           => $birthday,
                                'document_number'    => $passenger["document_number"],
                                'doctype_iso'        => $passenger["doctype_iso"],
                                'country_iso'        => $passenger["country_iso"],
                                'email'              => $passenger["email"],
                                'phone'              => $passenger["phone"],
                                'notes'              => $passenger["notes"],
                                'quote_id'           => $quote_id,
                                'type'               => $type,
                                "created_at"         => \Carbon\Carbon::now(),
                                "quote_age_child_id" => $quote_age_child_id
                            ]);
                        }
                    } else {
                        DB::table('quote_service_passengers')->where('quote_passenger_id', $passenger["id"])->delete();
                        DB::table('quote_passengers')->where('quote_id', $quote_id)->where(
                            'id',
                            $passenger["id"]
                        )->delete();
                    }
                }

                if (count($quoteDistributions) == 0) {
                    //generamos la tabla quoteDistributions en base a los nuevos cambios que se han hecho o si es que esta tabla esta varia tambien.
                    $quotePeople = QuotePeople::where("quote_id", $quote_id)->first();
                    $quotePassengers = QuotePassenger::where("quote_id", $quote_id)->orderBy('id')->get()->toArray();
                    $quoteAccommodation = QuoteAccommodation::where("quote_id", $quote_id)->first();
                    $quoteDistributionsChange = $this->generateQuoteDistributions(
                        $quotePassengers,
                        $quoteAccommodation->single,
                        $quoteAccommodation->double,
                        $quoteAccommodation->triple,
                        $quotePeople->adults,
                        $quotePeople->child
                    );
                    $this->saveOccupationPassengersHotel($quote_id, $quoteDistributionsChange);
                    $quoteDistributions = QuoteDistribution::with('passengers')->where(
                        "quote_id",
                        $quote_id
                    )->orderBy('occupation')->get();
                }

                //ahora la acomodacion de pasajeros la aremos desde esta funcion
                $this->setAccommodationInHotels(
                    $categories,
                    (int)$people['adults'],
                    (int)$people['child'],
                    $quoteDistributions
                );
                // aqui ya no se hace la acomodacion en hoteles
                $this->updateListServicePassengersGeneral($quote_id, (int)$people["adults"], (int)$people["child"]);


                $this->updateAmountAllServices($quote_id, $client_id);
                $this->store_history_logs($quote_id, $histories);
            });

            $passengers = DB::table('quote_passengers')->where('quote_id', $quote_id)->get();

            return \response()->json($passengers, 200);
        } catch (\Exception $ex) {
            // return $this->throwError($ex);
            return [
                'file'     => $ex->getFile(),
                'line'     => $ex->getLine(),
                'detail'   => $ex->getMessage(),
                'message'  => $ex->getMessage(),
                'type'     => 'error',
                'success'  => false,
                'response' => 'ERR',
            ];
        }
    }

    public function updateQuoteAssignment(Request $request)
    {

        $quote_id = $request->post('quote_id');
        $dataPassengerAssing = [
            "quote_people"         => QuotePeople::where('quote_id', $quote_id)->first()->toArray(),
            "quote_age_child"      => QuoteAgeChild::where('quote_id', $quote_id)->get()->toArray(),
            "quote_passengers"     => QuotePassenger::where('quote_id', $quote_id)->get()->toArray(),
            "quote_accommodations" => QuoteAccommodation::where('quote_id', $quote_id)->first()->toArray()
        ];

        DB::table('quotes')->where('id', $quote_id)->update([
            'data_type_passenger' => json_encode($dataPassengerAssing)
        ]);

        return Response::json(['success' => true, 'message' => 'update quote']);
    }

    public function deleteServices($quote_id, Request $request)
    {
        $services = $request->post('services');
        DB::transaction(function () use ($services, $quote_id) {
            $hasHotel = false;
            $histories = [];
            foreach ($services as $service) {
                if ($service['type'] == "hotel") {
                    $hasHotel = true;
                }
                QuoteServiceRate::where('quote_service_id', $service["id"])->forceDelete();
                QuoteServicePassenger::where('quote_service_id', $service["id"])->forceDelete();
                QuoteServiceAmount::where('quote_service_id', $service["id"])->forceDelete();
                $quote_service_rooms = DB::table('quote_service_rooms')->where(
                    'quote_service_id',
                    $service["id"]
                )->get();
                foreach ($quote_service_rooms as $quote_service_room) {
                    QuoteServiceRoom::where('id', $quote_service_room->id)->delete();
                }

                $quote_service_rooms = DB::table('quote_service_rooms_hyperguest')->where(
                    'quote_service_id',
                    $service["id"]
                )->get();
                foreach ($quote_service_rooms as $quote_service_room) {
                    QuoteServiceRoomHyperguest::where('id', $quote_service_room->id)->delete();
                }


                if ($service["extension_id"] != null) {
                    $service_extensions = DB::table('quote_services')
                        ->where('parent_service_id', $service["id"])
                        ->orderBy('date_in', 'asc')
                        ->orderBy('order', 'asc')
                        ->get();

                    $id_new_father = "";
                    foreach ($service_extensions as $i => $service_extension) {
                        if ($i == 0) {
                            $id_new_father = $service_extension->id;
                            QuoteService::where('id', $service_extension->id)
                                ->update([
                                    "extension_id"      => $service["extension_id"],
                                    "parent_service_id" => null
                                ]);
                        } else {
                            QuoteService::where('id', $service_extension->id)
                                ->update([
                                    "parent_service_id" => $id_new_father
                                ]);
                        }
                    }
                }
                QuoteService::where('id', $service["id"])->forceDelete();

                $service_code_ = '';
                if ($service["type"] === 'hotel') {
                    $service_code_ = ((isset($service["code"]))) ? $service["code"] : $service["hotel"]["channel"][0]["code"];
                }
                if ($service["type"] === 'service') {
                    $service_code_ = ((isset($service["code"]))) ? $service["code"] : $service["service"]["aurora_code"];
                }
                if ($service["type"] === 'flight') {
                    $service_code_ = ((isset($service["code"]))) ? $service["code"] : $service["code_flight"] . ' - ' . $service["origin"] . ' -> ' . $service["destiny"];
                }

                $type_class = QuoteCategory::find($service["quote_category_id"]);
                if ($type_class) {
                    $histories[] = [
                        "type"          => "destroy",
                        "slug"          => "destroy_service",
                        "previous_data" => "",
                        "current_data"  => json_encode([
                            "type_class_id" => $type_class->type_class_id,
                            "type_service"  => $service["type"],
                            "object_id"     => $service["object_id"],
                            "service_code"  => $service_code_,
                            "date_in"       => $service["date_in"]
                        ]),
                        "description" => "Eliminó servicio"
                    ];
                }
            }

            if ($hasHotel) {
                // ya no debe de actualizar la ocupacion de la cabecera
                // $this->updateOccupationRoomHotelByQuote($quote_id);
            }

            if (count($histories) > 0) {
                $this->store_history_logs($quote_id, $histories);
            }
        });

        return response()->json("servicios Eliminados", 200);
    }

    public function deletePassenger($passenger_id)
    {
        DB::transaction(function () use ($passenger_id) {
            DB::table('quote_service_passengers')->where('quote_passenger_id', $passenger_id)->delete();
            DB::table('quote_passengers')->where('id', $passenger_id)->delete();
        });

        return response()->json("Pasajero Eliminado Exitosamente", 200);
    }

    public function updateAgeChild(Request $request)
    {
        $age_child = $request->input('age_child');

        DB::transaction(function () use ($age_child) {

            DB::table('quote_age_child')->where('quote_id', $age_child["quote_id"])->where(
                'id',
                $age_child["id"]
            )->update([
                'age' => $age_child["age"]
            ]);
        });

        return response()->json("Edad de nino actualizada", 200);
    }

    public function createOrDeleteCategory(Request $request)
    {
        $operation = $request->post('operation');
        $quote_id = $request->post('quote_id');
        $category_id = $request->post('category_id');
        $source_category_id = $request->post('source_category_id') ?? 'programming';

        $services = [];
        $quote_category_id = 0;

        if ($operation === "new") {
            if ($source_category_id != 'programming') {
                $services = DB::table('quote_services')->where('quote_category_id', $source_category_id)
                    ->whereNotIn('type', ['hotel'])->get();
            }
        }

        DB::transaction(function () use ($quote_id, $category_id, $operation, $services, &$quote_category_id) {
            if ($operation == "new") {

                $quote_category_id = DB::table('quote_categories')->insertGetId([
                    'quote_id'      => $quote_id,
                    'type_class_id' => $category_id,
                    'created_at'    => \Carbon\Carbon::now()
                ]);

                $this->store_history_logs($quote_id, [
                    [
                        "type"          => "store",
                        "slug"          => "store_category",
                        "previous_data" => "",
                        "current_data"  => $category_id,
                        "description"   => "Agregó categoría"
                    ]
                ]);

                $adults = 0;
                $child = 0;
                $quote = DB::table('quotes')->where('id', $quote_id)->first();
                $package_id = DB::table('quote_logs')->where('quote_id', $quote_id)
                    ->whereIn('type', ['editing_package', 'from_package'])->first();

                if ($package_id != null) {
                    $package_id = $package_id->object_id;
                }

                if ($package_id == null) {
                    $package_id = $quote->package_id;
                }

                if ($quote->operation == "passengers") {
                    $quote_people = DB::table('quote_people')->where('quote_id', $quote_id)->first();

                    $adults = $quote_people->adults;
                    $child = $quote_people->child;
                }

                if ($package_id != null) {
                    $package_plan_rate = DB::table('package_plan_rates')
                        ->where('package_id', $package_id)
                        ->where('service_type_id', $quote->service_type_id)
                        ->whereNull('deleted_at')->first();

                    $package_plan_rate_category = DB::table('package_plan_rate_categories')
                        ->where('package_plan_rate_id', $package_plan_rate->id)
                        ->where('type_class_id', $category_id)->whereNull('deleted_at')->first();

                    if ($package_plan_rate_category != null) {

                        $package_services = DB::table('package_services')
                            ->where('package_plan_rate_category_id', $package_plan_rate_category->id)
                            ->whereNull('deleted_at')
                            ->orderBy('date_in')
                            ->orderBy('order');

                        $_date_in = $quote->date_in;
                        $difference_days = 0;
                        $_i = 0;

                        // Si hay servicios, se debe agregar sólo los hoteles..
                        if (count($services) > 0) {
                            $package_services = $package_services->whereIn('type', ['hotel'])->get();
                            $new_services = collect([]);
                            $first_date = $package_services[0]->date_in;

                            foreach ($package_services as $index_package_service => $package_service) {
                                $date_service_in = Carbon::parse($package_service->date_in);
                                $date_service_out = Carbon::parse($package_service->date_out);

                                if ($package_service->type == 'hotel') {
                                    $nigths = ($date_service_in->diffInDays($date_service_out) == 0) ? 1 : $date_service_in->diffInDays($date_service_out);
                                } else {
                                    $nigths = 0;
                                }

                                if ($_i > 0) {
                                    $difference_days = Carbon::parse($first_date)->diffInDays(Carbon::parse($package_service->date_in));
                                }

                                $date_in = Carbon::parse($_date_in)->addDays($difference_days)->format('Y-m-d');
                                $date_out = Carbon::parse($_date_in)->addDays($difference_days + $nigths)->format('Y-m-d');

                                $new_services->push($package_service);
                                $new_services[$index_package_service]->date_in = $date_in;
                                $new_services[$index_package_service]->date_out = $date_out;

                                $_i++;
                            }

                            $package_services = $new_services->concat($services);
                            $package_services = collect($package_services)->sortBy('order')->values();
                        } else {
                            $package_services = $package_services->get();
                        }

                        $difference_days = 0;
                        $_i = 0;

                        foreach ($package_services as $index_package_service => $package_service) {
                            $date_service_in = Carbon::parse($package_service->date_in);
                            $date_service_out = Carbon::parse($package_service->date_out);

                            if ($package_service->type == 'hotel') {
                                $nigths = ($date_service_in->diffInDays($date_service_out) == 0) ? 1 : $date_service_in->diffInDays($date_service_out);
                            } else {
                                $nigths = 0;
                            }

                            if ($_i > 0) {
                                $difference_days = Carbon::parse($package_services[0]->date_in)->diffInDays(Carbon::parse($package_service->date_in));
                            }

                            $date_in = Carbon::parse($_date_in)->addDays($difference_days)->format('Y-m-d');
                            $date_out = Carbon::parse($_date_in)->addDays($difference_days + $nigths)->format('Y-m-d');

                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $quote_category_id,
                                'type'              => $package_service->type,
                                'object_id'         => $package_service->object_id,
                                'order'             => $index_package_service,
                                'date_in'           => $date_in,
                                'date_out'          => $date_out,
                                'nights'            => $nigths,
                                'adult'             => $adults, // (int)$s['adult']
                                'child'             => $child,
                                'infant'            => 0,
                                'single'            => 0,
                                'double'            => 0,
                                'triple'            => 0,
                                'triple_active'     => 0,
                                'code_flight'       => $package_service->code_flight,
                                'origin'            => $package_service->origin,
                                'destiny'           => $package_service->destiny,
                                'created_at'        => Carbon::now()->format('Y-m-d H:i:s'),
                                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s')
                            ]);

                            //Servicios
                            if ($package_service->type == 'service') {
                                $package_service_rates = DB::table('package_service_rates')
                                    ->where('package_service_id', $package_service->id)
                                    ->whereNull('deleted_at')
                                    ->get();

                                if ($package_service_rates->count() > 0) {
                                    foreach ($package_service_rates as $package_service_rate) {
                                        DB::table('quote_service_rates')->insert([
                                            'quote_service_id' => $new_service_id,
                                            'service_rate_id'  => $package_service_rate->service_rate_id,
                                            'created_at'       => Carbon::now()->format('Y-m-d H:i:s'),
                                            "updated_at"       => Carbon::now()->format('Y-m-d H:i:s')
                                        ]);
                                    }
                                }
                            }

                            //Hoteles
                            if ($package_service->type == 'hotel') {

                                //Calcular la cantidad de habitaciones dobles segun el numero de pax
                                $total_rooms_double_hotel = ceil(((int)$adults + (int)$child) / 2);
                                //End
                                DB::table('quote_services')->where('id', $new_service_id)->update([
                                    "double" => $total_rooms_double_hotel
                                ]);
                                $package_service_rooms = DB::table('package_service_rooms')
                                    ->whereNull('deleted_at')
                                    ->where('package_service_id', $package_service->id)->get();

                                if ($package_service_rooms->count() > 0) {
                                    foreach ($package_service_rooms as $package_service_room) {
                                        DB::table('quote_service_rooms')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'rate_plan_room_id' => $package_service_room->rate_plan_room_id,
                                            'created_at'        => Carbon::now()->format('Y-m-d H:i:s'),
                                            "updated_at"        => Carbon::now()->format('Y-m-d H:i:s')
                                        ]);
                                    }
                                }
                            }
                            $_i++;
                        }
                    }
                }
            }
            if ($operation == "delete") {
                $quote_category = DB::table('quote_categories')->where('quote_id', $quote_id)->where(
                    'type_class_id',
                    $category_id
                )->pluck('id');

                $quote_services = DB::table('quote_services')->whereIn('quote_category_id', $quote_category)->get();

                foreach ($quote_services as $service) {
                    if ($service->type == "hotel") {
                        DB::table('quote_service_rooms')->where('quote_service_id', $service->id)->delete();
                        DB::table('quote_service_amounts')->where('quote_service_id', $service->id)->delete();
                    }
                    if ($service->type == "service") {
                        DB::table('quote_service_rates')->where('quote_service_id', $service->id)->delete();
                        DB::table('quote_service_amounts')->where('quote_service_id', $service->id)->delete();
                    }
                    DB::table('quote_services')->where('id', $service->id)->delete();
                }
                $category_ids = DB::table('quote_categories')->where('quote_id', $quote_id)->where(
                    'type_class_id',
                    $category_id
                )->pluck('id');
                DB::table('quote_dynamic_sale_rates')->whereIn('quote_category_id', $category_ids)->delete();
                DB::table('quote_categories')->whereIn('id', $category_ids)->where(
                    'quote_id',
                    $quote_id
                )->where('type_class_id', $category_id)->delete();

                $this->store_history_logs($quote_id, [
                    [
                        "type"          => "destroy",
                        "slug"          => "destroy_category",
                        "previous_data" => "",
                        "current_data"  => $category_id,
                        "description"   => "Eliminó categoría"
                    ]
                ]);
            }
        });

        return response()->json([
            "success" => true,
            "message" => ($operation == "new") ? "Categoría agregada correctamente" : "Categoría eliminada correctamente",
            "new_category_id" => $quote_category_id ?? 0,
            "delete_category_id" => $category_id ?? 0,
        ], 200);
    }

    public function occupationPassengersHotel(Request $request)
    {

        $single = $request->input('single');
        $double = $request->input('double');
        $triple = $request->input('triple');
        $adults = $request->input('adults');
        $child = $request->input('child');


        $quote_id = $request->input('quote_id');

        $quoteAccommodation = QuoteAccommodation::where("quote_id", $quote_id)->first();
        $quotePeople = QuotePeople::where("quote_id", $quote_id)->first();
        $quoteDistributions = QuoteDistribution::with('passengers')->where(
            "quote_id",
            $quote_id
        )->orderBy('order')->get();
        $quotePassengers = QuotePassenger::where("quote_id", $quote_id)->orderBy('id')->get()->toArray();

        if (count($quoteDistributions) > 0) {

            //validamos si se ha cambiado
            if ($quoteAccommodation->single != $single or $quoteAccommodation->double != $double or $quoteAccommodation->triple != $triple) {
                $quoteDistributions = [];
            } else {
                //validamos si se ha cambiado la cantidad adultos o niños
                $adultSave = $quoteDistributions->sum('adult');
                $childSave = $quoteDistributions->sum('child');
                if ($quotePeople->adults != $adultSave or $quotePeople->child != $childSave) {
                    $quoteDistributions = [];
                }
            }
        } else {
            $quoteDistributions = [];
        }

        $quoteDistributionsNew = [];
        if (count($quoteDistributions) == 0) {

            $quoteDistributionsNew = $this->generateQuoteDistributions(
                $quotePassengers,
                $single,
                $double,
                $triple,
                $adults,
                $child
            );
        } else {

            $countAdul = 0;
            $countChild = 0;
            $name = '';
            $quote_passengers = [];
            foreach ($quotePassengers as $passenger) {

                $name = '';
                if ($passenger['first_name'] or $passenger['last_name']) {
                    $name = $passenger['first_name'] . ' ' . $passenger['last_name'];
                } else {

                    if ($passenger['type'] == 'ADL') {
                        $countAdul++;
                        $name = 'Adult ' . $countAdul;
                    } else {
                        $countChild++;
                        $name = 'Child ' . $countChild;
                    }
                }

                $quote_passengers[$passenger['id']] = [
                    'code'  => $passenger['id'],
                    'label' => $name,
                ];
            }

            foreach ($quoteDistributions as $type_room => $distribution) {

                $passengers = [];
                foreach ($distribution->passengers as $passenger) {
                    array_push($passengers, $quote_passengers[$passenger->quote_passenger_id]);
                }

                array_push($quoteDistributionsNew, [
                    'type_room'      => $distribution->type_room,
                    'type_room_name' => $distribution->type_room_name,
                    'occupation'     => $distribution->occupation,
                    'single'         => $distribution->single,
                    'double'         => $distribution->double,
                    'triple'         => $distribution->triple,
                    'adult'          => $distribution->adult,
                    'child'          => $distribution->child,
                    'order'          => $distribution->order,
                    'passengers'     => $passengers
                ]);
            }
        }


        return \response()->json(['quoteDistributions' => $quoteDistributionsNew], 200);
    }

    public function storeOccupationPassengersHotel(Request $request)
    {


        $distribution_passengers = $request->post('distribution_passengers');
        $quote_id = $request->post('quote_id');

        if (!$this->validatePassengers($distribution_passengers)) {
            return response()->json(['error' => 'misassociated passengers'], 200);
        }

        $this->saveOccupationPassengersHotel($quote_id, $distribution_passengers);


        return response()->json("Acomodación actualizada", 200);
    }

    public function validatePassengers($distribution_passengers)
    {

        $rooms = [];
        foreach ($distribution_passengers as $passengers) {

            foreach ($passengers['passengers'] as $passenger) {
                $rooms[$passenger['code']][] = $passenger;
            }
        }

        foreach ($rooms as $room) {
            if (count($room) > 1) {
                return false;
            }
        }

        return true;
    }

    public function updateOccupationHotelGeneral(Request $request)
    {

        $simple = $request->post('simple');
        $double = $request->post('double');
        $double_child = $request->post('double_child');
        $triple = $request->post('triple');
        $triple_child = $request->post('triple_child');
        $quote_id = $request->post('quote_id');

        $client_id = $this->getClientId($request);
        $hotels_add_rooms = DB::transaction(function () use (
            $quote_id,
            $simple,
            $double,
            $double_child,
            $triple,
            $triple_child
        ) {

            $hotelsRemoved = $this->clearNoServiceRooms($quote_id);

            // dd($hotelsRemoved);

            DB::table('quote_accommodations')->where('quote_id', $quote_id)->update([
                'single'       => (int)$simple,
                'double'       => (int)$double,
                'double_child' => (int)$double_child,
                'triple'       => (int)$triple,
                'triple_child' => (int)$triple_child,
                'updated_at'   => Carbon::now()
            ]);
            $hotels_add_rooms = [];
            $hotels_for_validate = [];
            $quote_categories = DB::table('quote_categories')->select('id')->where('quote_id', $quote_id)->get();
            foreach ($quote_categories as $quote_category) {
                $quote_services = DB::table('quote_services')
                    ->where('type', 'hotel')
                    ->where('locked', 0)
                    ->where('quote_category_id', $quote_category->id)
                    ->get();
                $hotels = [];
                $passenger_cotizado = [];
                foreach ($quote_services as $quote_service) {

                    if (!$quote_service->hyperguest_pull) {
                        $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')
                            ->where('quote_service_id', $quote_service->id)->first();
                        if ($service_room != null) {
                            $hotel_checked = false;
                            foreach ($hotels as $hotel) {
                                if (
                                    $hotel["hotel_id"] == $quote_service->object_id && $hotel["date_in"] == $quote_service->date_in &&
                                    $hotel["rate_plan_room"] == $service_room->rate_plan_room_id && $hotel["nights"] == $quote_service->nights
                                ) {
                                    $hotel_checked = true;
                                }
                            }

                            if (!$hotel_checked) {

                                // $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where('id',
                                //     $service_room->rate_plan_room_id)->first();
                                // $room = DB::table('rooms')->leftjoin('hotels', 'hotels.id', '=', 'rooms.hotel_id')->select('room_type_id','country_id','state_id','typeclass_id')->where('rooms.id',
                                //     $rate_plan_room->room_id)->first();
                                // $room_type = DB::table('room_types')->select('occupation')->where('id',
                                //     $room->room_type_id)->first();

                                $rate_plan_room = RatesPlansRooms::with([
                                    'room' => function ($query) {
                                        $query->with([
                                            'hotel' => function ($query) {
                                                $query->with([
                                                    'country' => function ($query) {
                                                        $query->with([
                                                            'translations' => function ($query) {
                                                                $query->select(['object_id', 'value']);
                                                                $query->where('type', 'country');
                                                                $query->where('language_id', 2);
                                                            }
                                                        ]);
                                                    },
                                                    'state' => function ($query) {
                                                        $query->with([
                                                            'translations' => function ($query) {
                                                                $query->select(['object_id', 'value']);
                                                                $query->where('type', 'state');
                                                                $query->where('language_id', 2);
                                                            }
                                                        ]);
                                                    },
                                                    'hoteltypeclass' => function ($query) {
                                                        $query->where('year', date('Y'));
                                                    }
                                                ]);
                                            },
                                            'room_type'
                                        ]);
                                    }
                                ])->find($service_room->rate_plan_room_id);


                                $hotels[] = [
                                    "hotel_id"       => $quote_service->object_id,
                                    "date_in"        => $quote_service->date_in,
                                    "nights"         => $quote_service->nights,
                                    "rate_plan_room" => $service_room->rate_plan_room_id
                                ];

                                $hotelTypeClass = $rate_plan_room->room->hotel->hoteltypeclass->first();

                                $hotels_for_validate[$quote_category->id . '-' . $quote_service->object_id . '-' . $quote_service->date_in][] = [
                                    "quote_category_id" => $quote_category->id,
                                    "quote_service_id"  => $quote_service->id,
                                    "hotel_id"          => $quote_service->object_id,
                                    "hotel_name"        => $rate_plan_room->room->hotel->name,
                                    "date_in"           => $quote_service->date_in,
                                    "date_out"          => $quote_service->date_out,
                                    "nights"            => $quote_service->nights,
                                    "occupation"        => $rate_plan_room->room->room_type->occupation,
                                    'destiny_code'      => "{$rate_plan_room->room->hotel->country->iso},{$rate_plan_room->room->hotel->state->iso}",
                                    'destiny_label'     => "{$rate_plan_room->room->hotel->country->translations[0]->value},{$rate_plan_room->room->hotel->state->translations[0]->value}",
                                    'typeclass_id'      => $hotelTypeClass ? $hotelTypeClass->typeclass_id : ''

                                ];

                                $this->addRoomsToHotel(
                                    $rate_plan_room->room->room_type->occupation,
                                    $quote_service,
                                    $service_room->rate_plan_room_id,
                                    $simple,
                                    $double,
                                    $triple,
                                    $double_child,
                                    $triple_child,
                                    null,
                                    null
                                );
                            }
                        }
                    } else {
                        $service_room = DB::table('quote_service_rooms_hyperguest')->select(['room_id', 'rate_plan_id'])
                            ->where('quote_service_id', $quote_service->id)->first();
                        if ($service_room != null) {
                            $hotel_checked = false;
                            foreach ($hotels as $hotel) {
                                if (
                                    $hotel["hotel_id"] == $quote_service->object_id && $hotel["date_in"] == $quote_service->date_in &&
                                    $hotel["rate_plan_room"] == $service_room->room_id && $hotel["nights"] == $quote_service->nights
                                ) {
                                    $hotel_checked = true;
                                }
                            }

                            if (!$hotel_checked) {

                                $rate_plan_room = Room::with([
                                    'hotel' => function ($query) {
                                        $query->with([
                                            'country' => function ($query) {
                                                $query->with([
                                                    'translations' => function ($query) {
                                                        $query->select(['object_id', 'value']);
                                                        $query->where('type', 'country');
                                                        $query->where('language_id', 2);
                                                    }
                                                ]);
                                            },
                                            'state' => function ($query) {
                                                $query->with([
                                                    'translations' => function ($query) {
                                                        $query->select(['object_id', 'value']);
                                                        $query->where('type', 'state');
                                                        $query->where('language_id', 2);
                                                    }
                                                ]);
                                            }
                                        ]);
                                    },
                                    'room_type'
                                ])->find($service_room->room_id);


                                $hotels[] = [
                                    "hotel_id"       => $quote_service->object_id,
                                    "date_in"        => $quote_service->date_in,
                                    "nights"         => $quote_service->nights,
                                    "rate_plan_room" => $service_room->room_id
                                ];

                                $hotels_for_validate[$quote_category->id . '-' . $quote_service->object_id . '-' . $quote_service->date_in][] = [
                                    "quote_category_id" => $quote_category->id,
                                    "quote_service_id"  => $quote_service->id,
                                    "hotel_id"          => $quote_service->object_id,
                                    "hotel_name"        => $rate_plan_room->hotel->name,
                                    "date_in"           => $quote_service->date_in,
                                    "date_out"          => $quote_service->date_out,
                                    "nights"            => $quote_service->nights,
                                    "occupation"        => $rate_plan_room->room_type->occupation,
                                    'destiny_code'      => "{$rate_plan_room->hotel->country->iso},{$rate_plan_room->hotel->state->iso}",
                                    'destiny_label'     => "{$rate_plan_room->hotel->country->translations[0]->value},{$rate_plan_room->hotel->state->translations[0]->value}",
                                    'typeclass_id'      => $rate_plan_room->hotel->typeclass_id
                                ];

                                $this->addRoomsToHotel(
                                    $rate_plan_room->room_type->occupation,
                                    $quote_service,
                                    null,
                                    $simple,
                                    $double,
                                    $triple,
                                    $double_child,
                                    $triple_child,
                                    $service_room->rate_plan_id,
                                    $service_room->room_id,
                                );
                            }
                        }
                    }
                }
                // dd($hotels);

            }

            //armaos un array de las categorias y hoteles que no se crearon
            foreach ($hotels_for_validate as $index => $hotels_for) {

                $ocupactionList = array_unique(array_column($hotels_for, 'occupation'));

                if ($simple > 0 and !in_array(1, $ocupactionList)) {
                    $hotels_for[0]['occupation'] = 1;
                    $hotels_for[0]['cant'] = $simple;
                    $hotels_add_rooms[$index][] = $hotels_for[0];
                }

                if ($double > 0 and !in_array(2, $ocupactionList)) {
                    $hotels_for[0]['occupation'] = 2;
                    $hotels_for[0]['cant'] = $double;
                    $hotels_add_rooms[$index][] = $hotels_for[0];
                }

                if ($triple > 0 and !in_array(3, $ocupactionList)) {
                    $hotels_for[0]['occupation'] = 3;
                    $hotels_for[0]['cant'] = $triple;
                    $hotels_add_rooms[$index][] = $hotels_for[0];
                }
            }

            // dd($hotelsRemoved);
            // Solo los hoteles que no existan porque se han eliminado se volverar a generar,
            foreach ($hotelsRemoved as $index => $hotels) {
                if (!isset($hotels_add_rooms[$index]) and !isset($hotels_for_validate[$index])) {  // validamos que lo que emos eliminado, no exista dentro de los hotels_add_rooms(porque ya la logica lo debe de agregar) y que no este dentro  hotels_for_validate(porque ya lo logica lo debe de agregar)

                    if ($simple > 0) {
                        $hotels['occupation'] = 1;
                        $hotels['cant'] = $simple;
                        $hotels_add_rooms[$index][] = $hotels;
                    }

                    if ($double > 0) {
                        $hotels['occupation'] = 2;
                        $hotels['cant'] = $double;
                        $hotels_add_rooms[$index][] = $hotels;
                    }

                    if ($triple > 0) {
                        $hotels['occupation'] = 3;
                        $hotels['cant'] = $triple;
                        $hotels_add_rooms[$index][] = $hotels;
                    }
                }
            }


            return $hotels_add_rooms;
        });

        // dd("finnnnnnnnnnnn");

        $quote_categories = QuoteCategory::where('quote_id', $quote_id)->get();

        $quote = Quote::where('id', $quote_id)
            ->with([
                'people' => function ($query) {
                    $query->select(['id', 'adults', 'child', 'quote_id']);
                }
            ])
            ->first(['id', 'operation']);

        if ($quote and $quote->operation === 'passengers') {
            $people = $quote->people->first();
            if ($people) {
                // aqui ya no se hace la acomodacion en hoteles
                $this->updateListServicePassengersGeneral($quote_id, (int)$people->adults, (int)$people->child);
            }
        }

        foreach ($quote_categories as $quote_category) {
            $quote_services = QuoteService::where('type', 'hotel')->where(
                'quote_category_id',
                $quote_category["id"]
            )->get();
            foreach ($quote_services as $quote_service) {
                $this->updateAmountService($quote_service["id"], $client_id, $quote_id);
            }
        }

        $previous_data = QuoteHistoryLog::where('quote_id', $quote_id)
            ->where('slug', 'update_accommodation')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($previous_data) {
            $previous_data_ = $previous_data->current_data;
        } else {
            $previous_data_ = 'SGL:0 DBL:0 TPL:0';
        }

        $this->store_history_logs($quote_id, [
            [
                "type"          => "update",
                "slug"          => "update_accommodation_general",
                "previous_data" => $previous_data_,
                "current_data"  => "SGL:" . $simple . ' DBL:' . $double . ' TPL:' . $triple,
                "description"   => "Actualizó acomodación general y del itinerario"
            ]
        ]);

        return \response()->json([
            "message"          => "Ocupación de hoteles actualizada",
            "hotels_add_rooms" => $hotels_add_rooms
        ], 200);

        // return response()->json("Ocupación de hoteles actualizada", 200);
    }

    public function clearNoServiceRooms($quote_id)
    {


        $hotels_for_validate = [];
        $quote_categories = DB::table('quote_categories')->select('id')->where('quote_id', $quote_id)->get();
        foreach ($quote_categories as $quote_category) {
            $quote_services = DB::table('quote_services')
                ->where('type', 'hotel')
                ->where('locked', 0)
                ->where('quote_category_id', $quote_category->id)
                ->get();

            foreach ($quote_services as $quote_service) {

                // $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where('quote_service_id', $quote_service->id)->first();

                if (!$quote_service->hyperguest_pull) {
                    $service_room = QuoteServiceRoom::with([
                        'rate_plan_room' => function ($query) {
                            $query->with([
                                'room' => function ($query) {
                                    $query->with([
                                        'room_type'
                                    ]);
                                }
                            ]);
                        }
                    ])->where('quote_service_id', $quote_service->id)->first();

                    if (!$service_room) { // el servicio no tiene quote_service_rooms lo eliminamos, para que se regenere

                        $hotel = Hotel::with([
                            'country' => function ($query) {
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'country');
                                        $query->where('language_id', 2);
                                    }
                                ]);
                            },
                            'state' => function ($query) {
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'state');
                                        $query->where('language_id', 2);
                                    }
                                ]);
                            }
                        ])->find($quote_service->object_id);

                        $hotels_for_validate[$quote_category->id . '-' . $quote_service->object_id . '-' . $quote_service->date_in] = [
                            "quote_category_id" => $quote_category->id,
                            "quote_service_id"  => $quote_service->id,
                            "hotel_id"          => $quote_service->object_id,
                            "hotel_name"        => $hotel->name,
                            "date_in"           => $quote_service->date_in,
                            "date_out"          => $quote_service->date_out,
                            "nights"            => $quote_service->nights,
                            "occupation"        => "error",
                            'destiny_code'      => "{$hotel->country->iso},{$hotel->state->iso}",
                            'destiny_label'     => "{$hotel->country->translations[0]->value},{$hotel->state->translations[0]->value}",
                            'typeclass_id'      => $hotel->typeclass_id,
                            'quote_service'     => $quote_service
                        ];

                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                    } else {
                        // si tene un servicio que dice que es una simple pero su quote_service_rooms es distinto es un error y lo eliminamos, para que se regenere
                        $occupation_service = null;
                        if ($quote_service->single > 0) {
                            $occupation_service = 1;
                        }
                        if ($quote_service->double > 0) {
                            $occupation_service = 2;
                        }
                        if ($quote_service->triple > 0) {
                            $occupation_service = 3;
                        }

                        if ($occupation_service != null and $occupation_service != $service_room->rate_plan_room->room->room_type->occupation) {

                            $hotel = Hotel::with([
                                'country' => function ($query) {
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('type', 'country');
                                            $query->where('language_id', 2);
                                        }
                                    ]);
                                },
                                'state' => function ($query) {
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('type', 'state');
                                            $query->where('language_id', 2);
                                        }
                                    ]);
                                }
                            ])->find($quote_service->object_id);

                            $hotels_for_validate[$quote_category->id . '-' . $quote_service->object_id . '-' . $quote_service->date_in] = [
                                "quote_category_id" => $quote_category->id,
                                "quote_service_id"  => $quote_service->id,
                                "hotel_id"          => $quote_service->object_id,
                                "hotel_name"        => $hotel->name,
                                "date_in"           => $quote_service->date_in,
                                "date_out"          => $quote_service->date_out,
                                "nights"            => $quote_service->nights,
                                "occupation"        => "error",
                                'destiny_code'      => "{$hotel->country->iso},{$hotel->state->iso}",
                                'destiny_label'     => "{$hotel->country->translations[0]->value},{$hotel->state->translations[0]->value}",
                                'typeclass_id'      => $hotel->typeclass_id,
                                'quote_service'     => $quote_service
                            ];

                            DB::table('quote_services')->where('id', $quote_service->id)->delete();
                        }
                    }
                } else {

                    $service_room = QuoteServiceRoomHyperguest::with([
                        'room' => function ($query) {
                            $query->with([
                                'room_type'
                            ]);
                        }
                    ])->where('quote_service_id', $quote_service->id)->first();


                    if (!$service_room) { // el servicio no tiene quote_service_rooms lo eliminamos, para que se regenere

                        $hotel = Hotel::with([
                            'country' => function ($query) {
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'country');
                                        $query->where('language_id', 2);
                                    }
                                ]);
                            },
                            'state' => function ($query) {
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'state');
                                        $query->where('language_id', 2);
                                    }
                                ]);
                            }
                        ])->find($quote_service->object_id);

                        $hotels_for_validate[$quote_category->id . '-' . $quote_service->object_id . '-' . $quote_service->date_in] = [
                            "quote_category_id" => $quote_category->id,
                            "quote_service_id"  => $quote_service->id,
                            "hotel_id"          => $quote_service->object_id,
                            "hotel_name"        => $hotel->name,
                            "date_in"           => $quote_service->date_in,
                            "date_out"          => $quote_service->date_out,
                            "nights"            => $quote_service->nights,
                            "occupation"        => "error",
                            'destiny_code'      => "{$hotel->country->iso},{$hotel->state->iso}",
                            'destiny_label'     => "{$hotel->country->translations[0]->value},{$hotel->state->translations[0]->value}",
                            'typeclass_id'      => $hotel->typeclass_id,
                            'quote_service'     => $quote_service
                        ];

                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                    } else {

                        // si tene un servicio que dice que es una simple pero su quote_service_rooms es distinto es un error y lo eliminamos, para que se regenere
                        $occupation_service = null;
                        if ($quote_service->single > 0) {
                            $occupation_service = 1;
                        }
                        if ($quote_service->double > 0) {
                            $occupation_service = 2;
                        }
                        if ($quote_service->triple > 0) {
                            $occupation_service = 3;
                        }

                        if ($occupation_service != null and $occupation_service != $service_room->room->room_type->occupation) {

                            $hotel = Hotel::with([
                                'country' => function ($query) {
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('type', 'country');
                                            $query->where('language_id', 2);
                                        }
                                    ]);
                                },
                                'state' => function ($query) {
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('type', 'state');
                                            $query->where('language_id', 2);
                                        }
                                    ]);
                                }
                            ])->find($quote_service->object_id);

                            $hotels_for_validate[$quote_category->id . '-' . $quote_service->object_id . '-' . $quote_service->date_in] = [
                                "quote_category_id" => $quote_category->id,
                                "quote_service_id"  => $quote_service->id,
                                "hotel_id"          => $quote_service->object_id,
                                "hotel_name"        => $hotel->name,
                                "date_in"           => $quote_service->date_in,
                                "date_out"          => $quote_service->date_out,
                                "nights"            => $quote_service->nights,
                                "occupation"        => "error",
                                'destiny_code'      => "{$hotel->country->iso},{$hotel->state->iso}",
                                'destiny_label'     => "{$hotel->country->translations[0]->value},{$hotel->state->translations[0]->value}",
                                'typeclass_id'      => $hotel->typeclass_id,
                                'quote_service'     => $quote_service
                            ];

                            DB::table('quote_services')->where('id', $quote_service->id)->delete();
                        }
                    }
                }
            }
        }

        return $hotels_for_validate;
    }

    public function generateQuoteDistributions($quotePassengers, $single, $double, $triple, $adults, $child)
    {

        $quoteDistributions = [];
        $order = 1;
        for ($i = 0; $i < $single; $i++) {
            array_push($quoteDistributions, [
                'type_room'      => 'single',
                'type_room_name' => 'SGL',
                'occupation'     => 1,
                'single'         => 1,
                'double'         => 0,
                'triple'         => 0,
                'order'          => $order,
                'passengers'     => []
            ]);
            $order++;
        }
        for ($i = 0; $i < $double; $i++) {
            array_push($quoteDistributions, [
                'type_room'      => 'double',
                'type_room_name' => 'DBL',
                'occupation'     => 2,
                'single'         => 0,
                'double'         => 1,
                'triple'         => 0,
                'order'          => $order,
                'passengers'     => []
            ]);
            $order++;
        }
        for ($i = 0; $i < $triple; $i++) {
            array_push($quoteDistributions, [
                'type_room'      => 'triple',
                'type_room_name' => 'TPL',
                'occupation'     => 3,
                'single'         => 0,
                'double'         => 0,
                'triple'         => 1,
                'order'          => $order,
                'passengers'     => []
            ]);
            $order++;
        }

        $quoteDistributions = $this->updatePaxAcomodacionGeneral($quoteDistributions, $adults, $child, 0);
        $countAdul = 0;
        $countChild = 0;
        $name = '';
        foreach ($quoteDistributions as $index => $quoteAccommodation) {
            $totalAsignad = $quoteAccommodation['adult'] + $quoteAccommodation['child'];
            if ($totalAsignad > 0) {
                $passengers = array_slice($quotePassengers, 0, $totalAsignad);
                foreach ($passengers as $passenger) {


                    $name = '';
                    if ($passenger['first_name'] or $passenger['last_name']) {
                        $name = $passenger['first_name'] . ' ' . $passenger['last_name'];
                    } else {

                        if ($passenger['type'] == 'ADL') {
                            $countAdul++;
                            $name = 'Adult ' . $countAdul;
                        } else {
                            $countChild++;
                            $name = 'Child ' . $countChild;
                        }
                    }

                    array_push($quoteDistributions[$index]['passengers'], [
                        'code'  => $passenger['id'],
                        'label' => $name,
                    ]);
                }

                $quotePassengers = array_slice($quotePassengers, $totalAsignad);
            }
        }

        return $quoteDistributions;
    }

    public function saveOccupationPassengersHotel($quote_id, $distribution_passengers)
    {

        QuoteDistribution::where("quote_id", $quote_id)->delete();

        foreach ($distribution_passengers as $passengers) {

            $distribution = new QuoteDistribution();
            $distribution->type_room = $passengers['type_room'];
            $distribution->type_room_name = $passengers['type_room_name'];
            $distribution->occupation = $passengers['occupation'];
            $distribution->single = $passengers['single'];
            $distribution->double = $passengers['double'];
            $distribution->triple = $passengers['triple'];
            $distribution->adult = $passengers['adult'];
            $distribution->child = $passengers['child'];
            $distribution->order = $passengers['order'];
            $distribution->quote_id = $quote_id;
            $distribution->save();
            $adults = 0;
            $childs = 0;
            foreach ($passengers['passengers'] as $passenger) {

                $distribution_passenger = new QuoteDistributionPassenger();
                $distribution_passenger->quote_distribution_id = $distribution->id;
                $distribution_passenger->quote_passenger_id = $passenger['code'];
                $distribution_passenger->save();

                $quote_passeger = QuotePassenger::find($passenger['code']);
                if ($quote_passeger->type == 'ADL') {
                    $adults++;
                }
                if ($quote_passeger->type == 'CHD') {
                    $childs++;
                }

                $distribution->adult = $adults;
                $distribution->child = $childs;
                $distribution->save();
            }
        }
    }

    public function saveQuoteServicePassenger(Request $request)
    {
        $passengers = $request->post('passengers');
        $service_id = $request->post('service_id');
        $quote_id = $request->post('quote_id');


        DB::transaction(function () use ($passengers, $service_id, $quote_id) {

            foreach ($passengers as $passenger) {

                DB::table('quote_service_passengers')->where(
                    'quote_service_id',
                    $service_id
                )->where('quote_passenger_id', $passenger["id"])->delete();

                if ($passenger["checked"]) {
                    //Todo agregamos el pasajero al servicio
                    DB::table('quote_service_passengers')->insert([
                        'quote_service_id'   => $service_id,
                        'quote_passenger_id' => $passenger["id"],
                        'created_at'         => Carbon::now(),
                        'updated_at'         => Carbon::now(),
                    ]);
                }
                // } else {
                //     //Todo eliminamos el pasajero al servicio
                //     DB::table('quote_service_passengers')->where('quote_service_id',
                //         $service_id)->where('quote_passenger_id', $passenger["id"])->delete();
                // }
            }
        });

        DB::transaction(function () use ($passengers, $service_id, $quote_id) {
            //Todo Luego de agregar o eliminar el pasajero al servicio contamos cuantos adultos hay en el servicio y
            // actualizamos la cantidad de adultos en el servicio
            $quote_service_passengers = QuoteServicePassenger::where('quote_service_id', $service_id)
                ->with([
                    'passenger' => function ($query) {
                        $query->select(['id', 'type']);
                    }
                ])->orderBy('quote_passenger_id')->get();

            //Todo Contamos cuantos adultos tenemos en el servicio
            $adult_count = $quote_service_passengers->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            })->count();

            //Todo Contamos cuantos niños tenemos en el servicio
            $child_count = $quote_service_passengers->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            })->count();

            //Todo Actualizamos la cantidad de adultos y niños que tiene el servicio asignados
            DB::table('quote_services')->where('id', $service_id)->update([
                'adult' => $adult_count,
                'child' => $child_count,
            ]);
        });

        $service = QuoteService::where('id', $service_id)
            ->with('service')
            ->with('hotel')
            ->with('passengers')
            ->with('service_rate')
            ->orderBy('date_in')
            ->orderBy('order')
            ->first();


        return \response()->json([
            "message" => "Pasajeros Actualizados del servicio exitosamente",
            "service" => $service
        ], 200);
    }


    public function updatePassengersService(Request $request)
    {
        $service_id = $request->post('service_id');
        $adult = $request->post('adult');
        $child = $request->post('child');
        $quote_id = $request->post('quote_id');
        $client_id = $this->getClientId($request);
        $quote_service_ = QuoteService::find($service_id);

        if ($adult == 0) {
            $adult_ = 0;
            $child_ = 0;
        } else {
            $adult_ = $adult;
            $child_ = $child;
        }

        DB::transaction(function () use ($quote_id, $quote_service_, $adult_, $child_, $service_id) {
            $service_code_ = '';
            if ($quote_service_->type === 'hotel') {
                $hotel_ = Hotel::where('id', $quote_service_->object_id)->with('channel')->first();
                $service_code_ = ($hotel_) ? $hotel_->channel[0]->code : "";
            }
            if ($quote_service_->type === 'service') {
                $service_ = Service::where('id', $quote_service_->object_id)->first();
                $service_code_ = ($service_) ? $service_->aurora_code : "";
            }
            if ($quote_service_->type === 'flight') {
                $service_code_ = $quote_service_->code_flight . ' - ' . $quote_service_->origin . " > " . $quote_service_->destiny;
            }

            $type_class_id_ = QuoteCategory::find($quote_service_->quote_category_id)->type_class_id;
            $this->store_history_logs($quote_id, [
                [
                    "type"          => "update",
                    "slug"          => "update_service_paxs",
                    "previous_data" => json_encode([
                        "adult" => $quote_service_->adult,
                        "child" => $quote_service_->child,
                    ]),
                    "current_data" => json_encode([
                        "type_class_id" => $type_class_id_,
                        "type_service"  => $quote_service_->type,
                        "object_id"     => $quote_service_->object_id,
                        "service_code"  => $service_code_,
                        "adult"         => $adult_,
                        "child"         => $child_,
                    ]),
                    "description" => "Actualizó cantidad de Pasajeros"
                ]
            ]);

            $this->updateAssignPassengerService($quote_id, $service_id, (int)$adult_, (int)$child_);

            $quote_service_->adult = $adult_;
            $quote_service_->child = $child_;
            $quote_service_->save();
        });

        $this->updateAssignPassengerService($quote_id, $service_id, (int)$adult_, (int)$child_);
        // $this->updateAmountService($service_id, $client_id, $quote_id);
        $this->updateAmountAllServices($quote_id, $client_id);

        return response()->json("Pasajeros del servicio actualizados correctamente", 200);
    }

    public function update_hour_in($quote_service_id, Request $request)
    {
        try {
            $hour_in = $request->__get('hour_in');
            $quote_service = QuoteService::where('id', $quote_service_id)->first();
            $quote_service->hour_in = $hour_in;
            $quote_service->save();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'error'   => $ex->getMessage(),
            ]);
        }
    }

    public function update(Request $request, $quote_id_original)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
            'name'       => 'required',
            'date'       => 'required',
        ]);
        // cities / nights
        if ($validator->fails()) {
            $response = ['success' => false];
        } else {

            $name = $request->input('name');
            $reference_code = $request->input('reference_code');
            $client_id = $request->input('client_id');
            $date = $request->input('date');
            $date_estimated = $request->input('date_estimated');
            $service_type_id = $request->input('service_type_id');
            $_quote = Quote::find($quote_id_original);
            $languageId = $request->input('languageId');

            if ($_quote) {
                $histories = [];
                if ($_quote->name !== $name) {
                    array_push($histories, [
                        "type"          => "update",
                        "slug"          => "update_name",
                        "previous_data" => $_quote->name,
                        "current_data"  => $name,
                        "description"   => "Actualizó el nombre"
                    ]);
                }
                if ($_quote->date_in !== $date) {
                    $description = "Actualizó la fecha general y de todo el itinerario de: " . $_quote->date_in . " a: " . $date;
                    array_push($histories, [
                        "type"          => "update",
                        "slug"          => "update_date_general",
                        "previous_data" => $_quote->date_in,
                        "current_data"  => $date,
                        "description"   => $description
                    ]);
                }
                if ($_quote->estimated_travel_date !== $date_estimated) {
                    array_push($histories, [
                        "type"          => "update",
                        "slug"          => "update_date_estimated",
                        "previous_data" => $_quote->estimated_travel_date,
                        "current_data"  => $date_estimated,
                        "description"   => "Actualizó la fecha estimada"
                    ]);
                }
                if ($_quote->service_type_id !== $service_type_id) {
                    array_push($histories, [
                        "type"          => "update",
                        "slug"          => "update_service_type_general",
                        "previous_data" => $_quote->service_type_id,
                        "current_data"  => $service_type_id,
                        "description"   => "Actualizó el tipo de servicio general"
                    ]);
                }
                if ($_quote->languageId !== $languageId) {
                    array_push($histories, [
                        "type"          => "update",
                        "slug"          => "update_service_type_general",
                        "previous_data" => $_quote->languageId,
                        "current_data"  => $languageId,
                        "description"   => "Actualizó el idioma de la cotización"
                    ]);
                }
                if (count($histories) > 0) {
                    $this->store_history_logs($quote_id_original, $histories);
                }

                $_quote->name = $name;
                $_quote->reference_code = $reference_code;
                $_quote->date_in = $date;
                $_quote->estimated_travel_date = $date_estimated;
                $_quote->service_type_id = $service_type_id;
                $_quote->language_id = $languageId;
                $_quote->save();


                $quote_ = DB::table('quote_logs')->where('quote_id', $quote_id_original)->where(
                    'type',
                    'editing_quote'
                )->first();


                if ($quote_) { // Actualiza el usado en draft
                    $quote_id = $quote_->object_id;
                    // Draft - Original
                    $this->replaceQuote($quote_id_original, $quote_id);
                    $this->updateAmountAllServices($quote_id, $client_id);
                    $this->move_history_logs($quote_id_original, $quote_id);
                } else {
                    // cuando es un paquete que se esta editando | crea nuevo
                    $quote_log = DB::table('quote_logs')->where('quote_id', $quote_id_original)->where(
                        'type',
                        'editing_package'
                    )->first();
                    if ($quote_log) {
                        $this->replacePackageToQuote($quote_log);
                    }
                }

                $response = ['success' => true];
            } else {
                $response = ['success' => false, 'error' => 'No se encontró su cotización'];
            }
        }

        return Response::json($response);
    }

    public function updateOptional(Request $request)
    {
        $quote_service_id = $request->input('quote_service_id');
        $optional = $request->input('optional');

        DB::transaction(function () use ($quote_service_id, $optional) {

            if (is_array($quote_service_id)) {
                // en hoteles actualizamos todos las habitaciones que estan en el hotel.
                DB::table('quote_services')->whereIn('id', $quote_service_id)->update([
                    'optional' => ($optional == 1 ? 0 : 1)
                ]);
            } else {
                $optional = DB::table('quote_services')->where('id', $quote_service_id)->first()->optional;
                DB::table('quote_services')->where('id', $quote_service_id)->update([
                    'optional' => ($optional == 1 ? 0 : 1)
                ]);
            }
        });

        return response()->json("Opcional Actualizado", 200);
    }

    public function updateDateAndOrderServices(Request $request)
    {
        $services = $request->post('services');
        $client_id = $request->post('client_id');
        $quote_id = $request->post('quote_id');

        $this->updateOrderAndDateServices($services);

        //        $this->updateAmountAllServices($quote_id, $client_id);

        return \response()->json($services, 200);
    }

    public function priceByRanges($quote_id, Request $request)
    {
        $user_type_id = $request->get('user_type_id');
        $user_id = $request->get('user_id');
        $lang = $request->get('lang');
        $client_id = null;
        if ($user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', $user_id)->first();
            $client_id = $client_id["client_id"];
        }

        if ($user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        return $this->getQuotePriceRanger($quote_id, $client_id, $lang);
    }

    public function priceByPassengers($quote_id, Request $request)
    {
        try {
            $user_type_id = $request->get('user_type_id');
            $user_id = $request->get('user_id');
            $lang = $request->get('lang');
            $client_id = null;

            if (empty($user_type_id)) {
                return response()->json("No se envió el tipo de usuario, por favor intente nuevamente..", 500);
            }

            if (empty($user_id)) {
                return response()->json("No se envió el id de usuario, por favor intente nuevamente..", 500);
            }

            if (empty($lang)) {
                return response()->json("No se envió el idioma, por favor intente nuevamente..", 500);
            }

            if ($user_type_id == 4) {
                $client_id = ClientSeller::select('client_id')->where('user_id', $user_id)->first();
                $client_id = $client_id["client_id"];
            }

            if ($user_type_id == 3) {
                $client_id = $request->post('client_id');
            }

            return $this->getQuotePricePassenger($quote_id, $client_id, $lang, $user_type_id);
        } catch (\Exception $ex) {
            return response()->json(
                [
                    'file'     => $ex->getFile(),
                    'line'     => $ex->getLine(),
                    'detail'   => $ex->getMessage(),
                    'message'  => $ex->getMessage(),
                    'type'     => 'error',
                    'success'  => false,
                    'response' => 'ERR',
                ],
                500
            );
        }
    }

    public function saveRange(Request $request)
    {
        QuoteRange::where("quote_id", $request->quote_id)->delete();

        foreach ($request->ranges as $range) {

            $rangeBD = new QuoteRange();
            $rangeBD->quote_id = $request->quote_id;
            $rangeBD->from = $range['from'];
            $rangeBD->to = $range['to'];
            $rangeBD->save();
        }

        $quoteRanges = QuoteRange::where("quote_id", $request->quote_id)->get();

        QuoteDistribution::where("quote_id", $request->quote_id)->delete();

        return \response()->json($quoteRanges, 200);
    }


    public function copyPackageToQuote(Request $request)
    {

        $to_status = ($request->has('status')) ? $request->input('status') : 2;
        $user_id = ($request->input('user_id')) ? $request->input('user_id') : Auth::user()->id;
        $new_name = $request->input('name');
        $date_in = $request->input('date_in');
        $quantity_persons = $request->input('quantity_persons');
        $type_service = $request->input('type_service');
        $type_class_id = $request->input('type_class_id');
        // $package_token = $request->input('package_token');
        $packages_selected = $request->input('package_token');

        $hotels_changed = $request->input('hotels_changed');
        $rooms = $request->input('rooms');

        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }
        //Todo Busco el paquete seleccionado y almacenado en cache
        // if (Cache::has($package_token)) {
        //     $packages_selected = Cache::get($package_token);
        //     if (count($packages_selected) === 0) {
        //         return Response::json([
        //             'success' => false,
        //             'message' => 'Su tiempo de reserva termino, por favor vuelva a buscar su paquete',
        //         ], 404);
        //     }
        // } else {
        //     return Response::json([
        //         'success' => false,
        //         'message' => 'Su tiempo de reserva termino, por favor vuelva a buscar su paquete'
        //     ]);
        // }


        // $markup = DB::table('markups')
        //     ->where('client_id', $client_id)
        //     ->where('period', Carbon::now()->year);
        // if ($markup->count() > 0) {
        //     $markup = $markup->first()->hotel;
        // } else {
        //     $markup = 0;
        // }
        $markup = NULL;

        $quote_id = DB::transaction(function () use (
            $to_status,
            $user_id,
            $new_name,
            $date_in,
            $markup,
            $packages_selected,
            $quantity_persons,
            $type_service,
            $type_class_id,
            $hotels_changed,
            $rooms
        ) {

            $package = $packages_selected[0];
            $adults = $quantity_persons['adults'];
            // Todo Cantidad de adultos y niños
            $child_with_bed = $quantity_persons['child_with_bed'];
            $child_without_bed = $quantity_persons['child_without_bed'];
            $children = $child_with_bed + $child_without_bed;

            // Todo acomodacion Adultos
            $quantity_sgl = $rooms['quantity_sgl'];
            $quantity_dbl = $rooms['quantity_dbl'];
            $quantity_tpl = $rooms['quantity_tpl'];
            // Todo acomodacion Niños
            $quantity_child_dbl = 0; //$rooms['quantity_child_dbl'];
            $quantity_child_tpl = 0; //$rooms['quantity_child_tpl'];

            //Todo Consulto si hay una cotizacion en el tablero
            $quotes_front = DB::table('quotes')->where('status', 2)->where('user_id', Auth::user()->id)->get();
            //Todo Si hay elimino
            if ($quotes_front->count() > 0) {
                foreach ($quotes_front as $key => $quote_front) {
                    $this->forcefullyDestroy($quote_front->id);
                }
            }

            //Todo Creo la cabecera de la cotizacion
            $new_object_id = DB::table('quotes')->insertGetId([
                'name'            => $new_name,
                'date_in'         => Carbon::parse($date_in)->format('Y-m-d'),
                'nights'          => $package['nights'],
                'service_type_id' => $type_service,
                'user_id'         => $user_id,
                'markup'          => $markup,
                'status'          => 2,
                'operation'       => 'passengers',
                'package_id'      => $package['id'],
                'created_at'      => Carbon::now()
            ]);

            // Cache::add('quote_id_otas_generic', $new_object_id, 3600);

            //Todo Creo la acomodacion
            DB::table('quote_accommodations')->insert([
                'quote_id'     => $new_object_id,
                'single'       => $quantity_sgl,
                'double'       => $quantity_dbl,
                'double_child' => $quantity_child_dbl,
                'triple'       => $quantity_tpl,
                'triple_child' => $quantity_child_tpl,
                'created_at'   => Carbon::now(),
                "updated_at"   => Carbon::now()
            ]);

            //Todo Creo la categoria de la cotizacion
            $new_category_id = DB::table('quote_categories')->insertGetId([
                'quote_id'      => $new_object_id,
                'type_class_id' => $type_class_id,
                'created_at'    => Carbon::now(),
                "updated_at"    => Carbon::now()
            ]);

            //Todo Ingresamos las edades de los niños por default en 0
            $childrenID = [];
            if ($children > 0) {
                for ($i = 1; $i <= $children; $i++) {
                    $childrenID[$i] = DB::table('quote_age_child')->insertGetId([
                        'quote_id'   => $new_object_id,
                        'age'        => 0,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
            }

            $_date_in = Carbon::parse($date_in);

            $difference_days = 0;
            $_i = 0;

            $package_plan_rate_category_id_ = $package['plan_rates'][0]['plan_rate_categories'][0]['id'];
            $package_services_ = PackageService::where('package_plan_rate_category_id', $package_plan_rate_category_id_)
                ->with(['service_rates', 'service_rooms', 'service_rooms_hyperguest'])
                ->orderBy('date_in')
                ->orderBy('order')->get()->toArray();

            //Todo Cambio de hoteles
            $package_services_ = $this->changeHotels($package_services_, $hotels_changed);
            // dd($package_services_);
            //Todo Ingreso los servicios y hoteles del paquete
            foreach ($package_services_ as $key => $s) {
                $flag_child_without_bed = false;

                $date_service_in = Carbon::parse($s['date_in']);
                $date_service_out = Carbon::parse($s['date_out']);

                if ($s['type'] == 'hotel') {
                    $nigths = ($date_service_in->diffInDays($date_service_out) == 0) ? 1 : $date_service_in->diffInDays($date_service_out);
                } else {
                    $nigths = 0;
                }

                if ($_i > 0) {
                    $difference_days = Carbon::parse($package_services_[0]['date_in'])->diffInDays(Carbon::parse($s['date_in']));
                }

                $date_in = Carbon::parse($_date_in)->addDays($difference_days)->format('Y-m-d');
                $date_out = Carbon::parse($_date_in)->addDays($difference_days + $nigths)->format('Y-m-d');

                //Todo Si hay niños se asigna por habitaciones los hoteles
                //Todo Hab simple
                if ($s['type'] == 'hotel' and $quantity_sgl > 0) {
                    if (!$flag_child_without_bed and $quantity_dbl === 0 and $quantity_tpl === 0) {
                        $flag_child_without_bed = true;
                    }
                    if ($this->hasHotelRoom($s, 1)) {
                        for ($i = 0; $i < $quantity_sgl; $i++) {
                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $new_category_id,
                                'type'              => $s['type'],
                                'object_id'         => $s['object_id'],
                                'order'             => ($s['order'] === null) ? 0 : $s['order'],
                                'date_in'           => $date_in,
                                'date_out'          => $date_out,
                                'nights'            => $nigths,
                                'adult'             => 1,
                                'child'             => ($flag_child_without_bed === true and $children > 0) ? $children : 0,
                                'infant'            => 0,
                                'single'            => 0,
                                'double'            => 0,
                                'triple'            => 0,
                                'triple_active'     => 0,
                                'code_flight'       => @$s['code_flight'],
                                'origin'            => @$s['origin'],
                                'destiny'           => @$s['destiny'],
                                'hyperguest_pull'   => 0,
                                'created_at'        => Carbon::now(),
                                "updated_at"        => Carbon::now()
                            ]);

                            if (count($s['service_rooms']) > 0) {

                                foreach ($s['service_rooms'] as $r) {
                                    $room = RatesPlansRooms::with('room.room_type')
                                        ->where('id', $r['rate_plan_room_id'])
                                        ->first();
                                    if ($room and $room["room"]["room_type"]["occupation"] == 1) {
                                        DB::table('quote_service_rooms')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'rate_plan_room_id' => $r['rate_plan_room_id'],
                                            'created_at'        => Carbon::now(),
                                            "updated_at"        => Carbon::now()
                                        ]);

                                        DB::table('quote_services')->where('id', $new_service_id)->update([
                                            "single" => 1,
                                            "hyperguest_pull" => 0
                                        ]);
                                        break;
                                    }
                                }
                            }

                            if (count($s['service_rooms_hyperguest']) > 0) {

                                foreach ($s['service_rooms_hyperguest'] as $r) {
                                    $room = Room::with('room_type')
                                        ->where('id', $r['room_id'])
                                        ->first();
                                    if ($room and $room["room_type"]["occupation"] == 1) {
                                        DB::table('quote_service_rooms_hyperguest')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'room_id'       => $r['room_id'],
                                            'rate_plan_id'  => $r['rate_plan_id'],
                                            'created_at'    => Carbon::now(),
                                            "updated_at"    => Carbon::now()
                                        ]);

                                        DB::table('quote_services')->where('id', $new_service_id)->update([
                                            "single" => 1,
                                            "hyperguest_pull" => 1
                                        ]);

                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                //Todo Hab doble
                if ($s['type'] == 'hotel' and $quantity_dbl > 0) {
                    $quantityPersonsRoomsDBL = 2;
                    $total_adults_dbl = ($quantityPersonsRoomsDBL - $quantity_child_dbl);
                    if (!$flag_child_without_bed and $child_without_bed > 0) {
                        $flag_child_without_bed = true;
                        $quantity_child_dbl += $flag_child_without_bed;
                    }

                    if ($this->hasHotelRoom($s, 2)) {
                        for ($j = 0; $j < $quantity_dbl; $j++) {
                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $new_category_id,
                                'type'              => $s['type'],
                                'object_id'         => $s['object_id'],
                                'order'             => ($s['order'] === null) ? 0 : $s['order'],
                                'date_in'           => $date_in,
                                'date_out'          => $date_out,
                                'nights'            => $nigths,
                                'adult'             => $total_adults_dbl,
                                'child'             => $quantity_child_dbl,
                                'infant'            => 0,
                                'single'            => 0,
                                'double'            => 0,
                                'triple'            => 0,
                                'triple_active'     => 0,
                                'code_flight'       => @$s['code_flight'],
                                'origin'            => @$s['origin'],
                                'destiny'           => @$s['destiny'],
                                'hyperguest_pull'   => 0,
                                'created_at'        => Carbon::now(),
                                "updated_at"        => Carbon::now()
                            ]);


                            if (count($s['service_rooms']) > 0) {

                                foreach ($s['service_rooms'] as $r) {
                                    $room = RatesPlansRooms::with('room.room_type')
                                        ->where('id', $r['rate_plan_room_id'])
                                        ->first();
                                    if ($room and $room["room"]["room_type"]["occupation"] == 2) {
                                        DB::table('quote_service_rooms')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'rate_plan_room_id' => $r['rate_plan_room_id'],
                                            'created_at'        => Carbon::now(),
                                            "updated_at"        => Carbon::now()
                                        ]);

                                        DB::table('quote_services')->where('id', $new_service_id)->update([
                                            "double"       => 1,
                                            "double_child" => 0,
                                            "hyperguest_pull" => 0
                                        ]);

                                        break;
                                    }
                                }
                            }

                            if (count($s['service_rooms_hyperguest']) > 0) {

                                foreach ($s['service_rooms_hyperguest'] as $r) {
                                    $room = Room::with('room_type')
                                        ->where('id', $r['room_id'])
                                        ->first();
                                    if ($room and $room["room_type"]["occupation"] == 2) {
                                        DB::table('quote_service_rooms_hyperguest')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'room_id'       => $r['room_id'],
                                            'rate_plan_id'  => $r['rate_plan_id'],
                                            'created_at'    => Carbon::now(),
                                            "updated_at"    => Carbon::now()
                                        ]);

                                        DB::table('quote_services')->where('id', $new_service_id)->update([
                                            "double"       => 1,
                                            "double_child" => 0,
                                            "hyperguest_pull" => 1
                                        ]);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                //Todo Hab Triple
                if ($s['type'] == 'hotel' and $quantity_tpl > 0) {
                    $quantityPersonsRoomsTPL = 3;
                    $total_adults_tpl = ($quantityPersonsRoomsTPL - $quantity_child_tpl);
                    if (!$flag_child_without_bed and $child_without_bed > 0) {
                        $flag_child_without_bed = true;
                        $quantity_child_tpl += $flag_child_without_bed;
                    }
                    if ($this->hasHotelRoom($s, 3)) {
                        for ($k = 0; $k < $quantity_tpl; $k++) {
                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $new_category_id,
                                'type'              => $s['type'],
                                'object_id'         => $s['object_id'],
                                'order'             => ($s['order'] === null) ? 0 : $s['order'],
                                'date_in'           => $date_in,
                                'date_out'          => $date_out,
                                'nights'            => $nigths,
                                'adult'             => $total_adults_tpl,
                                'child'             => $quantity_child_tpl,
                                'infant'            => 0,
                                'single'            => 0,
                                'double'            => 0,
                                'triple'            => 0,
                                'triple_active'     => 0,
                                'code_flight'       => @$s['code_flight'],
                                'origin'            => @$s['origin'],
                                'destiny'           => @$s['destiny'],
                                'hyperguest_pull'   => 0,
                                'created_at'        => Carbon::now(),
                                "updated_at"        => Carbon::now()
                            ]);


                            if (count($s['service_rooms']) > 0) {

                                foreach ($s['service_rooms'] as $r) {
                                    $room = RatesPlansRooms::with('room.room_type')
                                        ->where('id', $r['rate_plan_room_id'])
                                        ->first();
                                    if ($room and $room["room"]["room_type"]["occupation"] == 3) {
                                        DB::table('quote_service_rooms')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'rate_plan_room_id' => $r['rate_plan_room_id'],
                                            'created_at'        => Carbon::now(),
                                            "updated_at"        => Carbon::now()
                                        ]);

                                        DB::table('quote_services')->where('id', $new_service_id)->update([
                                            "triple"       => 1,
                                            'triple_child' => 0,
                                            "hyperguest_pull" => 0
                                        ]);
                                        break;
                                    }
                                }
                            }

                            if (count($s['service_rooms_hyperguest']) > 0) {

                                foreach ($s['service_rooms_hyperguest'] as $r) {
                                    $room = Room::with('room_type')
                                        ->where('id', $r['room_id'])
                                        ->first();
                                    if ($room and $room["room_type"]["occupation"] == 3) {
                                        DB::table('quote_service_rooms_hyperguest')->insert([
                                            'quote_service_id'  => $new_service_id,
                                            'room_id'       => $r['room_id'],
                                            'rate_plan_id'  => $r['rate_plan_id'],
                                            'created_at'    => Carbon::now(),
                                            "updated_at"    => Carbon::now()
                                        ]);

                                        DB::table('quote_services')->where('id', $new_service_id)->update([
                                            "triple"       => 1,
                                            'triple_child' => 0,
                                            "hyperguest_pull" => 1
                                        ]);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                //Todo Agrego los Servicios
                if ($s['type'] == 'service' || $s['type'] == 'flight') {
                    $new_service_id = DB::table('quote_services')->insertGetId([
                        'quote_category_id' => $new_category_id,
                        'type'              => $s['type'],
                        'object_id'         => $s['object_id'],
                        'order'             => ($s['order'] === null) ? 0 : $s['order'],
                        'date_in'           => $date_in,
                        'date_out'          => $date_out,
                        'nights'            => $nigths,
                        'adult'             => $adults,
                        'child'             => $children,
                        'infant'            => 0,
                        'single'            => 0,
                        'double'            => 0,
                        'triple'            => 0,
                        'triple_active'     => 0,
                        'code_flight'       => @$s['code_flight'],
                        'origin'            => @$s['origin'],
                        'destiny'           => @$s['destiny'],
                        'created_at'        => Carbon::now(),
                        "updated_at"        => Carbon::now()
                    ]);

                    //Todo Servicios - ingreso las tarifas
                    if ($s['type'] == 'service' and isset($s['service_rates'][0]['id'])) {
                        DB::table('quote_service_rates')->insert([
                            'quote_service_id' => $new_service_id,
                            'service_rate_id'  => $s['service_rates'][0]['service_rate_id'],
                            'created_at'       => Carbon::now(),
                            "updated_at"       => Carbon::now()
                        ]);
                    }
                }
                $_i++;
            }

            $this->updateDateInServicesInAllCategories($new_object_id);

            //Todo Destinos
            foreach ($package['package_destinations'] as $destiny) {
                DB::table('quote_destinations')->insert([
                    'quote_id'   => $new_object_id,
                    'state_id'   => $destiny['state_id'],
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            //Todo Agrego los pasajeros

            //Todo Pasajeros
            DB::table('quote_people')->insert([
                'adults'     => $adults,
                'child'      => $children,
                'quote_id'   => $new_object_id,
                "created_at" => \Carbon\Carbon::now()
            ]);

            //Todo Agrego los pasajeros Adultos
            for ($i = 1; $i <= $adults; $i++) {
                DB::table('quote_passengers')->insert([
                    'first_name'      => '',
                    'last_name'       => '',
                    'gender'          => null,
                    'birthday'        => null,
                    'document_number' => null,
                    'doctype_iso'     => '',
                    'country_iso'     => '',
                    'email'           => null,
                    'phone'           => null,
                    'notes'           => null,
                    'type'            => 'ADL',
                    'quote_id'        => $new_object_id,
                    "created_at"      => \Carbon\Carbon::now()
                ]);
            }
            //Todo Agrego los pasajeros Niños
            for ($i = 1; $i <= $children; $i++) {
                DB::table('quote_passengers')->insert([
                    'first_name'         => '',
                    'last_name'          => '',
                    'gender'             => null,
                    'birthday'           => null,
                    'document_number'    => null,
                    'doctype_iso'        => '',
                    'country_iso'        => '',
                    'email'              => null,
                    'phone'              => null,
                    'notes'              => null,
                    'type'               => 'CHD',
                    'quote_id'           => $new_object_id,
                    'quote_age_child_id' => $childrenID[$i],
                    "created_at"         => \Carbon\Carbon::now()
                ]);
            }

            DB::table('quote_logs')->insert([
                'quote_id'   => $new_object_id,
                'type'       => 'editing_package',
                'object_id'  => $package['id'],
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            //Todo Recorro los servicios de la cotizacion para poder asignar a los pasajeros a los servicios y hoteles

            //Todo Obtengo los servicios de la cotizacion ya creada
            $quote_services = QuoteService::where('quote_category_id', $new_category_id)->get([
                'id',
                'quote_category_id',
                'object_id',
                'date_in',
                'type',
                'adult',
                'child',
                'infant'
            ]);

            $quote_passengers = QuotePassenger::where('quote_id', $new_object_id)
                ->orderBy('id')
                ->get(['id', 'type']);

            //Todo Recorro todos los servicios y les asigno a todos los pasajeros
            $hotels_collection = collect();
            foreach ($quote_services as $service) {
                //Todo Cuando son servicios se le asigna a todos los pasajeros
                if ($service['type'] == 'service') {
                    foreach ($quote_passengers as $passenger) {
                        DB::table('quote_service_passengers')->insert([
                            "quote_service_id"   => $service['id'],
                            "quote_passenger_id" => $passenger['id'],
                            "created_at"         => \Carbon\Carbon::now()
                        ]);
                    }
                }

                if ($service['type'] == 'hotel') {
                    $hotels_collection->add($service);
                }
            }


            if ($hotels_collection->count() > 0) {
                //Todo Agrupo solo los hoteles de la cotizacion
                $hotels_collection = $hotels_collection->groupBy(function ($item, $key) {
                    $date = convertDate($item["date_in"], '/', '-', 1);

                    return $date . '|' . $item['object_id'];
                });


                foreach ($hotels_collection as $hotel_group) {
                    $_quote_passengers = $quote_passengers->transform(function ($item) {
                        $item->check = false;

                        return $item;
                    });

                    foreach ($hotel_group as $hotel_room) {
                        $inserts = collect();

                        $_quote_passengers_hotel = $_quote_passengers->where('check', false)
                            ->where('type', 'ADL')
                            ->take($hotel_room['adult']);


                        foreach ($_quote_passengers_hotel as $passenger) {
                            $inserts->add([
                                'hotel_id'           => $hotel_room['object_id'],
                                'type_pax'           => $passenger['type'],
                                'quote_service_id'   => $hotel_room['id'],
                                'quote_passenger_id' => $passenger['id'],
                            ]);
                        }

                        foreach ($_quote_passengers as $passenger) {
                            foreach ($_quote_passengers_hotel as $passenger_hotel) {
                                if ($passenger['id'] === $passenger_hotel['id']) {
                                    $passenger['check'] = true;
                                }
                            }
                        }

                        if ($hotel_room['child'] > 0) {
                            $_quote_passengers_hotel = $_quote_passengers->where('check', false)
                                ->where('type', 'CHD')
                                ->take($hotel_room['child']);

                            foreach ($_quote_passengers_hotel as $passenger) {
                                $inserts->add([
                                    'hotel_id'           => $hotel_room['object_id'],
                                    'type_pax'           => $passenger['type'],
                                    'quote_service_id'   => $hotel_room['id'],
                                    'quote_passenger_id' => $passenger['id'],
                                ]);
                            }

                            foreach ($_quote_passengers as $passenger) {
                                foreach ($_quote_passengers_hotel as $passenger_hotel) {
                                    if ($passenger['id'] === $passenger_hotel['id']) {
                                        $passenger['check'] = true;
                                    }
                                }
                            }
                        }

                        //Todo Insertamos los pasajeros en la acomodacion del hotel
                        if ($inserts->count() > 0) {

                            foreach ($inserts as $insert) {
                                DB::table('quote_service_passengers')->insert([
                                    "quote_service_id"   => $insert['quote_service_id'],
                                    "quote_passenger_id" => $insert['quote_passenger_id'],
                                    "created_at"         => \Carbon\Carbon::now(),
                                    "updated_at"         => \Carbon\Carbon::now()
                                ]);
                            }
                        }
                    }
                }
            }

            return $new_object_id;
        });


        $adults = $quantity_persons['adults'];
        $child_with_bed = $quantity_persons['child_with_bed'];
        $child_without_bed = $quantity_persons['child_without_bed'];
        $children = $child_with_bed + $child_without_bed;
        $categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();


        //generamos la tabla quoteDistributions en base a los nuevos cambios que se han hecho o si es que esta tabla esta varia tambien.
        $quotePeople = QuotePeople::where("quote_id", $quote_id)->first();
        $quotePassengers = QuotePassenger::where("quote_id", $quote_id)->orderBy('id')->get()->toArray();
        $quoteAccommodation = QuoteAccommodation::where("quote_id", $quote_id)->first();
        $quoteDistributionsChange = $this->generateQuoteDistributions(
            $quotePassengers,
            $quoteAccommodation->single,
            $quoteAccommodation->double,
            $quoteAccommodation->triple,
            $quotePeople->adults,
            $quotePeople->child
        );
        $this->saveOccupationPassengersHotel($quote_id, $quoteDistributionsChange);
        $quoteDistributions = QuoteDistribution::with('passengers')->where(
            "quote_id",
            $quote_id
        )->orderBy('occupation')->get();
        //ahora la acomodacion de pasajeros la aremos desde esta funcion
        $this->setAccommodationInHotels($categories, (int)$adults, (int)$children, $quoteDistributions);
        // aqui ya no se hace la acomodacion en hoteles
        $this->updateListServicePassengersGeneral($quote_id, (int)$adults, (int)$children);
        $this->updateAmountAllServices($quote_id, $client_id);

        return Response::json(['success' => true, 'quote_id' => $quote_id]);
        // return Response::json(['success' => true, 'quote_id' => Cache::get('quote_id_otas_generic')]);

    }

    public function changeHotels($package_services, $hotels_changed)
    {
        if (count($hotels_changed) > 0) {
            foreach ($package_services as $key => $service) {
                if ($service["type"] == "hotel") {
                    foreach ($hotels_changed as $hotel) {
                        if ($service['id'] === $hotel['package_service_id']) {

                            $package_services[$key]['object_id'] = $hotel['hotel_id'];
                            $service_rooms = [];
                            $package_services[$key]['service_rooms'] = [];
                            $package_services[$key]['service_rooms_hyperguest'] = [];
                            foreach ($hotel['rooms'] as $room) {

                                if ($room['hyperguest_pull'] != 1) {

                                    array_push($package_services[$key]['service_rooms'], [
                                        'rate_plan_room_id'  => $room['rate_plan_room_id'],
                                        'package_service_id' => $service['id'],
                                    ]);
                                } else {
                                    array_push($package_services[$key]['service_rooms_hyperguest'], [
                                        'room_id'  => $room['room_id'],
                                        'rate_plan_id'  => $room['rate_plan_id'],
                                        'package_service_id' => $service['id'],
                                    ]);
                                }
                            }



                            // if($hotel['hyperguest_pull'] != 1 )
                            // {
                            //     foreach ($hotel['rooms'] as $room) {
                            //         $service_rooms[] = [
                            //             'rate_plan_room_id'  => $room['rate_plan_room_id'],
                            //             'package_service_id' => $service['id'],
                            //         ];
                            //     }
                            //     $package_services[$key]['service_rooms'] = $service_rooms;
                            // }else{
                            //     foreach ($hotel['rooms'] as $room) {
                            //         $service_rooms[] = [
                            //             'room_id'  => $room['room_id'],
                            //             'rate_plan_id'  => $room['rate_plan_id'],
                            //             'package_service_id' => $service['id'],
                            //         ];
                            //     }
                            //     $package_services[$key]['service_rooms_hyperguest'] = $service_rooms;
                            // }
                        }
                    }
                }
            }
        }

        return $package_services;
    }

    public function searchExistByUserStatus($status)
    {

        $user_id = Auth::user()->id;

        $quote = Quote::where('user_id', $user_id)
            ->where('status', $status);

        $value = false;
        $quote_open_id = null;
        if ($quote->count() > 0) {
            $value = true;
            $quote = $quote->first();
            $query_log = QuoteLog::where('quote_id', $quote->id)->orderBy('created_at', 'desc')->first(['id', 'quote_id', 'type', 'object_id', 'user_id']);
            if ($query_log) {
                $quote_open_id = $query_log->quote_id;
            }
        }

        return Response::json(['success' => $value, 'quote_open_id' => $quote_open_id]);
    }

    public function add_client_extension(Request $request)
    {
        $extension_id = $request->post('extension_id');
        $service_type_id = $request->post('service_type_id');
        $type_class_id = $request->post('type_class_id');
        $quote_id = $request->post('quote_id');
        $category_ids = $request->post('category_ids');
        $extension_date = $request->post('extension_date');
        $extension_date = Carbon::parse($extension_date)->format('Y-m-d');

        // no se permite el ingreso de extrensiones con del mismo codigo mas de 1 vez por categoría, pero si puede agregar las extensiones que quiera  en una categoría
        $quote_services = DB::table('quote_services')
            ->whereIn('quote_category_id', $category_ids)->orderBy('date_in')->orderBy('order')
            ->get();

        $services_extension_exists = $quote_services->first(function ($item) use ($extension_date, $extension_id) {
            return $item->new_extension_id == $extension_id;
        });

        if ($services_extension_exists) {
            $response = ['success' => false, "error" => "The extension already exists for this category"];

            return Response::json($response, 200);
        }

        $services_extension_medium = $quote_services->first(function ($item) use ($extension_date) {
            return ($extension_date == $item->date_in) and ($item->new_extension_id != '');
        });

        if ($services_extension_medium) {
            $response = ['success' => false, "error" => "An extension already exists on the selected date"];

            return Response::json($response, 200);
        }


        $package_plan_rate = DB::table('package_plan_rates')
            ->where('package_id', $extension_id)
            ->where('service_type_id', $service_type_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->whereDate('date_from', '<=', $extension_date)
            ->whereDate('date_to', '>=', $extension_date)
            ->orderBy('date_from', 'desc')
            ->first();

        if (!$package_plan_rate) {
            $response = ['success' => false, 'error' => 'No active plan rate for the selected extension date'];
            return Response::json($response, 200);
        }

        $package_category = DB::table('package_plan_rate_categories')
            ->where('package_plan_rate_id', $package_plan_rate->id)
            ->where('type_class_id', $type_class_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$package_category) {
            $response = ['success' => false, 'error' => 'No plan rate category for the selected extension date'];
            return Response::json($response, 200);
        }

        DB::transaction(function () use (
            $extension_id,
            $quote_id,
            $category_ids,
            $extension_date,
            $package_category
        ) {

            $package_services = DB::table('package_services')
                ->where('package_plan_rate_category_id', $package_category->id)
                ->whereNull('deleted_at')
                ->orderBy('date_in')
                ->get();
            $quote = DB::table('quotes')
                ->where('id', $quote_id)
                ->first();
            $people = DB::table('quote_people')
                ->where('quote_id', $quote_id);
            if ($people->count() == 0) {
                DB::table('quote_people')->insert([
                    "adults"   => 1,
                    "child"    => 0,
                    "quote_id" => $quote_id
                ]);
                $people = DB::table('quote_people')
                    ->where('quote_id', $quote_id)->first();
            } else {
                $people = $people->first();
            }

            $pivot_package_service_date_in = $package_services[0]->date_in;

            $histories = [];
            foreach ($category_ids as $category_id) {
                $max_date_in = $extension_date;
                $service_parent_id = null;
                for ($i = 0; $i < $package_services->count(); $i++) {
                    if ($i == 0) {
                        if ($package_services[$i]->type == "service") {
                            $service_parent_id = $this->addServiceFromExtension(
                                $package_services[$i],
                                $max_date_in,
                                $category_id,
                                $people->adults,
                                $people->child,
                                $extension_id,
                                null
                            );
                        }
                        if ($package_services[$i]->type == "hotel") {
                            $difference_days_hotel = Carbon::parse($package_services[$i]->date_in)->diffInDays(Carbon::parse($package_services[$i]->date_out));
                            $date_out = Carbon::parse($max_date_in)->addDays($difference_days_hotel)->format('Y-m-d');
                            $service_parent_id = $this->addHotelFromExtension(
                                $package_services[$i],
                                $category_id,
                                $max_date_in,
                                $date_out,
                                $people->adults,
                                $people->child,
                                $extension_id,
                                null
                            );
                        }
                    } else {
                        $difference_days = Carbon::parse($pivot_package_service_date_in)->diffInDays(Carbon::parse($package_services[$i]->date_in));

                        if ($package_services[$i]->type == "service") {
                            $date_in_service = Carbon::parse($max_date_in)->addDays($difference_days)->format('Y-m-d');
                            $this->addServiceFromExtension(
                                $package_services[$i],
                                $date_in_service,
                                $category_id,
                                $people->adults,
                                $people->child,
                                $extension_id,
                                null
                            );
                        }

                        if ($package_services[$i]->type == "hotel") {
                            $difference_days_hotel = Carbon::parse($package_services[$i]->date_in)->diffInDays(Carbon::parse($package_services[$i]->date_out));
                            $date_in = Carbon::parse($max_date_in)->addDays($difference_days)->format('Y-m-d');
                            $date_out = Carbon::parse($date_in)->addDays($difference_days_hotel)->format('Y-m-d');
                            $this->addHotelFromExtension(
                                $package_services[$i],
                                $category_id,
                                $date_in,
                                $date_out,
                                $people->adults,
                                $people->child,
                                $extension_id,
                                null
                            );
                        }
                    }
                }

                $type_class_id_ = QuoteCategory::find($category_id)->type_class_id;

                $service_extension_ = Package::find($extension_id);
                $service_code_ = ($service_extension_) ? $service_extension_->code : null;
                if ($service_extension_ && $service_code_ == null) {
                    $service_code_ = "E" . $service_extension_->id;
                }

                array_push($histories, [
                    "type"          => "store",
                    "slug"          => "store_extension",
                    "previous_data" => "",
                    "current_data"  => json_encode([
                        "type_class_id" => $type_class_id_,
                        "type_service"  => "extension",
                        "service_code"  => $service_code_,
                        "object_id"     => $extension_id,
                        "date_in"       => convertDate($extension_date, "-", "/", 1)
                    ]),
                    "description" => "Agregó extensión"
                ]);
            }

            if (count($histories) > 0) {
                $this->store_history_logs($quote_id, $histories);
            }
        });

        foreach ($category_ids as $category_id) {
            $orden = 1;
            $services_orden = [];

            $services = $services = QuoteService::where('quote_category_id', $category_id)->orderBy('date_in')->orderBy('order')->get();

            $services_before = $services->filter(function ($item) use ($extension_date, $extension_id) {
                return ($item->date_in_format <= $extension_date) and ($item->new_extension_id != $extension_id);
            });

            $extensions = $services->filter(function ($item) use ($extension_id) {
                return $item->new_extension_id == $extension_id;
            });

            $services_after = $services->filter(function ($item) use ($extension_date, $extension_id) {
                return ($item->date_in_format > $extension_date) and ($item->new_extension_id != $extension_id);
            }); //->sortBy('date_in_format')


            foreach ($services_before as $serviceBefore) {
                array_push($services_orden, [
                    'id'               => $serviceBefore->id,
                    'type'             => $serviceBefore->type,
                    'object_id'        => $serviceBefore->object_id,
                    'date_in'          => Carbon::createFromFormat('d/m/Y', $serviceBefore->date_in)->format('Y-m-d'),
                    'date_out'         => Carbon::createFromFormat('d/m/Y', $serviceBefore->date_out)->format('Y-m-d'),
                    'nights'           => $serviceBefore->nights,
                    'new_extension_id' => $serviceBefore->new_extension_id,
                    'orden'            => $orden
                ]);
                $orden++;
            }

            $extension_date_end = '';
            foreach ($extensions as $extension) {
                array_push($services_orden, [
                    'id'               => $extension->id,
                    'type'             => $extension->type,
                    'object_id'        => $extension->object_id,
                    'date_in'          => Carbon::createFromFormat('d/m/Y', $extension->date_in)->format('Y-m-d'),
                    'date_out'         => Carbon::createFromFormat('d/m/Y', $extension->date_out)->format('Y-m-d'),
                    'nights'           => $extension->nights,
                    'new_extension_id' => $extension->new_extension_id,
                    'orden'            => $orden
                ]);
                $orden++;
                $extension_date_end = $extension->date_in_format;
            }

            // coloca adelante de la extension todos los servicios

            $date_in_prefix = '';
            foreach ($services_after as $servicesAfter) {

                if ($date_in_prefix != $servicesAfter->date_in_format) {
                    $extension_date_end = $date_out = Carbon::parse($extension_date_end)->addDays(1)->format('Y-m-d');
                    $date_in_prefix = $servicesAfter->date_in_format;
                }

                if ($servicesAfter->type == 'service') {
                    $date_out = $extension_date_end;
                } else {
                    $date_out = Carbon::parse($extension_date_end)->addDays($servicesAfter->nights)->format('Y-m-d');
                }

                array_push($services_orden, [
                    'id'               => $servicesAfter->id,
                    'type'             => $servicesAfter->type,
                    'object_id'        => $servicesAfter->object_id,
                    'date_in'          => $extension_date_end,
                    'date_out'         => $date_out,
                    'nights'           => $servicesAfter->nights,
                    'new_extension_id' => $servicesAfter->new_extension_id,
                    'orden'            => $orden
                ]);
                $orden++;
            }


            foreach ($services_orden as $service) {
                $quote_service = QuoteService::find($service['id']);
                $quote_service->date_in = $service['date_in'];
                $quote_service->date_out = $service['date_out'];
                $quote_service->order = $service['orden'];
                $quote_service->save();
            }


            // dd($services_orden);


            // $extension_id
            // $this->updateOrderAndDateServices($services);
        }

        $response = ['success' => true, "message" => "Extension Agregada"];

        return Response::json($response, 200);
    }

    public function update_extension(Request $request)
    {

        $category_id = $request->input('category_id');
        $extension_date = $request->input('date');
        $extension_id = $request->input('extension_id');


        $orden = 1;
        $services_orden = [];

        $services = $services = QuoteService::where('quote_category_id', $category_id)->orderBy('date_in')->orderBy('order')->get();

        $services_before = $services->filter(function ($item) use ($extension_date, $extension_id) {
            return ($item->date_in_format <= $extension_date) and ($item->new_extension_id != $extension_id);
        });

        $extensions = $services->filter(function ($item) use ($extension_id) {
            return $item->new_extension_id == $extension_id;
        });

        $services_after = $services->filter(function ($item) use ($extension_date, $extension_id) {
            return ($item->date_in_format > $extension_date) and ($item->new_extension_id != $extension_id);
        }); //->sortBy('date_in_format')


        foreach ($services_before as $serviceBefore) {
            array_push($services_orden, [
                'id'               => $serviceBefore->id,
                'type'             => $serviceBefore->type,
                'object_id'        => $serviceBefore->object_id,
                'date_in'          => Carbon::createFromFormat('d/m/Y', $serviceBefore->date_in)->format('Y-m-d'),
                'date_out'         => Carbon::createFromFormat('d/m/Y', $serviceBefore->date_out)->format('Y-m-d'),
                'nights'           => $serviceBefore->nights,
                'new_extension_id' => $serviceBefore->new_extension_id,
                'orden'            => $orden
            ]);
            $orden++;
        }

        $extension_date_end = $extension_date;
        $date_in_prefix = '';
        $index = 0;
        foreach ($extensions as $extension) {

            if ($date_in_prefix != $extension->date_in_format) {

                if ($index > 0) {
                    $extension_date_end = $date_out = Carbon::parse($extension_date_end)->addDays(1)->format('Y-m-d');
                }

                $date_in_prefix = $extension->date_in_format;
            }

            if ($extension->type == 'service') {
                $date_out = $extension_date_end;
            } else {
                $date_out = Carbon::parse($extension_date_end)->addDays($extension->nights)->format('Y-m-d');
            }

            array_push($services_orden, [
                'id'               => $extension->id,
                'type'             => $extension->type,
                'object_id'        => $extension->object_id,
                'date_in'          => $extension_date_end,
                'date_out'         => $date_out,
                'nights'           => $extension->nights,
                'new_extension_id' => $extension->new_extension_id,
                'orden'            => $orden,
                'index'            => $index
            ]);
            $orden++;
            $index++;
            // $extension_date_end = $extension->date_in_format;
        }

        // coloca adelante de la extension todos los servicios

        $date_in_prefix = '';
        foreach ($services_after as $servicesAfter) {

            if ($date_in_prefix != $servicesAfter->date_in_format) {
                $extension_date_end = $date_out = Carbon::parse($extension_date_end)->addDays(1)->format('Y-m-d');
                $date_in_prefix = $servicesAfter->date_in_format;
            }

            if ($servicesAfter->type == 'service') {
                $date_out = $extension_date_end;
            } else {
                $date_out = Carbon::parse($extension_date_end)->addDays($servicesAfter->nights)->format('Y-m-d');
            }

            array_push($services_orden, [
                'id'               => $servicesAfter->id,
                'type'             => $servicesAfter->type,
                'object_id'        => $servicesAfter->object_id,
                'date_in'          => $extension_date_end,
                'date_out'         => $date_out,
                'nights'           => $servicesAfter->nights,
                'new_extension_id' => $servicesAfter->new_extension_id,
                'orden'            => $orden
            ]);
            $orden++;
        }


        foreach ($services_orden as $service) {
            $quote_service = QuoteService::find($service['id']);
            $quote_service->date_in = $service['date_in'];
            $quote_service->date_out = $service['date_out'];
            $quote_service->order = $service['orden'];
            $quote_service->save();
        }


        // dd($services_orden);


        // $extension_id
        // $this->updateOrderAndDateServices($services);

        $response = ['success' => true, "message" => "Extension Actualizada", "data" => $services_orden];

        return Response::json($response, 200);
    }

    public function statements(Request $request, $quote_id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $type_class_id = $request->__get('type_class_id');
            $client_id = $request->__get('client_id');
            $client = Client::select('code', 'email', 'name', 'business_name')
                ->where('id', '=', $client_id)->first()->toArray();

            $this->updateAmountAllServices($quote_id, $client_id, false);

            $quote = Quote::with(['people'])->where('id', '=', $quote_id)->first();

            if ($quote) {
                $quote = $quote->toArray();

                $category = QuoteCategory::where('quote_id', '=', $quote_id)
                    ->where('type_class_id', '=', $type_class_id)->first()->toArray();

                $services = QuoteService::with([
                    'amount',
                    'service_rooms.rate_plan_room',
                    'service_rooms.rate_plan_room.policies_cancelation'
                ])
                    ->where('quote_category_id', '=', $category['id'])->get()->toArray();
                $amounts = [];

                $guest_quantity = $quote['people'][0]['adults'] + $quote['people'][0]['child'];

                $min_date = '';
                $current_date = Carbon::now();
                $format = (strpos($quote['date_in'], '/')) ? 'd/m/Y' : 'Y-m-d';
                $quote_date_in = Carbon::createFromFormat($format, $quote['date_in']);

                $total = 0;
                $total_without_markup = 0;
                $format_min_date = '';
                $policies = [];

                foreach ($services as $key => $value) {
                    $format = (strpos($value['date_in'], '/')) ? 'd/m/Y' : 'Y-m-d';

                    $amount = 0;

                    foreach ($value['amount'] as $item) {
                        $amount += $item['price_per_night'];
                    }

                    $date_in = Carbon::createFromFormat($format, $value['date_in']);
                    $date_out = Carbon::createFromFormat($format, $value['date_out']);

                    if (
                        $value['type'] == 'hotel' and ($value['single'] > 0 or $value['double'] > 0
                            or $value['double_child'] > 0 or $value['triple'] > 0 or $value['triple_child'] > 0)
                    ) {
                        $rooms = $value['service_rooms'];
                        $guest_quantity = $value['adult'] + $value['child'];
                        // $date_in = Carbon::parse($value['date_in'], $format); $date_out = Carbon::parse($value['date_out'], $format);

                        foreach ($rooms as $key => $room) {
                            $rate_plan_room = $room['rate_plan_room'];

                            $calendarys = RatesPlansCalendarys::with([
                                'policies_cancelation.policy_cancellation_parameter.penalty',
                                'policies_rates.policies_cancelation.policy_cancellation_parameter.penalty'
                            ])
                                ->where('rates_plans_room_id', '=', $rate_plan_room['id'])
                                ->where('date', '>=', $date_in)
                                ->where('date', '<=', $date_out)
                                ->where('status', '=', 1)->get()->toArray();

                            if ($rate_plan_room['channel_id'] == 1) {
                                $selected_policies_cancelation = collect($calendarys[0]['policies_rates']['policies_cancelation']);
                            } else {
                                if (count($rate_plan_room['policies_cancelation']) == 0) {
                                    $selected_policies_cancelation = collect($calendarys[0]["policies_cancelation"]);
                                } else {
                                    $selected_policies_cancelation = collect($rate_plan_room['policies_cancelation']);
                                }
                            };

                            $policies = $this->calculateCancellationPolicies(
                                $current_date,
                                $date_in,
                                $date_out,
                                $amount,
                                $selected_policies_cancelation,
                                $guest_quantity,
                                count($rooms)
                            );

                            // print_r($policies); die;

                            if ($policies['next_penalty']['apply_date'] < $min_date or $min_date == '') {
                                $min_date = $policies['next_penalty']['apply_date'];
                                $format_min_date = (strpos($min_date, '/')) ? 'd/m/Y' : 'd-m-Y';
                            }
                        }
                    }

                    $amounts[] = $value['amount'];

                    foreach ($value['amount'] as $amount) {
                        $total += (roundLito($amount['price_adult']) * $value['adult']);

                        if ($value['child'] > 0) {
                            $total += (roundLito($amount['price_child']) * $value['child']);
                        }

                        $total_without_markup += (roundLito($amount['price_adult_without_markup']) * $value['adult']);

                        if ($value['child'] > 0) {
                            $total_without_markup += (roundLito($amount['price_child_without_markup']) * $value['child']);
                        }
                    }
                }

                // if (config('app.env') == 'production') {
                //     $search_credit = $this->toArray($this->stellaService->clients_verify_credit($client['code']));
                // } else {
                //     $search_credit = [
                //         [
                //             'flag_credit' => 1
                //         ]
                //     ];
                // }

                $search_credit = [
                    [
                        'flag_credit' => 1
                    ]
                ];

                $quote['date_out'] = $date_out;
                $quote['date_out_format'] = Carbon::parse($date_out)->format('Y-m-d');
                $quote['nights'] = $date_out->diff($quote_date_in)->days;

                return response()->json([
                    'client'                => $client,
                    'flag_credit'           => $search_credit[0]['flag_credit'],
                    'type'                  => 'success',
                    'quote'                 => $quote,
                    'total'                 => $total,
                    'total_without_markup'  => $total_without_markup,
                    'category'              => $category,
                    'services'              => $services,
                    'amounts'               => $amounts,
                    'min_date'              => $min_date,
                    'format_date'           => $format_min_date,
                    'policies'              => $policies,
                    'min_date_cancellation' => ($min_date != '') ? Carbon::createFromFormat(
                        $format_min_date,
                        $min_date
                    )->format("Y-m-d") : '',
                ]);
            } else {
                return response()->json([
                    'client'  => $client,
                    'type'    => 'error',
                    'quote'   => $quote,
                    'message' => 'No se encontró la cotización..',
                ]);
            }
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function share($quote_id, Request $request)
    {
        $user_id = Auth::user()->id;
        $username = Auth::user()->name;
        $date_in = Carbon::now()->format('d-m-Y');
        $send_notification = ($request->has('send_notification')) ? $request->input('send_notification') : 0;
        $clients_selected = $request->input('clients_selected');
        $client_seller_selected = $request->input('client_seller_selected');
        $permission_selected = $request->input('permission_selected');


        DB::transaction(function () use (
            $quote_id,
            $username,
            $user_id,
            $permission_selected,
            $date_in,
            $send_notification,
            $clients_selected,
            $client_seller_selected
        ) {

            $edit_permission = false;

            if ($permission_selected == "edit_permission") {
                $edit_permission = true;
            }
            ShareQuote::where('quote_id', $quote_id)->where('client_id', $clients_selected["code"])->where(
                'seller_id',
                $client_seller_selected["client_seller"]["user_id"]
            )->where('user_id', $user_id)->delete();

            Quote::where('id', $quote_id)->update([
                'shared' => 1
            ]);
            ShareQuote::create([
                'quote_id'        => $quote_id,
                'view_permission' => true,
                'edit_permission' => $edit_permission,
                'client_id'       => $clients_selected["code"],
                'seller_id'       => $client_seller_selected["client_seller"]["user_id"],
                'user_id'         => $user_id
            ]);

            if ($send_notification == 1) {
                $quote_name = Quote::where('id', $quote_id)->first()->name;
                $client = Client::find($clients_selected["code"]);

                $data = [
                    'user_from'    => $username,
                    'quote_id'     => $quote_id,
                    'quote_date'   => $date_in,
                    'quote_name'   => $quote_name,
                    'quote_client' => $clients_selected["label"] . " " . $client_seller_selected["name"],
                    'quote_cities' => '',
                ];
                $user = User::find($user_id);
                if ($user) {
                    Mail::to($user->email)->send(
                        new NotificationSharedQuote($data)
                    );
                }
                if ($client->email != null) {
                    Mail::to($client->email)->send(
                        new NotificationSharedQuote($data)
                    );
                }


                if ($client_seller_selected["email"] != null) {
                    Mail::to($client_seller_selected["email"])->send(
                        new NotificationSharedQuote($data)
                    );
                }
            }
        });


        return Response::json(['success' => true]);
    }

    public function checkEditing($quote_id)
    {
        $response = [
            'editing' => false,
            'user'    => null,
        ];

        $get_quote_editing = QuoteLog::where('object_id', $quote_id)
            ->where('type', 'editing_quote')
            ->with([
                'user' => function ($query) {
                    $query->select(['id', 'name', 'code', 'email']);
                }
            ])
            ->orderBy('created_at', 'asc')
            ->first(['id', 'quote_id', 'type', 'object_id', 'user_id']);
        if ($get_quote_editing and !empty($get_quote_editing->user)) {
            $response = [
                'editing' => true,
                'user'    => $get_quote_editing->user
            ];
        }

        return response()->json($response);
    }


    public function replaceQuoteInFront($quote_id_original, Request $request)
    {

        $client_id = $this->getClientId($request);
        $markup = 0;
        $markup_hotel = DB::table('markups')
            ->whereNull('deleted_at')
            ->where('client_id', $client_id)
            ->where('period', Carbon::now()->year)
            ->first();

        if ($markup_hotel != null) {
            $markup = $markup_hotel->hotel;
        }

        $quote_front = [];

        DB::table('quotes')->where('status', 2)->where('user_id', Auth::user()->id)->update([
            'markup' => $markup
        ]);

        $quote_front = DB::table('quotes')
            ->whereNull('deleted_at')
            ->where('status', 2)
            ->where('user_id', Auth::user()->id)->first([
                'id',
                'code',
                'name',
                'date_in',
                'cities',
                'nights',
                'estimated_price',
                'user_id',
                'service_type_id',
                'status',
                'discount',
                'discount_detail',
                'discount_user_permission',
                'order_related',
                'order_position',
                'show_in_popup',
                'created_at',
                'updated_at',
                'operation',
                'markup',
                'shared',
                'estimated_travel_date',
                'file_id',
                'file_number',
                'file_total_amount'
            ]);

        //        dd($quote_id_original);

        $this->replaceQuote($quote_id_original, $quote_front->id);

        return Response::json(['success' => true, 'user' => Auth::user()->id, 'quote_front' => $quote_front]);
    }

    public function copy($quote_id, Request $request)
    {
        $to_status = ($request->input('status')) ? $request->input('status') : 1;
        $user_id = ($request->input('user_id')) ? $request->input('user_id') : Auth::user()->id;
        $new_name = $request->input('name');
        $date_in = $request->input('date_in');
        $send_notification = ($request->has('send_notification')) ? $request->input('send_notification') : 0;

        $client_id = $this->getClientId($request);

        $new_object_id = DB::transaction(function () use (
            $quote_id,
            $to_status,
            $user_id,
            $new_name,
            $date_in,
            $send_notification,
            $client_id
        ) {

            QuotePassenger::where('quote_id', $quote_id)->update([
                'quote_passenger_id' => null
            ]);

            $quote_for_copy = Quote::where('id', $quote_id)
                ->with([
                    'categories',
                    'ranges',
                    'notes',
                    'passengers',
                    'people',
                    'age_child',
                    'destinations',
                    'accommodation',
                    'distributions.passengers'
                ])->first();

            if ($to_status == 2) {
                $this->setObjectId($quote_id);
            }


            if ($new_name == '') {
                $quote_for_copy_name = $quote_for_copy->name;
                if ($to_status == 1 and $user_id == Auth::user()->id) {
                    $quote_for_copy_name = $quote_for_copy_name . " (Copia)";
                }
            } else {
                $quote_for_copy_name = $new_name;
            }

            if ($date_in == '') {
                $date_in = $quote_for_copy->date_in;
            }

            $date_estimated = $date_in;

            if ($date_in == '') {
                $date_estimated = $quote_for_copy->date_estimated;
            }

            $this->newQuote(
                $quote_for_copy_name,
                $date_in,
                $date_estimated,
                $quote_for_copy->nights,
                $quote_for_copy->service_type_id,
                $user_id,
                [],
                [],
                [],
                [],
                [],
                $quote_for_copy->operation,
                $to_status,
                $quote_for_copy->markup,
                $quote_for_copy->discount,
                $quote_for_copy->discount_detail,
                null,
                null,
                $quote_for_copy->file_id,
                $quote_for_copy->file_number,
                $quote_for_copy->file_total_amount,
                $quote_for_copy->is_multiregion
            );
            $new_object_id = $this->getObjectId();

            foreach ($quote_for_copy->categories as $c) {
                $new_category_id = DB::table('quote_categories')->insertGetId([
                    'quote_id'      => $new_object_id,
                    'type_class_id' => $c->type_class_id,
                    'created_at'    => Carbon::now(),
                    "updated_at"    => Carbon::now()
                ]);

                $c_services = QuoteService::where('quote_category_id', $c->id)
                    ->with([
                        'service_rate' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'service_rate_id']);
                        }
                    ])
                    ->with([
                        'service_rooms' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'rate_plan_room_id']);
                        }
                    ])
                    ->with([
                        'service_rooms_hyperguest' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'room_id', 'rate_plan_id']);
                        }
                    ])
                    ->with(['amount'])
                    ->with([
                        'passengers' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                        }
                    ])
                    ->orderBy('date_in', 'asc')
                    ->orderBy('order', 'asc')
                    ->get();

                //Todo Arreglo para agregar las extensiones
                $array_quote_services_id_extensions = [];

                //Todo Buscamos las extensiones
                foreach ($c_services as $s) {
                    if ($quote_for_copy->file_id) { // cuado son cotizaciones que se han generado de files a3
                        $locked = $s->locked;
                    } else {
                        $locked = 0;
                        if ($to_status === 2) {
                            $locked = $s->locked;
                        }
                    }

                    if ($s->extension_id !== null && $s->parent_service_id == null) {
                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $new_category_id,
                            'type'              => $s->type,
                            'object_id'         => $s->object_id,
                            'order'             => $s->order,
                            'date_in'           => convertDate($s->date_in, '/', '-', 1),
                            'date_out'          => convertDate($s->date_out, '/', '-', 1),
                            'hour_in'           => $s->hour_in,
                            'nights'            => $s->nights,
                            'adult'             => $s->adult,
                            'child'             => $s->child,
                            'infant'            => $s->infant,
                            'single'            => $s->single,
                            'double'            => $s->double,
                            'triple'            => $s->triple,
                            'locked'            => $locked,
                            'triple_active'     => $s->triple_active,
                            'on_request'        => $s->on_request,
                            'optional'          => (int)$s->optional,
                            'code_flight'       => @$s->code_flight,
                            'origin'            => @$s->origin,
                            'destiny'           => @$s->destiny,
                            'extension_id'      => $s->extension_id,
                            'new_extension_id'  => $s->new_extension_id,
                            'parent_service_id' => null,
                            'schedule_id'       => $s->schedule_id,

                            'is_file'           => $s->is_file,
                            'file_itinerary_id' => $s->file_itinerary_id,
                            'file_status'       => $s->file_status,
                            'file_amount_sale'  => $s->file_amount_sale,
                            'file_amount_cost'  => $s->file_amount_cost,
                            'hyperguest_pull'   => $s->hyperguest_pull,
                            'markup_regionalization' => $s->markup_regionalization ? $s->markup_regionalization : 0,

                            'created_at' => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);

                        $array_quote_services_id_extensions[] = [
                            "quote_service_id_old" => $s->id,
                            "quote_service_id_new" => $new_service_id
                        ];
                    }
                }

                foreach ($c_services as $s) {
                    if ($quote_for_copy->file_id) { // cuado son cotizaciones que se han generado de files a3
                        $locked = $s->locked;
                    } else {

                        $locked = 0;
                        if ($to_status === 2) {
                            $locked = $s->locked;
                        }
                    }
                    //Todo Verifico si el servicio pertenece a la extension (parent_service_id)
                    if ($s->extension_id == null && $s->parent_service_id !== null) {
                        $new_parent_service_id = null;

                        foreach ($array_quote_services_id_extensions as $quote_services_id_extension) {
                            if ($s->parent_service_id == $quote_services_id_extension["quote_service_id_old"]) {
                                $new_parent_service_id = $quote_services_id_extension["quote_service_id_new"];
                            }
                        }
                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $new_category_id,
                            'type'              => $s->type,
                            'object_id'         => $s->object_id,
                            'order'             => $s->order,
                            'date_in'           => convertDate($s->date_in, '/', '-', 1),
                            'date_out'          => convertDate($s->date_out, '/', '-', 1),
                            'hour_in'           => $s->hour_in,
                            'nights'            => $s->nights,
                            'adult'             => $s->adult,
                            'child'             => $s->child,
                            'infant'            => $s->infant,
                            'single'            => $s->single,
                            'double'            => $s->double,
                            'triple'            => $s->triple,
                            'locked'            => $locked,
                            'triple_active'     => $s->triple_active,
                            'on_request'        => $s->on_request,
                            'optional'          => (int)$s->optional,
                            'code_flight'       => @$s->code_flight,
                            'origin'            => @$s->origin,
                            'destiny'           => @$s->destiny,
                            'extension_id'      => null,
                            'new_extension_id'  => $s->new_extension_id,
                            'parent_service_id' => $new_parent_service_id,
                            'schedule_id'       => $s->schedule_id,

                            'is_file'           => $s->is_file,
                            'file_itinerary_id' => $s->file_itinerary_id,
                            'file_status'       => $s->file_status,
                            'file_amount_sale'  => $s->file_amount_sale,
                            'file_amount_cost'  => $s->file_amount_cost,
                            'hyperguest_pull'   => $s->hyperguest_pull,
                            'markup_regionalization' => $s->markup_regionalization ? $s->markup_regionalization : 0,
                            'created_at' => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                    }
                    //Todo Verifico si el servicio no es una extension
                    if ($s->extension_id == null && $s->parent_service_id == null) {
                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $new_category_id,
                            'type'              => $s->type,
                            'object_id'         => $s->object_id,
                            'order'             => $s->order,
                            'date_in'           => convertDate($s->date_in, '/', '-', 1),
                            'date_out'          => convertDate($s->date_out, '/', '-', 1),
                            'hour_in'           => $s->hour_in,
                            'nights'            => $s->nights,
                            'adult'             => $s->adult,
                            'child'             => $s->child,
                            'infant'            => $s->infant,
                            'single'            => $s->single,
                            'double'            => $s->double,
                            'triple'            => $s->triple,
                            'locked'            => $locked,
                            'triple_active'     => $s->triple_active,
                            'on_request'        => $s->on_request,
                            'optional'          => (int)$s->optional,
                            'code_flight'       => @$s->code_flight,
                            'origin'            => @$s->origin,
                            'destiny'           => @$s->destiny,
                            'extension_id'      => null,
                            'new_extension_id'  => $s->new_extension_id,
                            'parent_service_id' => null,
                            'schedule_id'       => $s->schedule_id,

                            'is_file'           => $s->is_file,
                            'file_itinerary_id' => $s->file_itinerary_id,
                            'file_status'       => $s->file_status,
                            'file_amount_sale'  => $s->file_amount_sale,
                            'file_amount_cost'  => $s->file_amount_cost,
                            'hyperguest_pull'   => $s->hyperguest_pull,
                            'markup_regionalization' => $s->markup_regionalization ? $s->markup_regionalization : 0,

                            'created_at' => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                    }

                    if ($s->extension_id !== null && $s->parent_service_id == null) {
                        foreach ($array_quote_services_id_extensions as $quote_services_id_extension) {
                            if ($s->id == $quote_services_id_extension["quote_service_id_old"]) {
                                $new_service_id = $quote_services_id_extension["quote_service_id_new"];
                            }
                        }
                    }

                    if ($s->type == 'service' and isset($s->service_rate->id)) {
                        DB::table('quote_service_rates')->insert([
                            'quote_service_id' => $new_service_id,
                            'service_rate_id'  => $s->service_rate->service_rate_id,
                            'created_at'       => Carbon::now(),
                            "updated_at"       => Carbon::now()
                        ]);
                    }
                    if ($s->type == 'hotel') {
                        foreach ($s->service_rooms as $r) {
                            DB::table('quote_service_rooms')->insert([
                                'quote_service_id'  => $new_service_id,
                                'rate_plan_room_id' => $r->rate_plan_room_id,
                                'created_at'        => Carbon::now(),
                                "updated_at"        => Carbon::now()
                            ]);
                        }
                        foreach ($s->service_rooms_hyperguest as $r) {
                            DB::table('quote_service_rooms_hyperguest')->insert([
                                'quote_service_id'  => $new_service_id,
                                'room_id'           => $r->room_id,
                                'rate_plan_id'      => $r->rate_plan_id,
                                'created_at'        => Carbon::now(),
                                "updated_at"        => Carbon::now()
                            ]);
                        }

                        foreach ($s->amount as $amount) {
                            DB::table('quote_service_amounts')->insert([
                                'quote_service_id'  => $new_service_id,
                                'date_service'      => $amount->date_service,
                                'price_per_night_without_markup'      => $amount->price_per_night_without_markup,
                                'price_per_night'      => $amount->price_per_night,
                                'price_adult_without_markup'      => $amount->price_adult_without_markup,
                                'price_adult'      => $amount->price_adult,
                                'price_child_without_markup'      => $amount->price_child_without_markup,
                                'price_child'      => $amount->price_child,
                                'price_teenagers_without_markup'      => $amount->price_teenagers_without_markup ? $amount->price_teenagers_without_markup : 0,
                                'price_teenagers'      => $amount->price_teenagers ? $amount->price_teenagers : 0

                            ]);
                        }
                    }

                    /*
                     * Todo Creamos la asignacion de pasajeros con el quote_passenger_id de la cotizacion copia
                     *  para luego actualizarlo en el metodo copyServicePassengersFromQuote
                     */
                    foreach ($s->passengers as $passenger) {
                        DB::table('quote_service_passengers')->insert([
                            'quote_service_id'   => $new_service_id,
                            'quote_passenger_id' => $passenger->quote_passenger_id,
                            'created_at'         => Carbon::now(),
                            "updated_at"         => Carbon::now()
                        ]);
                    }
                }
            }

            $this->updateDateInServicesInAllCategories($new_object_id);

            if ($quote_for_copy->operation == 'ranges') {
                foreach ($quote_for_copy->ranges as $range) {
                    DB::table('quote_ranges')->insert([
                        'from'       => $range->from,
                        'to'         => $range->to,
                        'quote_id'   => $new_object_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
            }
            if ($quote_for_copy->operation == 'passengers') {

                $people = $quote_for_copy->people->first();

                DB::table('quote_people')->insert([
                    'adults'     => $people->adults,
                    'child'      => $people->child,
                    'quote_id'   => $new_object_id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);

                $quote_passengers_childs_createds = [];
                if ($people->child > 0) {
                    foreach ($quote_for_copy->age_child as $quote_age_child) {

                        $quote_passenger_child_New = DB::table('quote_age_child')->insertGetId([
                            'age'      => $quote_age_child->age,
                            'quote_id' => $new_object_id,
                        ]);

                        $quote_passengers_childs_createds[$quote_age_child->id] = $quote_passenger_child_New;
                    }
                }

                $quote_passengers_createds = [];
                foreach ($quote_for_copy->passengers as $passenger) {

                    $quote_age_child_id = null;
                    if ($passenger->type == 'CHD') {
                        if (isset($quote_passengers_childs_createds[$passenger->quote_age_child_id])) {
                            $quote_age_child_id = $quote_passengers_childs_createds[$passenger->quote_age_child_id];
                        }
                    }

                    if ($passenger->birthday === '0000-00-00' || $passenger->birthday === null) {
                        $passenger_birthday = null;
                    } else {
                        $passenger_birthday = Carbon::parse($passenger->birthday)->format('Y-m-d');
                    }

                    $quote_passengers_New = DB::table('quote_passengers')->insertGetId([
                        'first_name'         => $passenger->first_name,
                        'last_name'          => $passenger->last_name,
                        'gender'             => $passenger->gender ? $passenger->gender : null,
                        'birthday'           => $passenger_birthday,
                        'document_number'    => $passenger->document_number,
                        'doctype_iso'        => $passenger->doctype_iso,
                        'country_iso'        => $passenger->country_iso,
                        'email'              => $passenger->email,
                        'phone'              => $passenger->phone,
                        'notes'              => $passenger->notes,
                        'type'               => $passenger->type,
                        'quote_id'           => $new_object_id,
                        'quote_age_child_id' => $quote_age_child_id,
                        "created_at"         => Carbon::now(),
                        "updated_at"         => Carbon::now()
                    ]);

                    $quote_passengers_createds[$passenger->id] = $quote_passengers_New;
                }


                foreach ($quote_for_copy->distributions as $distribution) {

                    $quote_distribution = DB::table('quote_distributions')->insertGetId([
                        'type_room'      => $distribution->type_room,
                        'type_room_name' => $distribution->type_room_name,
                        'occupation'     => $distribution->occupation,
                        'single'         => $distribution->single,
                        'double'         => $distribution->double,
                        'triple'         => $distribution->triple,
                        'adult'          => $distribution->adult,
                        'child'          => $distribution->child,
                        'order'          => $distribution->order,
                        'quote_id'       => $new_object_id,
                        "created_at"     => Carbon::now(),
                        "updated_at"     => Carbon::now()
                    ]);

                    foreach ($distribution->passengers as $passenger) {
                        DB::table('quote_distribution_passengers')->insertGetId([
                            'quote_distribution_id' => $quote_distribution,
                            'quote_passenger_id'    => $quote_passengers_createds[$passenger->quote_passenger_id],
                            "created_at"            => Carbon::now(),
                            "updated_at"            => Carbon::now()
                        ]);
                    }
                }
            }

            foreach ($quote_for_copy->notes as $note) {
                DB::table('quote_notes')->insert([
                    'parent_note_id' => $note->parent_note_id,
                    'comment'        => $note->comment,
                    'status'         => $note->status,
                    'quote_id'       => $new_object_id,
                    'user_id'        => $note->user_id,
                    "created_at"     => Carbon::now(),
                    "updated_at"     => Carbon::now()
                ]);
            }

            foreach ($quote_for_copy->destinations as $destiny) {
                DB::table('quote_destinations')->insert([
                    'quote_id'   => $new_object_id,
                    'state_id'   => $destiny->state_id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            $_logs = DB::table('quote_logs')->where('quote_id', $quote_for_copy->id)
                ->whereIn('type', [
                    'from_package',
                    'from_extension',
                    'quote_added',
                    'extension_added'
                ])->get();

            foreach ($_logs as $log) {
                DB::table('quote_logs')->insert([
                    'quote_id'   => $new_object_id,
                    'type'       => $log->type,
                    'object_id'  => $log->object_id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            if ($to_status != 2) { // 1

                if ($user_id == Auth::user()->id) { // "Duplicar"
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $new_object_id,
                        'type'       => 'copy_self',
                        'object_id'  => $quote_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $new_object_id,
                        'type'       => 'from_quote',
                        'object_id'  => $quote_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                } else { // share
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $new_object_id,
                        'type'       => 'copy_from',
                        'object_id'  => Auth::user()->id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $quote_id,
                        'type'       => 'copy_to',
                        'object_id'  => $user_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
            }

            if ($quote_for_copy->operation == 'passengers') {
                $this->copyServicePassengersFromQuote($quote_id, $new_object_id);
            }

            if ($send_notification == 1) {
                $user = User::find($user_id);
                $client = Client::find($client_id);
                $client_name = '';
                if ($client) {
                    $client_name = $client->name;
                }
                if ($user) {
                    $data = [
                        'user_from'    => Auth::user()->name,
                        'quote_id'     => $new_object_id,
                        'quote_date'   => $quote_for_copy->date_in,
                        'quote_name'   => $quote_for_copy->name,
                        'quote_client' => $client_name,
                        'quote_cities' => '',
                    ];
                    Mail::to($user->email)->send(
                        new NotificationSharedQuote($data)
                    );
                }
            }

            if ($quote_for_copy->accommodation) {
                $new_accommodation = new QuoteAccommodation();
                $new_accommodation->quote_id = $new_object_id;
                $new_accommodation->single = $quote_for_copy->accommodation->single;
                $new_accommodation->double = $quote_for_copy->accommodation->double;
                $new_accommodation->double_child = $quote_for_copy->accommodation->double_child;
                $new_accommodation->triple = $quote_for_copy->accommodation->triple;
                $new_accommodation->triple_child = $quote_for_copy->accommodation->triple_child;
                $new_accommodation->save();
            }

            return $new_object_id;
        });


        return Response::json(['success' => true, 'new_quote_id' => $new_object_id]);
    }

    public function occupationPassengersHotelClient(Request $request)
    {

        $single = $request->input('single');
        $double = $request->input('double');
        $triple = $request->input('triple');
        $adults = $request->input('adults');
        $child = $request->input('child');
        $quote_id = $request->input('quote_id');


        $quoteAccommodation = QuoteAccommodation::where("quote_id", $quote_id)->first();
        $quotePeople = QuotePeople::where("quote_id", $quote_id)->first();
        $quoteDistributions = QuoteDistribution::with('passengers')->where(
            "quote_id",
            $quote_id
        )->orderBy('order')->get();
        $quotePassengers = QuotePassenger::where("quote_id", $quote_id)->orderBy('id')->get()->toArray();

        $quotePassengers = $this->transforPassengers($quote_id, $quotePassengers, $adults, $child);


        if (count($quoteDistributions) > 0) {

            //validamos si se ha cambiado
            if ($quoteAccommodation->single != $single or $quoteAccommodation->double != $double or $quoteAccommodation->triple != $triple) {
                $quoteDistributions = [];
            } else {
                //validamos si se ha cambiado la cantidad adultos o niños
                $adultSave = $quoteDistributions->sum('adult');
                $childSave = $quoteDistributions->sum('child');
                if ($adults != $adultSave or $child != $childSave) {
                    $quoteDistributions = [];
                }
            }
        } else {
            $quoteDistributions = [];
        }

        $quoteDistributionsNew = [];
        if (count($quoteDistributions) == 0) {

            $quoteDistributionsNew = $this->generateQuoteDistributionsClient(
                $quotePassengers,
                $single,
                $double,
                $triple,
                $adults,
                $child
            );
        } else {

            $countAdul = 0;
            $countChild = 0;
            $name = '';
            $quote_passengers = [];
            foreach ($quotePassengers as $passenger) {

                $name = '';
                if ($passenger['first_name'] or $passenger['last_name']) {
                    $name = $passenger['first_name'] . ' ' . $passenger['last_name'];
                } else {

                    if ($passenger['type'] == 'ADL') {
                        $countAdul++;
                        $name = 'Adult ' . $countAdul;
                    } else {
                        $countChild++;
                        $name = 'Child ' . $countChild;
                    }
                }

                $quote_passengers[$passenger['id']] = [
                    'code'  => $passenger['id'],
                    'label' => $name,
                ];
            }

            foreach ($quoteDistributions as $type_room => $distribution) {

                $passengers = [];
                foreach ($distribution->passengers as $passenger) {
                    array_push($passengers, $quote_passengers[$passenger->quote_passenger_id]);
                }

                array_push($quoteDistributionsNew, [
                    'type_room'      => $distribution->type_room,
                    'type_room_name' => $distribution->type_room_name,
                    'occupation'     => $distribution->occupation,
                    'single'         => $distribution->single,
                    'double'         => $distribution->double,
                    'triple'         => $distribution->triple,
                    'adult'          => $distribution->adult,
                    'child'          => $distribution->child,
                    'order'          => $distribution->order,
                    'passengers'     => $passengers
                ]);
            }
        }


        return \response()->json(['quoteDistributions' => $quoteDistributionsNew, "quoteDistributions_ant" => $quoteDistributions, "passengers" => $quotePassengers], 200);
    }

    public function transforPassengers($quote_id, $quotePassengers, $newAuls, $newChild)
    {

        $passengerADL = [];
        $passengerCHD = [];

        foreach ($quotePassengers as $passenger) {
            if ($passenger['type'] == 'ADL') {
                array_push($passengerADL, $passenger);
            }
            if ($passenger['type'] == 'CHD') {
                array_push($passengerCHD, $passenger);
            }
        }


        if ((count($passengerADL) == $newAuls) and (count($passengerCHD) == $newChild)) {
            return $quotePassengers;
        } else {
            $newPassengers = [];
            for ($i = 0; $i < $newAuls; $i++) {
                if (isset($passengerADL[$i])) {
                    array_push($newPassengers, $passengerADL[$i]);
                } else {
                    array_push($newPassengers, [
                        "id"                   => ($i + 1) . "x",
                        "first_name"           => "",
                        "last_name"            => "",
                        "gender"               => null,
                        "birthday"             => null,
                        "document_number"      => null,
                        "doctype_iso"          => null,
                        "country_iso"          => null,
                        "city_ifx_iso"         => null,
                        "email"                => null,
                        "phone"                => null,
                        "notes"                => null,
                        "created_at"           => null,
                        "updated_at"           => null,
                        "quote_id"             => $quote_id,
                        "address"              => null,
                        "dietary_restrictions" => null,
                        "medical_restrictions" => null,
                        "type"                 => "ADL",
                        "is_direct_client"     => false,
                        "quote_age_child_id"   => null,
                        "quote_passenger_id"   => null
                    ]);
                }
            }

            for ($i = 0; $i < $newChild; $i++) {
                if (isset($passengerCHD[$i])) {
                    array_push($newPassengers, $passengerCHD[$i]);
                } else {
                    array_push($newPassengers, [
                        "id"                   => ($i + 1) . "x",
                        "first_name"           => "",
                        "last_name"            => "",
                        "gender"               => null,
                        "birthday"             => null,
                        "document_number"      => null,
                        "doctype_iso"          => null,
                        "country_iso"          => null,
                        "city_ifx_iso"         => null,
                        "email"                => null,
                        "phone"                => null,
                        "notes"                => null,
                        "created_at"           => null,
                        "updated_at"           => null,
                        "quote_id"             => $quote_id,
                        "address"              => null,
                        "dietary_restrictions" => null,
                        "medical_restrictions" => null,
                        "type"                 => "CHD",
                        "is_direct_client"     => false,
                        "quote_age_child_id"   => null,
                        "quote_passenger_id"   => null
                    ]);
                }
            }

            return $newPassengers;
        }
    }

    public function generateQuoteDistributionsClient($quotePassengers, $single, $double, $triple, $adults, $child)
    {

        $quoteDistributions = [];
        $order = 1;
        for ($i = 0; $i < $single; $i++) {
            array_push($quoteDistributions, [
                'type_room'      => 'single',
                'type_room_name' => 'SGL',
                'occupation'     => 1,
                'single'         => 1,
                'double'         => 0,
                'triple'         => 0,
                'order'          => $order,
                'passengers'     => []
            ]);
            $order++;
        }
        for ($i = 0; $i < $double; $i++) {
            array_push($quoteDistributions, [
                'type_room'      => 'double',
                'type_room_name' => 'DBL',
                'occupation'     => 2,
                'single'         => 0,
                'double'         => 1,
                'triple'         => 0,
                'order'          => $order,
                'passengers'     => []
            ]);
            $order++;
        }
        for ($i = 0; $i < $triple; $i++) {
            array_push($quoteDistributions, [
                'type_room'      => 'triple',
                'type_room_name' => 'TPL',
                'occupation'     => 3,
                'single'         => 0,
                'double'         => 0,
                'triple'         => 1,
                'order'          => $order,
                'passengers'     => []
            ]);
            $order++;
        }

        $quoteDistributions = $this->updatePaxAcomodacionGeneral($quoteDistributions, $adults, $child, 0);
        $countAdul = 0;
        $countChild = 0;
        $name = '';
        foreach ($quoteDistributions as $index => $quoteAccommodation) {
            $totalAsignad = $quoteAccommodation['adult'] + $quoteAccommodation['child'];
            if ($totalAsignad > 0) {
                $passengers = array_slice($quotePassengers, 0, $totalAsignad);
                foreach ($passengers as $passenger) {


                    $name = '';
                    if ($passenger['first_name'] or $passenger['last_name']) {
                        $name = $passenger['first_name'] . ' ' . $passenger['last_name'];
                    } else {

                        if ($passenger['type'] == 'ADL') {
                            $countAdul++;
                            $name = 'Adult ' . $countAdul;
                        } else {
                            $countChild++;
                            $name = 'Child ' . $countChild;
                        }
                    }

                    array_push($quoteDistributions[$index]['passengers'], [
                        'code'  => $passenger['id'],
                        'label' => $name,
                    ]);
                }

                $quotePassengers = array_slice($quotePassengers, $totalAsignad);
            }
        }

        return $quoteDistributions;
    }

    public function updateShowInPopup($value)
    {

        DB::table('quotes')
            ->where('user_id', Auth::user()->id)
            ->update([
                'show_in_popup' => $value
            ]);

        $response = ['success' => true];

        return Response::json($response);
    }

    public function discountPermissionMail($quote_id, Request $request)
    {
        $user_code = $request->input('user_code');

        $user = User::where("user_type_id", 3)
            ->where('code', $user_code)
            ->where("code", '!=', Auth::user()->code);

        if ($user->count() == 0) {
            return Response::json(['success' => false]);
        } else {
            $user = $user->first();
            $quote = Quote::find($quote_id);
            // Enviar Mail
            Mail::to($user->email)->send(
                new QuoteDiscountAlert(
                    Auth::user()->name,
                    $quote_id,
                    $quote->name,
                    $quote->discount,
                    $quote->discount_detail
                )
            );

            return Response::json(['success' => true]);
        }
    }

    public function relateOrder(Request $request)
    {
        $nropla_v2 = $request->input('nropla_v2');
        $lastNroped = (@$request->input('lastNroped') > 0) ? $request->input('lastNroped') : 0;
        $lastNroord = (@$request->input('lastNroord') > 0) ? $request->input('lastNroord') : 0;
        $nroped = (@$request->input('nroped') > 0) ? $request->input('nroped') : 0;
        $nroord = $request->input('nroord');
        $mode = $request->input('mode');
        $response = [];
        $_quote = Quote::where('id', $nropla_v2)->with('logs')->first();

        //pasando los parámetros a un array
        $array = [
            'nropla'     => $nropla_v2,
            'lastNroped' => $lastNroped,
            'lastNroord' => $lastNroord,
            'nroped'     => $nroped,
            'nroord'     => $nroord,
            'mode'       => $mode,
            'date'       => date("d/m/Y"),
            'hour'       => date("H:i:s")
        ];

        if ($_quote) {
            $_nropla_v2 = $nropla_v2;
            if (isset($_quote->logs)) {
                foreach ($_quote->logs as $l) {
                    if ($l->type == "editing_quote") {
                        $_nropla_v2 = $l->object_id;
                        break;
                    }
                }
            }

            //pasando los parámetros a un array
            $array['nropla'] = $_nropla_v2;
            $response = (array)$this->stellaService->relate_coti_order($array);
        }

        $quote = Quote::find($nropla_v2);
        if ($quote) {
            if ((int)$mode == 1) {
                $_nroped = $nroped;
                if ($nroord == '' || $nroord == null || $nroord == 0) {
                    $_nroord = $response[count($response) - 1]->nroord;
                } else {
                    $_nroord = $nroord;
                }
            } else {
                $_nroped = null;

                $_nroord = null;
            }

            if (!($lastNroped == $_nroped and $lastNroord == $_nroord)) {
                $this->store_history_logs($nropla_v2, [
                    [
                        "type"          => "update",
                        "slug"          => "update_relate_order",
                        "previous_data" => $quote->order_related,
                        "current_data"  => $_nroped,
                        "description"   => "Actualizó el vínculo con un N° de pedido"
                    ]
                ]);
            }

            $quote->order_related = $_nroped;
            $quote->order_position = $_nroord;
            $quote->save();

            if ($quote->status == 2) {
                $find_quote_original = DB::table('quote_logs')
                    ->where('quote_id', $quote->id)
                    ->where(
                        'type',
                        'editing_quote'
                    )
                    ->first();

                // Validación mínima y esencial:
                if ($find_quote_original && $find_quote_original->object_id) {
                    $quote_original = Quote::find($find_quote_original->object_id);

                    if ($quote_original) {
                        $quote_original->order_related = $_nroped;
                        $quote_original->order_position = $_nroord;
                        $quote_original->save();
                    }
                }
            }
            $response = ['success' => true, 'data' => $response, 'post' => $array];
        } else {
            $response = ['success' => false, 'data' => [], 'post' => $array];
        }


        return Response::json($response);
    }

    public function updateDiscount($quote_id, Request $request)
    {
        $discount = $request->input('discount');
        $discount_detail = $request->input('discount_detail');
        $discount_user_permission = $request->input('discount_user_permission');

        $quote = Quote::find($quote_id);
        $quote->discount = $discount;
        $quote->discount_detail = $discount_detail;
        $quote->discount_user_permission = $discount_user_permission;
        $quote->save();

        return Response::json(['success' => true]);
    }

    public function convertToPackage($quote_id, Request $request)
    {

        $quote = DB::table('quotes')->where('id', $quote_id)->first();
        $quote_categories_ids = DB::table('quote_categories')->where('quote_id', $quote->id)->pluck('id');
        $quote_services = DB::table('quote_services')
            ->whereIn('quote_category_id', $quote_categories_ids)
            ->orderBy('quote_category_id')->orderBy('date_in')->orderBy('order')
            ->get();

        if (count($quote_categories_ids) == 0 || count($quote_services) == 0) {
            return Response::json(['success' => false, 'message' => 'La cotización no contiene servicios']);
        }

        $tag_id = DB::table('translations')
            ->where('type', 'tag')
            ->where('slug', 'tag_name')
            ->where('language_id', 1)
            ->where('value', "EXCLUSIVO")
            ->first();

        if (!$tag_id) {
            return Response::json([
                'success' => false,
                'message' => 'No existe ninguna categoría de nombre "EXCLUSIVO"'
            ]);
        } else {
            $tag_id = $tag_id->object_id;
        }

        $markup = $request->post('markup');
        $client_id = $request->post('client_id');

        try {
            DB::beginTransaction();
            if ($quote_services[0]->type == 'hotel') {
                $_service = Hotel::find($quote_services[0]->object_id);
                $country_id = $_service->country_id;
            } elseif ($quote_services[0]->type == 'service') {
                $_service = ServiceDestination::where('service_id', $quote_services[0]->object_id)->first();
                $country_id = $_service->country_id;
            } else {
                $country_id = 89;
            }


            $package = new Package();
            $package->country_id = $country_id;
            $package->status = 1;
            $package->nights = $quote->nights;
            $package->extension = 0;
            $package->physical_intensity_id = PhysicalIntensity::all()->first()->id;
            $package->tag_id = $tag_id;
            $package->recommended = 0;
            $package->save();

            // TEXTOS POR DEFECTO
            $langs = Language::where('state', 1)->get();
            foreach ($langs as $lang) {
                if ($lang->id <= 4) {
                    $package_translation = new PackageTranslation();
                    $package_translation->language_id = $lang->id;
                    $package_translation->package_id = $package->id;
                    $package_translation->name = $quote->name;
                    $package_translation->tradename = $quote->name;
                    $package_translation->save();
                }
            }
            // DESTINATIONS
            $destinations = QuoteDestination::where('quote_id', $quote->id)->get();
            foreach ($destinations as $d) {
                $package_destination = new PackageDestination();
                $package_destination->state_id = $d->state_id;
                $package_destination->package_id = $package->id;
                $package_destination->save();
            }

            $date_out = Carbon::parse($quote->date_in)->addDay($quote->nights)->format('Y-m-d');


            $plan_rate = new PackagePlanRate();
            $plan_rate->package_id = $package->id;
            $plan_rate->name = $quote->name;
            $plan_rate->date_from = $quote->date_in;
            $plan_rate->date_to = $date_out;
            $plan_rate->status = 1;
            $plan_rate->service_type_id = $quote->service_type_id;
            $plan_rate->save();


            // TARIFA VENTA
            if ($client_id == '') {
                if (Auth::user()->user_type_id == 1) {
                    $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
                    $client_id = $client_id["client_id"];
                }
            }
            if ($client_id == '') {
                return response()->json(["message" => "Cliente no encontrado"], 422);
            }

            $market_id = Client::where('id', $client_id)->first()->market_id;

            $sale = new PackageRateSaleMarkup();

            $sale->package_plan_rate_id = $plan_rate->id;

            $exists_markup = PackageRateSaleMarkup::where('seller_type', "App\Client")->where(
                'seller_id',
                $client_id
            )->where('package_plan_rate_id', $plan_rate->id)->get();

            if ($exists_markup->count() > 0) {
                return response()->json(["message" => "ya este cliente ha sido guardado"], 422);
            } else {
                $sale->seller_type = "App\Client";
                $sale->seller_id = $client_id;
                $sale->markup = $markup;
                $sale->save();
            }

            $sale_id = $sale->id;

            $category_ids = PackagePlanRateCategory::where('package_plan_rate_id', $plan_rate->id)->pluck('id');

            $packageRates = PackageDynamicRateCopy::whereIn('package_plan_rate_category_id', $category_ids)->get();

            DB::transaction(function () use ($sale_id, $packageRates) {
                foreach ($packageRates as $packageRate) {
                    $package_dynamic_sale_rates = new PackageDynamicSaleRate();
                    $package_dynamic_sale_rates->service_type_id = $packageRate["service_type_id"];
                    $package_dynamic_sale_rates->package_plan_rate_category_id = $packageRate["package_plan_rate_category_id"];
                    $package_dynamic_sale_rates->pax_from = $packageRate["pax_from"];
                    $package_dynamic_sale_rates->pax_to = $packageRate["pax_to"];
                    $package_dynamic_sale_rates->simple = $packageRate["simple"];
                    $package_dynamic_sale_rates->double = $packageRate["double"];
                    $package_dynamic_sale_rates->triple = $packageRate["triple"];
                    $package_dynamic_sale_rates->status = 1;
                    $package_dynamic_sale_rates->package_rate_sale_markup_id = $sale_id;
                    $package_dynamic_sale_rates->save();
                }
            });


            // CATEGORIES
            $quote_categories = QuoteCategory::where('quote_id', $quote->id)
                ->with('services.service_rate', 'services.service_rooms')->get();
            foreach ($quote_categories as $c) {
                $package_plan_rate_categories = new PackagePlanRateCategory();
                $package_plan_rate_categories->package_plan_rate_id = $plan_rate->id;
                $package_plan_rate_categories->type_class_id = $c->type_class_id;
                $package_plan_rate_categories->save();

                foreach ($c->services as $s) {
                    $package_service = new PackageService();
                    $package_service->package_plan_rate_category_id = $package_plan_rate_categories->id;
                    $package_service->type = $s->type;
                    $package_service->object_id = $s->object_id;
                    $package_service->order = $s->order;
                    $package_service->calculation_included = 0;
                    $package_service->date_in = convertDate($s->date_in, '/', '-', true);
                    $package_service->date_out = convertDate($s->date_out, '/', '-', true);
                    $package_service->adult = $s->adult;
                    $package_service->child = $s->child;
                    $package_service->infant = $s->infant;
                    $package_service->single = $s->single;
                    $package_service->double = $s->double;
                    $package_service->triple = $s->triple;
                    if ($s->type == 'flight') {
                        $package_service->code_flight = $s->code_flight;
                        $package_service->origin = $s->origin;
                        $package_service->destiny = $s->destiny;
                    }
                    $package_service->save();
                    if ($s->type == 'service') {
                        if (isset($s->service_rate->service_rate_id)) {
                            $package_service_rates = new PackageServiceRate();
                            $package_service_rates->package_service_id = $package_service->id;
                            $package_service_rates->service_rate_id = $s->service_rate->service_rate_id;
                            $package_service_rates->save();
                        }
                    }

                    if ($s->type == 'hotel') {
                        foreach ($s->service_rooms as $s_room) {
                            $package_service_rooms = new PackageServiceRoom();
                            $package_service_rooms->package_service_id = $package_service->id;
                            $package_service_rooms->rate_plan_room_id = $s_room->rate_plan_room_id;
                            $package_service_rooms->save();
                        }
                    }
                }

                // quotes ranges
                $quote_ranges = QuoteRange::where('quote_id', $quote->id)->get();
                if (count($quote_ranges) > 0) {
                    $r_i = $quote_ranges[0]->from;
                    for ($i = $r_i; $i <= $quote_ranges[count($quote_ranges) - 1]->to; $i++) {
                        $new_package_dynamic_rate = new PackageDynamicRate();
                        $new_package_dynamic_rate->service_type_id = $quote->service_type_id;
                        $new_package_dynamic_rate->package_plan_rate_category_id = $package_plan_rate_categories->id;
                        $new_package_dynamic_rate->pax_from = $i;
                        $new_package_dynamic_rate->pax_to = $i;
                        $new_package_dynamic_rate->simple = 0.00;
                        $new_package_dynamic_rate->double = 0.00;
                        $new_package_dynamic_rate->triple = 0.00;
                        $new_package_dynamic_rate->save();
                    }
                    $this->calculatePricePackage($package_plan_rate_categories->id);
                }
            }
            DB::commit();

            return Response::json(['success' => true, 'package_id' => $package->id]);
        } catch (\Exception $exception) {
            DB::rollBack();

            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function copyFirstPassengerData(Request $request)
    {
        $quote_id = $request->post('quote_id');
        $passenger = $request->post('passenger');

        DB::transaction(function () use ($quote_id, $passenger) {
            DB::table('quote_passengers')->where('quote_id', $quote_id)->update([
                'first_name'      => $passenger["first_name"],
                'last_name'       => $passenger["last_name"],
                'gender'          => $passenger["gender"],
                'birthday'        => $passenger["birthday"],
                'document_number' => $passenger["document_number"],
                'doctype_iso'     => $passenger["doctype_iso"],
                'country_iso'     => $passenger["country_iso"],
                'email'           => $passenger["email"],
                'phone'           => $passenger["phone"],
                'notes'           => $passenger["notes"],
                "updated_at"      => \Carbon\Carbon::now()
            ]);
        });

        $passengers = DB::table('quote_passengers')->where('quote_id', $quote_id)->get();

        return \response()->json($passengers, 200);
    }

    public function replaceService(Request $request)
    {
        $services = $request->input('services');
        $quote_service_id_old = $request->input('quote_service_id_old');
        $service_rate_id_old = $request->input('service_rate_id_old');
        $object_id = $request->input('object_id');
        $date_in = $request->input('date_in');
        $date_out = $request->input('date_out');
        $service_rate_ids = $request->input('service_rate_ids');
        $quote_id = $request->input('quote_id');

        DB::transaction(function () use (
            $services,
            $quote_service_id_old,
            $service_rate_id_old,
            $object_id,
            $date_in,
            $date_out,
            $service_rate_ids,
            $quote_id
        ) {

            $histories = [];

            foreach ($services as $service) {
                $quote_service = QuoteService::find($service['id']);
                if ($quote_service) {
                    $service_rate = DB::table('quote_service_rates')->where(
                        'quote_service_id',
                        $service["id"]
                    )->first();
                    if ($service_rate) {
                        DB::table('quote_service_rates')->where('id', $service_rate->id)->update([
                            'service_rate_id' => $service_rate_ids[0],
                            'updated_at'      => \Carbon\Carbon::now()
                        ]);

                        $service_code_ = '';
                        $service_replace_code_ = '';

                        if ($quote_service->type === 'hotel') {
                            $hotel_ = Hotel::where('id', $quote_service->object_id)->with('channel')->first();
                            $service_code_ = ($hotel_) ? $hotel_->channel[0]->code : "";

                            $hotel_replace_ = Hotel::where('id', $object_id)->with('channel')->first();
                            $service_replace_code_ = ($hotel_replace_) ? $hotel_replace_->channel[0]->code : "";
                        }
                        if ($quote_service->type === 'service') {
                            $service_ = Service::where('id', $quote_service->object_id)->first();
                            $service_code_ = ($service_) ? $service_->aurora_code : "";

                            $service_replace_ = Service::where('id', $object_id)->first();
                            $service_replace_code_ = ($service_replace_) ? $service_replace_->aurora_code : "";
                        }

                        $type_class_id_ = QuoteCategory::find($quote_service->quote_category_id)->type_class_id;

                        $histories[] = [
                            "type"          => "update",
                            "slug"          => "replace_service",
                            "previous_data" => json_encode([
                                "type_class_id" => $type_class_id_,
                                "type_service"  => $quote_service->type,
                                "object_id"     => $quote_service->object_id,
                                "service_code"  => $service_code_,
                                "date_in"       => $quote_service->date_in
                            ]),
                            "current_data" => json_encode([
                                "type_class_id" => $type_class_id_,
                                "type_service"  => $quote_service->type,
                                "object_id"     => $object_id,
                                "service_code"  => $service_replace_code_,
                                "date_in"       => convertDate($date_in, "-", "/", 1)
                            ]),
                            "description" => "Reemplazó servicio"
                        ];

                        $quote_service->object_id = $object_id;
                        $quote_service->date_in = $date_in;
                        $quote_service->date_out = $date_out;
                        $quote_service->save();
                    }
                }
            }

            if (count($histories) > 0) {
                $this->store_history_logs($quote_id, $histories);
            }
        });

        return Response::json(['success' => true]);
    }

    public function updateMarkup(Request $request)
    {
        $client_id = $this->getClientId($request);
        $quote_id = $request->input('quote_id');
        $markup = NULL;
        if ($request->input('option') == 1) {
            // if (empty($quote_id)) {
            //     $markup_hotel = DB::table('markups')
            //         ->where('client_id', $client_id)
            //         ->where('period', Carbon::now()->year)
            //         ->first();

            //     if ($markup_hotel != null) {
            //         $markup = $markup_hotel->hotel;
            //     }
            // } else {
            //     $quote = Quote::find($quote_id, ['date_in', 'markup']);
            //     $markup_hotel = DB::table('markups')
            //         ->where('client_id', $client_id)
            //         ->where('period', Carbon::parse($quote->date_in)->format('Y'))
            //         ->first();
            //     if ($markup_hotel != null) {
            //         $markup = $markup_hotel->hotel;
            //     }
            // }

        } elseif ($request->input('option') == 2) {
            $markup = $request->input('markup');
            if (!isset($markup) || !is_numeric($markup) || $markup <= 0) {
                $markup = NULL;
            }
        } else {
            // $quote = Quote::find($quote_id, ['date_in']);
            // $markup_hotel = DB::table('markups')
            //     ->where('client_id', $client_id)
            //     ->where('period', Carbon::parse($quote->date_in)->format('Y'))
            //     ->first();
            // if ($markup_hotel != null) {
            //     $markup = $markup_hotel->hotel;
            // }
        }

        if (!empty($quote_id)) {
            $quote = Quote::find($quote_id);
            if ($quote) {

                $this->store_history_logs($quote_id, [
                    [
                        "type"          => "update",
                        "slug"          => "update_markup",
                        "previous_data" => $quote->markup,
                        "current_data"  => $markup,
                        "description"   => "Actualizó el markup"
                    ]
                ]);
                $quote->markup = $markup;
                $quote->save();
            }
        }

        return \response()->json(["message" => "Markup Actualizado", "markup" => $markup]);
    }

    public function createNote(Request $request)
    {
        $note_original = new QuoteNote();
        $note_original->comment = $request->post('comment');
        $note_original->status = $request->post('status');
        $note_original->quote_id = $request->post('quote_id');
        $note_original->user_id = $request->post('user_id');
        $note_original->save();

        $user = User::find($request->post('user_id'));

        if (Auth::user()->id != $request->post('user_id')) {
            // Notificación de Repuesta..
            $response_notification = new Notification();
            $response_notification->title = "Nota Nueva";
            $response_notification->content = "Han creado una nota nueva: " . $note_original->id;
            $response_notification->target = 1;
            $response_notification->type = 1;
            $response_notification->url = '/packages/cotizacion';
            $response_notification->user = $user->code;
            $response_notification->status = 1;
            $response_notification->data = "";
            $response_notification->created_by = Auth::user()->code;
            $response_notification->updated_by = Auth::user()->code;
            $response_notification->module = '/packages/cotizacion';
            $response_notification->save();

            $pushNoti = (object)[
                'user'         => $response_notification->user,
                'title'        => $response_notification->title,
                'body'         => $response_notification->content,
                'click_action' => URL::to('/packages/cotizacion')
            ];
            parent::sendPushNotification($pushNoti);
        }

        return \response()->json(["message" => "respuesta creada correctamente", "note_id" => $note_original->id], 200);
    }

    public function updateNote($note_id, Request $request)
    {

        $comment = $request->input('comment');

        $note_original = QuoteNote::find($note_id);
        $note_original->comment = $comment;
        $note_original->save();

        return \response()->json(["message" => "nota actualizada correctamente"], 200);
    }

    public function createResponse(Request $request)
    {

        $note_original = new QuoteNote();
        $note_original->parent_note_id = $request->post('parent_note_id');
        $note_original->comment = $request->post('comment');
        $note_original->status = $request->post('status');
        $note_original->quote_id = $request->post('quote_id');
        $note_original->user_id = $request->post('user_id');
        $note_original->save();

        $nota_padre = QuoteNote::find($request->post('parent_note_id'));

        $user = User::find($nota_padre->user_id);

        if ($nota_padre->user_id != $request->post('user_id')) {
            // Notificación de Repuesta..
            $response_notification = new Notification();
            $response_notification->title = "Respuesta Nueva";
            $response_notification->content = "Han respondido una nota: " . $note_original->id;
            $response_notification->target = 1;
            $response_notification->type = 1;
            $response_notification->url = '/packages/cotizacion';
            $response_notification->user = $user->code;
            $response_notification->status = 1;
            $response_notification->data = "";
            $response_notification->created_by = Auth::user()->code;
            $response_notification->updated_by = Auth::user()->code;
            $response_notification->module = '/packages/cotizacion';
            $response_notification->save();

            $pushNoti = (object)[
                'user'         => $response_notification->user,
                'title'        => $response_notification->title,
                'body'         => $response_notification->content,
                'click_action' => URL::to('/packages/cotizacion')
            ];
            parent::sendPushNotification($pushNoti);
        }

        return \response()->json(["message" => "respuesta creada correctamente"], 200);
    }

    public function restore(Request $request)
    {
        $quote_id = $request->post('quote_id');
        $quote_id_original = DB::table('quote_logs')
            ->where('quote_id', $quote_id)
            ->where('type', 'editing_quote')
            ->first()->object_id;
        $this->replaceQuote($quote_id_original, $quote_id);

        return response()->json("Cambios Descartados Correctamente", 200);
    }

    public function filterInformixOrders()
    {

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);

        $user = User::where('id', Auth::user()->id)->with('client_seller')->first();
        if ($user->client_seller) {
            $user_code = $user->automatic;
        } else {
            $user_code = $user->code;
        }

        //        $wsdl="http://localhost:9000/OrderLITO.php?wsdl";
        $wsdl = config('services.aurora_extranet.domain') . "/OrderLITO.php?wsdl"; // PROD
        //instanciando un nuevo objeto cliente para consumir el webservice
        $client = new \SoapClient($wsdl, [
            'encoding'     => 'UTF-8',
            'trace'        => true,
            'soap_version' => SOAP_1_1,
            'exceptions'   => true
        ]);

        //pasando los parámetros a un array
        $filter = array(
            'usuario' => $user_code //$user_code | 'MBB'
        );
        //llamando al método y pasándole el array con los parámetros
        $result = $client->__call("searchPendByExecutive", array('data' => $filter));

        $response = json_decode($result);

        $response = ['success' => true, 'data' => $response];

        return Response::json($response);
    }

    public function import($id, Request $request)
    {
        $code = $request->input('code');

        //        $wsdl="http://localhost:9000/QuoteLITO.php?wsdl";
        $wsdl = config('services.aurora_extranet.domain') . "/QuoteLITO.php?wsdl"; // PROD
        //instanciando un nuevo objeto cliente para consumir el webservice
        // new SoapClient("some.wsdl", array('soap_version'   => SOAP_1_2));
        $client = new \SoapClient($wsdl, [
            'encoding'     => 'UTF-8',
            'trace'        => true,
            'soap_version' => SOAP_1_1,
            'exceptions'   => true
        ]);

        //pasando los parámetros a un array
        $filter = array('code' => $code);

        //llamando al método y pasándole el array con los parámetros
        $result = $client->__call("search", array('data' => $filter));
        $result_json = json_decode($result);

        foreach ($result_json as $service) {

            if ($service->TIPSER == 'H') {
                $service->type = 'hotel';
                $_hotel = DB::table('channel_hotel')->where('code', $service->CODSVS)->first();

                if ($_hotel != null) {
                    $service->object_id = $_hotel->hotel_id;
                } else {
                    return Response::json([
                        'false'   => true,
                        'message' => 'El hotel: ' . $service->CODSVS . ' aún no ha sido creado en esta versión 2.'
                    ]);
                    die;
                }
                $service->adult = (int)$service->NROCA1 + (int)$service->NROCA2 + (int)$service->NROCA3;
            } else {
                $service->type = 'service';
                $_service = DB::table('services')->where('equivalence_aurora', $service->NROEQU);
                if ($_service->count() > 0) {
                    $_service = $_service->first();
                    $service->object_id = $_service->id;
                } else {
                    return Response::json([
                        'false'   => true,
                        'message' => 'El servicio, de nroequ: ' . $service->NROEQU . ' aún no ha sido creado en esta versión 2.'
                    ]);
                    die;
                }
                $service->adult = (int)$service->NROCA1;
            }
        }

        DB::transaction(function () use ($id, $result_json) {

            $findTypeClass = TypeClass::where('code', 'X')->first();
            $created_at = \Carbon\Carbon::now();
            $idCategory = DB::table('quote_categories')->insertGetId([
                'quote_id'      => $id,
                'type_class_id' => $findTypeClass->id,
                'created_at'    => $created_at,
                'updated_at'    => $created_at
            ]);

            $year_first_date_in = (int)substr($result_json[0]->DATE_IN, 0, 4);

            if ($year_first_date_in < 2020) {
                $years_for_add = (2020 - $year_first_date_in);
            } else {
                $years_for_add = 0;
            }

            foreach ($result_json as $service) {

                $service->DATE_OUT = ($service->DATE_OUT) ? $service->DATE_OUT : $service->DATE_IN;

                $new_date_in = $this->add_years($service->DATE_IN, $years_for_add);
                $new_date_out = $this->add_years($service->DATE_OUT, $years_for_add);

                $_service_id = DB::table('quote_services')->insertGetId([
                    'type'              => $service->type,
                    'object_id'         => $service->object_id,
                    'quote_category_id' => $idCategory,
                    'order'             => (int)$service->NROORD,
                    'date_in'           => $new_date_in,
                    'date_out'          => $new_date_out,
                    'adult'             => $service->adult,
                    'child'             => 0,
                    'infant'            => 0,
                    'single'            => (int)$service->NROCA1,
                    'double'            => (int)$service->NROCA2,
                    'triple'            => (int)$service->NROCA3,
                    'nights'            => (int)$service->NOCHES,
                    'triple_active'     => 0,
                    'created_at'        => $created_at,
                    'updated_at'        => $created_at
                ]);

                if ($service->type == 'service') {

                    $service_rate = Service::where('id', $service->object_id)->with([
                        'service_rate' => function ($query) use ($new_date_in) {
                            $query->with([
                                'service_rate_plans' => function ($query) use ($new_date_in) {
                                    $query->where('date_from', '<=', $new_date_in);
                                    $query->where('date_to', '>=', $new_date_in);
                                }
                            ]);
                        }
                    ])->first();

                    if (count($service_rate->service_rate) > 0) {
                        DB::table('quote_service_rates')->insert([
                            'quote_service_id' => $_service_id,
                            'service_rate_id'  => $service_rate->service_rate[0]->id,
                            'created_at'       => $created_at,
                            'updated_at'       => $created_at
                        ]);
                    } else {
                        return Response::json([
                            'false'   => true,
                            'message' => 'El servicio: ' . $service->NROEQU .
                                ' no tiene plan tarifario.'
                        ]);
                        die;
                    }
                }

                $this->updateNightsAndCities($id);
            }
        });

        return Response::json(['success' => true, 'result' => $result_json]);
    }

    public function filterInformixHeaders(Request $request)
    {

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);

        $query = $request->input('query');
        $page = ($request->input('page')) ? $request->input('page') : 1;
        $limit = ($request->input('limit')) ? $request->input('limit') : 5;

        $user = User::where('id', Auth::user()->id)->with('client_seller')->first();
        if ($user->client_seller) {
            $user_code = $user->automatic;
        } else {
            $user_code = $user->code;
        }

        $quote_codes = Quote::where('user_id', Auth::user()->id)->where('code', '!=', '')->where(
            'code',
            '!=',
            null
        )->pluck('code');

        $ignoreCodes = 0;
        foreach ($quote_codes as $code) {
            $coma = ',';
            $ignoreCodes .= $coma . $code;
        }

        //        $wsdl="http://localhost:9000/QuoteLITO.php?wsdl";
        $wsdl = config('services.aurora_extranet.domain') . "/QuoteLITO.php?wsdl"; // PROD
        //instanciando un nuevo objeto cliente para consumir el webservice
        // new SoapClient("some.wsdl", array('soap_version'   => SOAP_1_2));
        $client = new \SoapClient($wsdl, [
            'encoding'     => 'UTF-8',
            'trace'        => true,
            'soap_version' => SOAP_1_1,
            'exceptions'   => true
        ]);

        //pasando los parámetros a un array
        $filter = array(
            'query'       => $query,
            'skip'        => $limit * ($page - 1),
            'first'       => (int)$limit,
            'ignoreCodes' => $ignoreCodes,
            'usuario'     => $user_code //$user_code | '9CORTE'
        );
        //llamando al método y pasándole el array con los parámetros
        $result = $client->__call("searchWithIgnores", array('data' => $filter));

        $result_json = json_decode($result);

        foreach ($result_json->data as $d) {
            $d->cities = explode(",", $d->CIUDES);
        }

        $response = ['success' => true, 'data' => $result_json->data, 'count' => (int)$result_json->count];

        return Response::json($response);
    }

    public function importHeader(Request $request)
    {

        $_code = ($request->input('service_type_code') == 'PRI') ? 'PC' : 'SIM';
        $service_type = ServiceType::where('code', $_code)->first();

        $new_header = new Quote();
        $new_header->code = $request->input('code');
        $new_header->name = $request->input('name');
        $new_header->date_in = $request->input('date_in');
        $new_header->nights = $request->input('nights');
        $new_header->service_type_id = $service_type->id;
        $new_header->user_id = Auth::user()->id;
        $new_header->status = 1;
        $new_header->show_in_popup = 1;
        $new_header->created_at = Carbon::now();
        $new_header->updated_at = Carbon::now();

        $response = ['success' => $new_header->save()];

        return Response::json($response);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
            'name'       => 'required',
            'date'       => 'required',
        ]);
        // cities / nights
        if ($validator->fails()) {
            $response = ['success' => false];
        } else {

            $name = $request->input('name');
            $date = $request->input('date');
            $date_estimated = $request->input('date_estimated');
            $service_type_id = $request->input('service_type_id');
            $user_id = Auth::user()->id;
            $categories = $request->input('categories');
            $ranges = $request->post('ranges');
            $notes = $request->post('notes');
            $passengers = $request->post('passengers');
            $people = $request->post('people');
            $operation = $request->post('operation');
            $createdByClient = false;
            $client_id = '';
            if (Auth::user()->user_type_id == 4) {
                $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
                $createdByClient = true;
                $client_id = $client_id["client_id"];
            }

            if (Auth::user()->user_type_id == 3) {
                $client_id = $request->post('client_id');
            }


            // $markup = DB::table('markups')
            //     ->whereNull('deleted_at')
            //     ->where('client_id', $client_id)
            //     ->where('period', Carbon::now()->year);
            // if ($markup->count() > 0) {
            //     $markup = $markup->first()->hotel;
            // } else {
            //     $markup = 0;
            // }

            $markup = NULL;
            $newQuoteInFront = $this->newQuoteInFront(
                $name,
                $date,
                $date_estimated,
                $service_type_id,
                $user_id,
                $categories,
                $ranges,
                $notes,
                $passengers,
                $people,
                $operation,
                $markup
            );

            $quote_open = Quote::where('user_id', $user_id)
                ->where('status', 2)
                ->with('logs')
                ->with('categories')
                ->with('ranges')
                ->with('people')
                ->with('passengers')
                ->with('notes.user')
                ->first()->toArray();

            $notes = [];

            foreach ($quote_open["notes"] as $note) {
                if (is_null($note["parent_note_id"])) {
                    array_push($notes, [
                        "id"              => $note["id"],
                        "comment"         => $note["comment"],
                        "status"          => $note["status"],
                        "quote_id"        => $note["quote_id"],
                        "user_name"       => $note["user"]["name"],
                        "user_id"         => $note["user_id"],
                        "responses"       => [],
                        "edit"            => false,
                        "create_response" => false
                    ]);
                }
            }

            foreach ($quote_open["notes"] as $response) {
                if ($response["parent_note_id"] != null) {

                    foreach ($notes as $index => $note) {
                        if ($note["id"] == $response["parent_note_id"]) {
                            array_push($notes[$index]["responses"], [
                                "id"             => $response["id"],
                                "parent_note_id" => $response["parent_note_id"],
                                "comment"        => $response["comment"],
                                "status"         => $response["status"],
                                "quote_id"       => $response["quote_id"],
                                "user_name"      => $response["user"]["name"],
                                "user_id"        => $response["user_id"],
                                "edit"           => false
                            ]);
                        }
                    }
                }
            }

            $quote_open["notes"] = $notes;

            //Todo Si un cliente crea una cotizacion le enviamos una notificacion a las sus ejecutivas
            if ($createdByClient) {
                $quote_ = DB::table('quote_logs')->where('quote_id', $quote_open['id'])->where(
                    'type',
                    'editing_quote'
                )->first();
                $quote = Quote::find($quote_->object_id);
                if ($quote) {
                    SendNotificationQuoteClient::dispatchNow($quote, $client_id);
                }
            }

            $response = ['success' => $newQuoteInFront, 'quote_open' => $quote_open];
        }

        return Response::json($response);
    }

    public function reverseEngineering(Request $request)
    {

        $user_id = Auth::user()->id;

        $quote_count = Quote::where('user_id', $user_id)
            ->where('status', 2)
            ->count();

        if ($quote_count > 0) {
            return Response::json(['success' => false, 'message' => 'You have an open quote, you must close it before continuing']);
        }

        DB::transaction(function () use ($request) {

            $quote_id = $this->createQuoteInverse($request, 1);
            $quote_id_copy = $this->createQuoteInverse($request, 2);

            $this->updateNightsAndCities($quote_id);
            $this->updateNightsAndCities($quote_id_copy);

            if (isset($request->clone_file_id)) {
                $this->updateCloneQuote($quote_id, $request);
                $this->updateCloneQuote($quote_id_copy, $request);
            }

            DB::table('quote_logs')->insert([
                'quote_id'   => $quote_id_copy,
                'type'       => 'editing_quote',
                'object_id'  => $quote_id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
        });

        return Response::json(['success' => true, 'message' => 'process executed successfully']);
    }

    public function createQuoteInverse($request, $status)
    {

        $quote_name = $request->description;
        // $quote_name = "TEST QUOTES IMPORT";
        $file_id = $request->id;
        $file_number = $request->file_number;
        $total_amount = $request->total_amount;
        $date_in = $request->date_in;
        $date_estimated = $request->date_out;
        $nights = Carbon::parse($request->date_in)->diffInDays(Carbon::parse($request->date_out));
        $service_type_id = 1;
        $user_id = 1;
        $operation = 'passengers';
        // $status = 1;
        $markup = $request->markup_client;
        $discount = null;
        $discount_detail = null;
        $order_position = null;
        $type_class_id = $request->type_class_id;
        $adults = $request->adults;
        $children = $request->children;
        $itineraries = $request->itineraries;
        $passengers = $request->passengers;


        // , $quote_for_copy->file_id, $quote_for_copy->file_number, $quote_for_copy->file_total_amount


        $this->newQuote(
            $quote_name,
            $date_in,
            $date_estimated,
            $nights,
            $service_type_id,
            $user_id,
            [],
            [],
            [],
            [],
            [],
            $operation,
            $status,
            $markup,
            $discount,
            $discount_detail,
            null,
            $order_position,
            $file_id,
            $file_number,
            $total_amount
        );

        $new_object_id = $this->getObjectId();

        $new_category_id = DB::table('quote_categories')->insertGetId([
            'quote_id'      => $new_object_id,
            'type_class_id' => $type_class_id,
            'created_at'    => Carbon::now(),
            "updated_at"    => Carbon::now()
        ]);

        foreach ($passengers as $ix_p => $passenger) {

            $passengers[$ix_p]['age'] = null;
            $quote_age_child_id = null;
            if ($passenger['type'] == 'CHD') {

                $years = 1;
                if ($passenger['date_birth']) {
                    $years = Carbon::parse($passenger['date_birth'])->age;
                    $passengers[$ix_p]['age'] = $years;
                }

                $quote_age_child_id = DB::table('quote_age_child')->insertGetId([
                    'quote_id'   => $new_object_id,
                    'age'        => $years,
                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            $quote_passenger_id = DB::table('quote_passengers')->insertGetId([
                'quote_id'           => $new_object_id,
                'first_name'         => $passenger['name'],
                'last_name'          => $passenger['surnames'],
                'gender'             => $passenger['genre'],
                'birthday'           => $passenger['date_birth'],
                'document_number'    => $passenger['document_number'],
                'doctype_iso'        => $passenger['doctype_iso'],
                'country_iso'        => $passenger['country_iso'],
                'type'               => $passenger['type'],
                'quote_age_child_id' => $quote_age_child_id,
                'created_at'         => Carbon::now(),
                "updated_at"         => Carbon::now()
            ]);

            $passengers[$ix_p]['quote_passenger_id'] = $quote_passenger_id;
        }

        $quoteDistributions = [];
        $accommodation = [];
        $orderItinerary = 0;
        foreach ($itineraries as $service) {
            $orderItinerary++;
            $locked = 0;
            $nights = Carbon::parse($service['date_in'])->diffInDays(Carbon::parse($service['date_out']));

            if ($service['entity'] == 'service') {

                $new_service_id = DB::table('quote_services')->insertGetId([
                    'quote_category_id' => $new_category_id,
                    'type'              => $service['entity'],
                    'object_id'         => $service['object_id'],
                    'order'             => $orderItinerary,
                    'date_in'           => $service['date_in'],
                    'date_out'          => $service['date_out'],
                    'hour_in'           => $service['start_time'],
                    'nights'            => $nights,
                    'adult'             => $service['total_adults'],
                    'child'             => $service['total_children'],
                    'infant'            => $service['total_infants'],
                    'single'            => 0,
                    'double'            => 0,
                    'triple'            => 0,
                    'locked'            => $locked,
                    'triple_active'     => 0,
                    'on_request'        => $service['confirmation_status'],
                    'optional'          => 0,
                    'code_flight'       => null,
                    'origin'            => null,
                    'destiny'           => null,
                    'extension_id'      => null,
                    'parent_service_id' => null,
                    'schedule_id'       => null,

                    'is_file'           => 1,
                    'file_itinerary_id' => $service['id'],
                    'file_status'       => $service['status'],
                    'file_amount_sale'  => $service['total_amount'],
                    'file_amount_cost'  => $service['total_cost_amount'],

                    'locked' => $service['status'] == "1" ? 0 : 1,

                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);

                DB::table('quote_service_rates')->insert([
                    'quote_service_id' => $new_service_id,
                    'service_rate_id'  => $service['service_rate_id'],
                    'created_at'       => Carbon::now(),
                    "updated_at"       => Carbon::now()
                ]);

                foreach ($service['accommodations'] as $passenger) {


                    $passengerFilter = collect($passengers)->filter(function ($p) use ($passenger) {
                        return $passenger['file_passenger_id'] === $p['id'];
                    })->first();

                    DB::table('quote_service_passengers')->insert([
                        'quote_service_id'   => $new_service_id,
                        'quote_passenger_id' => $passengerFilter['quote_passenger_id'],
                        'created_at'         => Carbon::now(),
                        "updated_at"         => Carbon::now()
                    ]);
                }
            }

            if ($service['entity'] == 'flight') {

                $new_service_id = DB::table('quote_services')->insertGetId([
                    'quote_category_id' => $new_category_id,
                    'type'              => $service['entity'],
                    'object_id'         => $service['object_id'],
                    'order'             => $orderItinerary,
                    'date_in'           => $service['date_in'],
                    'date_out'          => $service['date_out'],
                    'hour_in'           => $service['start_time'],
                    'nights'            => $nights,
                    'adult'             => $service['total_adults'],
                    'child'             => $service['total_children'],
                    'infant'            => $service['total_infants'],
                    'single'            => 0,
                    'double'            => 0,
                    'triple'            => 0,
                    'locked'            => $locked,
                    'triple_active'     => 0,
                    'on_request'        => $service['confirmation_status'],
                    'optional'          => 0,
                    'code_flight'       => $service['object_code'],
                    'origin'            => null,
                    'destiny'           => null,
                    'extension_id'      => null,
                    'parent_service_id' => null,
                    'schedule_id'       => null,

                    'is_file'           => 1,
                    'file_itinerary_id' => $service['id'],
                    'file_status'       => $service['status'],
                    'file_amount_sale'  => $service['total_amount'],
                    'file_amount_cost'  => $service['total_cost_amount'],

                    'locked' => $service['status'] == "1" ? 0 : 1,

                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            if ($service['entity'] == 'hotel') {
                $order = 1;
                $single = 0;
                $double = 0;
                $triple = 0;
                $quoteDistributionRooms = [];
                foreach ($service['rooms'] as $room) {

                    $new_service_id = DB::table('quote_services')->insertGetId([
                        'quote_category_id' => $new_category_id,
                        'type'              => $service['entity'],
                        'object_id'         => $service['object_id'],
                        'order'             => $orderItinerary,
                        'date_in'           => $service['date_in'],
                        'date_out'          => $service['date_out'],
                        'hour_in'           => $service['start_time'],
                        'nights'            => $nights,
                        'adult'             => $room['total_adults'],
                        'child'             => $room['total_children'],
                        'infant'            => $service['total_infants'],
                        'single'            => $room['single'],
                        'double'            => $room['double'],
                        'triple'            => $room['triple'],
                        'locked'            => $locked,
                        'triple_active'     => 0,
                        'on_request'        => $room['confirmation_status'],
                        'optional'          => 0,
                        'code_flight'       => null,
                        'origin'            => null,
                        'destiny'           => null,
                        'extension_id'      => null,
                        'parent_service_id' => null,
                        'schedule_id'       => null,

                        'is_file'           => 1,
                        'file_itinerary_id' => $room['itinerary_id'],
                        'file_status'       => $room['status'],
                        'file_amount_sale'  => $room['amount_sale'],
                        'file_amount_cost'  => $room['amount_cost'],

                        'locked' => $room['status'] == "1" ? 0 : 1,

                        'created_at' => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);

                    DB::table('quote_service_rooms')->insert([
                        'quote_service_id'  => $new_service_id,
                        'rate_plan_room_id' => $room['rate_plan_room_id'],
                        'created_at'        => Carbon::now(),
                        "updated_at"        => Carbon::now()
                    ]);

                    foreach ($room['accommodations'] as $px => $passenger) {


                        $passengerFilter = collect($passengers)->filter(function ($p) use ($passenger) {
                            return $passenger['file_passenger_id'] === $p['id'];
                        })->first();

                        DB::table('quote_service_passengers')->insert([
                            'quote_service_id'   => $new_service_id,
                            'quote_passenger_id' => $passengerFilter['quote_passenger_id'],
                            'created_at'         => Carbon::now(),
                            "updated_at"         => Carbon::now()
                        ]);

                        $room['accommodations'][$px]['quote_passenger_id'] = $passengerFilter['quote_passenger_id'];
                    }

                    if (count($quoteDistributions) == 0) {

                        $typeRoom = '';
                        $typeRoomName = '';
                        if ($room['occupation'] == 1) {
                            $typeRoom = 'single';
                            $typeRoomName = 'SGL';
                            $single++;
                        }
                        if ($room['occupation'] == 2) {
                            $typeRoom = 'double';
                            $typeRoomName = 'DBL';
                            $double++;
                        }
                        if ($room['occupation'] == 3) {
                            $typeRoom = 'triple';
                            $typeRoomName = 'TPL';
                            $triple++;
                        }

                        array_push($quoteDistributionRooms, [
                            'type_room'      => $typeRoom,
                            'type_room_name' => $typeRoomName,
                            'occupation'     => $room['occupation'],
                            'single'         => $room['single'],
                            'double'         => $room['double'],
                            'triple'         => $room['triple'],
                            'adult'          => $room['total_adults'],
                            'child'          => $room['total_children'],
                            'order'          => $order,
                            'quote_id'       => $new_object_id,
                            'passengers'     => $room['accommodations']
                        ]);
                        $order++;
                    }
                }

                if (count($quoteDistributions) == 0) {
                    $accommodation['single'] = $single;
                    $accommodation['double'] = $double;
                    $accommodation['double_child'] = 0;
                    $accommodation['triple'] = $triple;
                    $accommodation['triple_child'] = 0;

                    $quoteDistributions = $quoteDistributionRooms;
                }
            }
        }

        DB::table('quote_people')->insert([
            'adults'     => $adults,
            'child'      => $children,
            'quote_id'   => $new_object_id,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ]);


        foreach ($quoteDistributions as $distribution) {

            $quote_distribution = DB::table('quote_distributions')->insertGetId([
                'type_room'      => $distribution['type_room'],
                'type_room_name' => $distribution['type_room_name'],
                'occupation'     => $distribution['occupation'],
                'single'         => $distribution['single'],
                'double'         => $distribution['double'],
                'triple'         => $distribution['triple'],
                'adult'          => $distribution['adult'],
                'child'          => $distribution['child'],
                'order'          => $distribution['order'],
                'quote_id'       => $new_object_id,
                "created_at"     => Carbon::now(),
                "updated_at"     => Carbon::now()
            ]);

            foreach ($distribution['passengers'] as $passenger) {
                DB::table('quote_distribution_passengers')->insertGetId([
                    'quote_distribution_id' => $quote_distribution,
                    'quote_passenger_id'    => $passenger['quote_passenger_id'],
                    "created_at"            => Carbon::now(),
                    "updated_at"            => Carbon::now()
                ]);
            }
        }

        if (count($accommodation) > 0) {
            $new_accommodation = new QuoteAccommodation();
            $new_accommodation->quote_id = $new_object_id;
            $new_accommodation->single = $accommodation['single'];
            $new_accommodation->double = $accommodation['double'];
            $new_accommodation->double_child = $accommodation['double_child'];
            $new_accommodation->triple = $accommodation['triple'];
            $new_accommodation->triple_child = $accommodation['triple_child'];
            $new_accommodation->save();
        }

        return $new_object_id;
    }

    public function destroy($id)
    {
        $find_editing_quote = QuoteLog::where('type', 'editing_quote')->where('object_id', $id)->count();

        if ($find_editing_quote > 0) {
            return Response::json(['success' => false, 'message' => 'editing']);
        }

        DB::transaction(function () use ($id) {
            DB::table('quotes')->where('id', $id)->update([
                'status' => 0
            ]);
        });

        $response = ['success' => true];

        return Response::json($response);
    }

    public function finishCloneFile($quote_id, Request $request)
    {
        $param_update = [
            'clone_executed' => 1
        ];
        $language_id = $request->input('language_id', null);
        if ($language_id !== null) {
            $param_update['language_id'] = $language_id;
        }

        Quote::where('id', $quote_id)->update($param_update);
    }

    public function update_schedule($quote_service_id, Request $request)
    {

        try {
            $schedule_id = $request->__get('schedule_id');
            $quote_service = QuoteService::where('id', $quote_service_id)->first();
            $quote_service->schedule_id = $schedule_id;
            $quote_service->hour_in = null;
            $quote_service->save();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }
}
