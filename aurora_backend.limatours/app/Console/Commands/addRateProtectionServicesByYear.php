<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceRatePlan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class addRateProtectionServicesByYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:add_rates_protection_services';

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
        $year = Carbon::now()->year;
        $year_to = Carbon::now()->year + 1;
        $protectionValue = 5;

        //Todo Eliminamos todas las tarifas del 2024 si tiene cada uno de los servicios
        $servicesYearTo = $this->getQueryServicesByYear($year_to);
        foreach ($servicesYearTo as $service) {
            foreach ($service['service_rate'] as $service_rate) {
                foreach ($service_rate['service_rate_plans'] as $service_rate_plans) {
                    $findDeleted = ServiceRatePlan::find($service_rate_plans['id']);
                    $findDeleted->delete();
                }
            }
        }

        $servicesYear = $this->getQueryServicesByYear($year);
        //Todo Recorremos y aplicamos el margen de proteccion
        foreach ($servicesYear as $service) {
            foreach ($service['service_rate'] as $service_rate) {
                foreach ($service_rate['service_rate_plans'] as $service_rate_plans) {
                    $priceAdult = $service_rate_plans->price_adult;
                    $priceAdultAmount = ($priceAdult * $protectionValue) / 100;
                    $totalPriceAdult = $priceAdult + $priceAdultAmount;
                    $totalPriceAdult = number_format($totalPriceAdult, 2);

                    $priceChild = $service_rate_plans->price_child;
                    $priceChildAmount = ($priceChild * $protectionValue) / 100;
                    $totalPriceChild = $priceChild + $priceChildAmount;
                    $totalPriceChild = number_format($totalPriceChild, 2);

                    $priceInfant = $service_rate_plans->price_infant;
                    $priceInfantAmount = ($priceInfant * $protectionValue) / 100;
                    $totalPriceInfant = $priceInfant + $priceInfantAmount;
                    $totalPriceInfant = number_format($totalPriceInfant, 2);

                    $priceGuide = $service_rate_plans->price_guide;
                    $priceGuideAmount = ($priceGuide * $protectionValue) / 100;
                    $totalPriceGuide = $priceGuide + $priceGuideAmount;
                    $totalPriceGuide = number_format($totalPriceGuide, 2);

                    $service_rate_plan = new ServiceRatePlan();
                    $service_rate_plan->service_rate_id = $service_rate->id;
                    $service_rate_plan->date_from = str_replace($year, $year_to, $service_rate_plans->date_from);
                    $service_rate_plan->date_to = str_replace($year, $year_to, $service_rate_plans->date_to);
                    $service_rate_plan->price_adult = $totalPriceAdult;
                    $service_rate_plan->price_child = $totalPriceChild;
                    $service_rate_plan->price_infant = $totalPriceInfant;
                    $service_rate_plan->price_guide = $totalPriceGuide;
                    $service_rate_plan->pax_from = $service_rate_plans->pax_from;
                    $service_rate_plan->pax_to = $service_rate_plans->pax_to;
                    $service_rate_plan->monday = $service_rate_plans->monday;
                    $service_rate_plan->tuesday = $service_rate_plans->tuesday;
                    $service_rate_plan->wednesday = $service_rate_plans->wednesday;
                    $service_rate_plan->thursday = $service_rate_plans->thursday;
                    $service_rate_plan->friday = $service_rate_plans->friday;
                    $service_rate_plan->saturday = $service_rate_plans->saturday;
                    $service_rate_plan->sunday = $service_rate_plans->sunday;
                    $service_rate_plan->user_id = $service_rate_plans->user_id;
                    $service_rate_plan->service_cancellation_policy_id = $service_rate_plans->service_cancellation_policy_id;
                    $service_rate_plan->status = 1;
                    $service_rate_plan->flag_migrate = 1;
                    $service_rate_plan->save();
                }
            }
        }

    }

    private function getQueryServicesByYear($year)
    {
       return Service::with([
            'service_rate' => function ($query) use ($year) {
                $query->select(['id', 'name', 'service_id', 'status']);
                $query->with([
                    'service_rate_plans' => function ($query) use ($year) {
                        $query->whereYear('date_from', $year);
                    }
                ]);
            }
        ])->whereIn('aurora_code', [
            'AQPTRA',
            'AQPTR8',
            'HAZTUA',
            'HAZTUV',
            'ICATU2',
            'ICATU5',
            'ICATU3',
            'ICATU4',
            'ICATU6',
            'LIMTU2',
            'LIMTU3',
            'LIMTUB',
            'LIMTU6',
            'LIMTUD',
            'LIMTU7',
            'LIMTUA',
            'LIMTUV',
            'NAZTU1',
            'NAZTU6',
            'NAZTU2',
            'NAZTU5',
            'NAZTU3',
            'NAZTU7',
            'PCSTU1',
            'PCSTU3',
            'PCSTU2',
            'PCSTU4',
            'PUNTR6',
        ])->get(['id']);
    }
}
