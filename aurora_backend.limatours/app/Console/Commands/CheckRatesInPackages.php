<?php

namespace App\Console\Commands;

use App\Hotel;
use App\Package;
use App\PackagePlanRate;
use App\RatesPlansCalendarys;
use App\Service;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use phpDocumentor\Reflection\Types\Integer;

class CheckRatesInPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:check-rates'; // En prod 1 vez x dia

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::transaction(function () {
            $packages_with_errors = [];
            $packages_with_errors_i = 0;
            $packages = Package::select(['id', 'code', 'extension'])
                ->where('status', 1)
                ->with([
                    'translations' => function ($query) {
                        $query->where('language_id', 1);
                    }
                ])
                ->with([
                    'plan_rates' => function ($query) {
                        $query->where('status', 1);
                        $query->with('service_type');
                        $query->with([
                            'plan_rate_categories.type_class.translations' => function ($query1) {
                                $query1->where('language_id', 1);
                            }
                        ]);
                        $query->with([
                            'plan_rate_categories.services' => function ($query2) {
                                $query2->with([
                                    'hotel.channel',
                                    'service_rooms.rate_plan_room.rate_plan' =>function ($query0) {
                                        $query0->withTrashed();
                                    },
                                    'service_rooms.rate_plan_room.room' => function ($query0) {
                                        $query0->withTrashed();
                                        $query0->with(['room_type.translations'=> function ($query0) {
                                            $query0->where('language_id', 1);
                                        }]);
                                    },
                                    'service_rates'
                                ]);
                                $query2->with([
                                    'service' => function ($query) {
                                        $query->with([
                                            'service_rate.service_rate_plans'
                                        ]);
                                        $query->with([
                                            'serviceType' => function ($query) {
                                                $query->with([
                                                    'translations' => function ($query) {
                                                        $query->select('object_id', 'value');
                                                        $query->where('type', 'servicetype');
                                                        $query->where('language_id', 1);
                                                    },
                                                ]);
                                            }
                                        ]);
                                    }
                                ]);
                                $query2->orderBy('date_in');
                                $query2->orderBy('order');
                            }
                        ]);
                    }
                ])
//            ->skip(10)
//                ->limit(10)
//                ->whereIn('id', [725])
//                ->get();

                ->chunk(10, function ($packages) use ($packages_with_errors, $packages_with_errors_i) {
                    $this->output->progressStart(10);

                    foreach ($packages as $package) {
                        sleep(1);
                        $packages_with_errors_i = count($packages_with_errors);
                        $packages_with_errors[$packages_with_errors_i] = array(
                            "id" => $package->id,
                            "code" => $package->code,
                            "name" => $package->translations[0]->name,
                            "plan_rates" => [],
                        );
                        if ($package->code === null) {
                            $packages_with_errors[$packages_with_errors_i]['code'] =
                                ($package->extension === 1)
                                    ? "EX" . $package->id
                                    : "PAQ" . $package->id;
                        }

                        $plan_rates_i = 0;
                        foreach ($package->plan_rates as $plan_rate) {

                            $plan_rates_i = count($packages_with_errors[$packages_with_errors_i]['plan_rates']);
                            $packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i] = [
                                "id" => $plan_rate->id,
                                "name" => '[' . $plan_rate->service_type->code . '] ' . $plan_rate->name,
                                "categories" => []
                            ];

                            foreach ($plan_rate->plan_rate_categories as $category) {

                                $categories_i = count($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories']);
                                $packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i] = [
                                    "id" => $category->id,
                                    "name" => $category->type_class->translations[0]->value,
                                    "services" => []
                                ];

                                // price_from  |  price_from_pax
                                foreach ($category->services as $p_s) {
                                    $date_in = $p_s->date_in;
                                    $date_out = $p_s->date_out;

                                    if ($p_s->type == "service") {
                                        if (!$p_s->service) {
                                            $service_ = Service::where('id', $p_s->object_id)->withTrashed()->first();
                                            array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                [
                                                    "package_service_id" => $p_s->id,
                                                    "type" => $p_s->type,
                                                    "code" => $service_->aurora_code,
                                                    "date" => $p_s->date_in,
                                                    "name" => $service_->name,
                                                    "error" => "Servicio eliminado. package_service_id: " . '(' . $p_s->id . ')'. ' - service_id: ' . $p_s->object_id,
                                                ]);
                                        }
                                        elseif ( !$p_s->service->status ){
                                            $service_ = Service::where('id', $p_s->object_id)->withTrashed()->first();
                                            array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                [
                                                    "package_service_id" => $p_s->id,
                                                    "type" => $p_s->type,
                                                    "code" => $service_->aurora_code,
                                                    "date" => $p_s->date_in,
                                                    "name" => $service_->name,
                                                    "error" => "Servicio desactivado. package_service_id: " . '(' . $p_s->id . ')'. ' - service_id: ' . $p_s->object_id,
                                                ]);
                                        }
                                        else {

                                            foreach ($p_s->service->service_rate as $s_rate) {
                                                $s_rate->price_from = '';
                                                $s_rate->price_from_pax = '';
                                                foreach ($s_rate->service_rate_plans as $s_plan) {
                                                    if (strtotime($s_plan->date_from) <= strtotime($date_in) &&
                                                        strtotime($s_plan->date_to) >= strtotime($date_in)) {
                                                        $s_rate->price_from = $s_plan->price_adult;
                                                        $s_rate->price_from_pax = $s_plan->pax_from;
                                                        break;
                                                    }
                                                }

                                                // Agregando a la matriz de errores si no encuentra precios
                                                if ($s_rate->price_from === '' || $s_rate->price_from == 0 || $s_rate->price_from === null) {
//                                                    var_export( json_encode( $s_rate->service_rate_plans ) ); die;
                                                    array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                        [
                                                            "package_service_id" => $p_s->id,
                                                            "type" => $p_s->type,
                                                            "code" => $p_s->service->aurora_code,
                                                            "date" => $p_s->date_in,
                                                            "name" => $p_s->service->name,
                                                            "error" => "Precio no encontrado, en la tarifa: " . '"(' . $s_rate->id . ') ' . $s_rate->name . '"',
                                                        ]);
                                                }

                                            }
                                        }
                                    }

                                    if ($p_s->type == "hotel") {

                                        if (!$p_s->hotel) {
                                            $hotel_ = Hotel::with('channel')->where('id',$p_s->object_id)->withTrashed()->first();
                                            array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                [
                                                    "package_service_id" => $p_s->id,
                                                    "type" => $p_s->type,
                                                    "code" => $hotel_->channel[0]->code,
                                                    "date" => $p_s->date_in,
                                                    "name" => $hotel_->name,
                                                    "error" => "Hotel eliminado. package_service_id: " . '(' . $p_s->id . ')' . ' - hotel_id: ' . $p_s->object_id,
                                                ]);
                                        }
                                        elseif ( !$p_s->hotel->status ){
                                            $hotel_ = Hotel::with('channel')->where('id',$p_s->object_id)->withTrashed()->first();
                                            array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                [
                                                    "package_service_id" => $p_s->id,
                                                    "type" => $p_s->type,
                                                    "code" => $hotel_->channel[0]->code,
                                                    "date" => $p_s->date_in,
                                                    "name" => $hotel_->name,
                                                    "error" => "Hotel desactivado. package_service_id: " . '(' . $p_s->id . ')' . ' - hotel_id: ' . $p_s->object_id,
                                                ]);
                                        }
                                        else {
                                            $date_service_in = Carbon::parse($p_s->date_in);
                                            $date_service_out = Carbon::parse($p_s->date_out);
                                            $nights = $date_service_in->diffInDays($date_service_out);
                                            $errors_ = 0;

                                            foreach ($p_s->service_rooms as $s_room) {
                                                $s_room->rate_plan_room->first_rate = [];
                                                $new_calendarys = [];

                                                $calendarys_ = RatesPlansCalendarys::where('rates_plans_room_id', $s_room->rate_plan_room->id)
                                                    ->where('date', '<=', $p_s->date_out)
                                                    ->where('date', '>=', $p_s->date_in)
                                                    ->with('rate')
                                                    ->get();

                                                foreach ($calendarys_ as $calendary) {
                                                    if (strtotime($calendary->date) <= strtotime($date_out) &&
                                                        strtotime($calendary->date) >= strtotime($date_in)) {
                                                        if (count($s_room->rate_plan_room->first_rate) == 0 && count($calendary->rate) > 0) {
                                                            $s_room->rate_plan_room->first_rate = $calendary->rate;
                                                        }
                                                        array_push($new_calendarys, $calendary);
                                                    }
                                                }
                                                unset($s_room->rate_plan_room->calendarys);
                                                $s_room->rate_plan_room->calendarys_in_dates = $new_calendarys;

                                                // Agregando a la matriz de errores si no encuentra precios
                                                if (count($s_room->rate_plan_room->first_rate) === 0) {
                                                    array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                        [
                                                            "package_service_id" => $p_s->id,
                                                            "type" => $p_s->type,
                                                            "code" => $p_s->hotel->channel[0]->code,
                                                            "date" => $p_s->date_in . ' - ' . $p_s->date_out,
                                                            "name" => $p_s->hotel->name,
                                                            "error" => "Precio no encontrado, en la tarifa: " . '"(' . $s_room->id . ') ' .
                                                                $s_room->rate_plan_room->room->room_type->translations[0]->value . '"' .
                                                                ', en el tipo de habitación: ' . $s_room->rate_plan_room->room->room_type->occupation
                                                        ]);
                                                }

                                                if( !($s_room->rate_plan_room->room->state) ){
                                                    array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                        [
                                                            "package_service_id" => $p_s->id,
                                                            "type" => $p_s->type,
                                                            "code" => $p_s->hotel->channel[0]->code,
                                                            "date" => $p_s->date_in . ' - ' . $p_s->date_out,
                                                            "name" => $p_s->hotel->name,
                                                            "error" => "Habitación desactivada: " . '"(' . $s_room->id . ') ' .
                                                                $s_room->rate_plan_room->room->room_type->translations[0]->value . '"' .
                                                                ', en el tipo de habitación: ' . $s_room->rate_plan_room->room->room_type->occupation
                                                        ]);
                                                }

                                                if( $s_room->rate_plan_room->room->deleted_at !== null && $s_room->rate_plan_room->room->deleted_at !== "" ){
                                                    array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                        [
                                                            "package_service_id" => $p_s->id,
                                                            "type" => $p_s->type,
                                                            "code" => $p_s->hotel->channel[0]->code,
                                                            "date" => $p_s->date_in . ' - ' . $p_s->date_out,
                                                            "name" => $p_s->hotel->name,
                                                            "error" => "Habitación eliminada: " . '"(' . $s_room->id . ') ' .
                                                                $s_room->rate_plan_room->room->room_type->translations[0]->value . '"' .
                                                                ', en el tipo de habitación: ' . $s_room->rate_plan_room->room->room_type->occupation
                                                        ]);
                                                }

                                                if( !($s_room->rate_plan_room->rate_plan->status) ){
                                                    array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                        [
                                                            "package_service_id" => $p_s->id,
                                                            "type" => $p_s->type,
                                                            "code" => $p_s->hotel->channel[0]->code,
                                                            "date" => $p_s->date_in . ' - ' . $p_s->date_out,
                                                            "name" => $p_s->hotel->name,
                                                            "error" => "Tarifa desactivada: " . '"(' . $s_room->rate_plan_room->rate_plan->id . ') ' .
                                                                $s_room->rate_plan_room->rate_plan->name . '"' .
                                                                ', en el tipo de habitación: ' . $s_room->rate_plan_room->room->room_type->occupation
                                                        ]);
                                                }

                                                if( $s_room->rate_plan_room->rate_plan->deleted_at !== null && $s_room->rate_plan_room->rate_plan->deleted_at !== "" ){
                                                    array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                        [
                                                            "package_service_id" => $p_s->id,
                                                            "type" => $p_s->type,
                                                            "code" => $p_s->hotel->channel[0]->code,
                                                            "date" => $p_s->date_in . ' - ' . $p_s->date_out,
                                                            "name" => $p_s->hotel->name,
                                                            "error" => "Tarifa eliminada: " . '"(' . $s_room->rate_plan_room->rate_plan->id . ') ' .
                                                                $s_room->rate_plan_room->rate_plan->name . '"' .
                                                                ', en el tipo de habitación: ' . $s_room->rate_plan_room->room->room_type->occupation
                                                        ]);
                                                }

                                                ///////////////////////////////
                                                for($i=0; $i<$nights; $i++){
                                                    $date_find = Carbon::parse($p_s->date_in)->addDays($i)->format('Y-m-d');
                                                    $coin = 0;
                                                    foreach ($s_room->rate_plan_room->calendarys_in_dates as $calendary) {
                                                        if( $calendary->date == $date_find ){
                                                            $coin++;
                                                        }
                                                    }
                                                    if( $coin === 0 ){
                                                        $errors_++;
                                                    }
                                                }

                                            }

                                            if( $errors_ > 0 ){
                                                array_push($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services'],
                                                    [
                                                        "package_service_id" => $p_s->id,
                                                        "type" => $p_s->type,
                                                        "code" => $p_s->hotel->channel[0]->code,
                                                        "date" => $p_s->date_in . ' - ' . $p_s->date_out,
                                                        "name" => $p_s->hotel->name,
                                                        "error" => "Existe uno o más días del hotel cuyos precios no fueron encontrados en habitaciones."
                                                    ]);
                                            }
                                        }

                                    }

                                }

                                if (count($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]['services']) === 0) {
                                    unset($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories'][$categories_i]);
                                }

                            }

                            if (count($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]['categories']) === 0) {
                                unset($packages_with_errors[$packages_with_errors_i]['plan_rates'][$plan_rates_i]);
                            }
                        }
                        if (count($packages_with_errors[$packages_with_errors_i]['plan_rates']) === 0) {
                            unset($packages_with_errors[$packages_with_errors_i]);
                        }
                        $this->output->progressAdvance();
                    }

                    if (count($packages_with_errors) > 0) {

                        foreach ($packages_with_errors as $packages_with_error) {
                            foreach ($packages_with_error['plan_rates'] as $plan_rate) {
                                $plan_rate_ = PackagePlanRate::find($plan_rate['id']);
                                $plan_rate_->status = 0;
                                $plan_rate_->save();
                            }
                        }

                        $mail = mail::to("neg@limatours.com.pe");
                        $mail->cc("producto@limatours.com.pe", "qr@limatours.com.pe", "kams@limatours.com.pe","juancarlos.huaman@tui.com");
                        $mail->send(new \App\Mail\NotificationPackageRates($packages_with_errors));
                    }
//                    else {
//                        return print_r(' - Sin errores');
//                    }

                });

//                    return print_r($packages_with_errors);
//                    return print_r(json_encode( $packages[0] ));


        });

    }
}
