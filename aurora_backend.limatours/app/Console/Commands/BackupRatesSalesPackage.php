<?php

namespace App\Console\Commands;

use App\Package;
use App\PackagePermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupRatesSalesPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:bk_rates_sales_package {package_or_user_id} {--user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permite guardar un backup de las tabla de tarifas de venta de un paquete';

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
        try {
            $package_or_user_id = (int)$this->argument('package_or_user_id');
            $option_user = (int)$this->option('user');
            DB::transaction(function () use ($package_or_user_id, $option_user) {
                if ($option_user) {
                    $package_ids = PackagePermission::where('user_id',
                        $package_or_user_id)->select(['package_id'])->distinct()->get();
                    if ($package_ids->count() > 0) {
                        $packages = Package::with('plan_rates')
                            ->with('plan_rates.plan_rate_categories')
                            ->with('plan_rates.package_rate_sale_markup')
                            ->with('plan_rates.plan_rate_categories.sale_rates')
                            ->whereIn('id', $package_ids->pluck('package_id'))->get();
                    } else {
                        $this->info('No se encontro paquetes asignados al usuario');
                    }
                } else {
                    $packages = Package::with('plan_rates')
                        ->with('plan_rates.plan_rate_categories')
                        ->with('plan_rates.package_rate_sale_markup')
                        ->with('plan_rates.plan_rate_categories.sale_rates')
                        ->where('id', $package_or_user_id)->get();
                }

                if ($packages->count() > 0) {
                    $this->output->progressStart($packages->count());
                    foreach ($packages as $package) {
                        sleep(1);
                        foreach ($package->plan_rates as $package_rate) {
                            foreach ($package_rate->plan_rate_categories as $category) {
                                foreach ($category->sale_rates as $sale_rate) {
                                    DB::table('package_dynamic_sale_rate_copies')
                                        ->updateOrInsert(
                                            [
                                                'object_id' => $sale_rate->id,
                                                'package_id' => $package->id,
                                            ],
                                            [
                                                'object_id' => $sale_rate->id,
                                                'package_id' => $package->id,
                                                'service_type_id' => $sale_rate->service_type_id,
                                                'package_plan_rate_category_id' => $sale_rate->package_plan_rate_category_id,
                                                'pax_from' => $sale_rate->pax_from,
                                                'pax_to' => $sale_rate->pax_to,
                                                'simple' => $sale_rate->simple,
                                                'double' => $sale_rate->double,
                                                'triple' => $sale_rate->triple,
                                                'package_rate_sale_markup_id' => $sale_rate->package_rate_sale_markup_id,
                                                'status' => $sale_rate->status,
                                                'created_at' => date("Y-m-d H:i:s"),
                                                'updated_at' => date("Y-m-d H:i:s"),
                                            ]
                                        );

                                }
                            }
                            foreach ($package_rate->package_rate_sale_markup as $sale_markup) {
                                DB::table('package_rate_sale_markup_copies')
                                    ->updateOrInsert(
                                        [
                                            'object_id' => $sale_markup->id,
                                            'package_id' => $package->id,
                                        ],
                                        [
                                            'object_id' => $sale_markup->id,
                                            'package_id' => $package->id,
                                            'seller_type' => $sale_markup->seller_type,
                                            'seller_id' => $sale_markup->seller_id,
                                            'markup' => $sale_markup->markup,
                                            'status' => $sale_markup->status,
                                            'package_plan_rate_id' => $sale_markup->package_plan_rate_id,
                                            'created_at' => date("Y-m-d H:i:s"),
                                            'updated_at' => date("Y-m-d H:i:s"),
                                        ]
                                    );
                            }
                        }
                        $this->output->progressAdvance();
                    }
                    $this->output->progressFinish();
                } else {
                    $this->info('No se encontro el paquete con el id ingresado');
                }
            });
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
