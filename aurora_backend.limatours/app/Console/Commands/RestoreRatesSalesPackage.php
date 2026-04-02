<?php

namespace App\Console\Commands;

use App\PackagePermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreRatesSalesPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rs_rates_sales_package {package_or_user_id} {--user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permite restaurar el backup de las tabla de tarifas de venta de un paquete';

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
                        $package_rate_sale_markup_copies = DB::table('package_rate_sale_markup_copies')
                            ->whereIn('package_id', $package_ids)->get();
                        $package_dynamic_sale_rate_copies = DB::table('package_dynamic_sale_rate_copies')
                            ->whereIn('package_id', $package_ids)->get();
                    } else {
                        $this->info('No se encontro paquetes asignados al usuario');
                    }
                } else {
                    $package_rate_sale_markup_copies = DB::table('package_rate_sale_markup_copies')
                        ->where('package_id', $package_or_user_id)->get();
                    $package_dynamic_sale_rate_copies = DB::table('package_dynamic_sale_rate_copies')
                        ->where('package_id', $package_or_user_id)->get();
                }

                if ($package_rate_sale_markup_copies->count() > 0 || $package_dynamic_sale_rate_copies->count() > 0) {
                    $total = $package_rate_sale_markup_copies->count() + $package_dynamic_sale_rate_copies->count();
                    $this->output->progressStart($total);
                    foreach ($package_rate_sale_markup_copies as $sale_markup) {
                        sleep(1);
                        DB::table('package_rate_sale_markups')
                            ->updateOrInsert(
                                [
                                    'id' => $sale_markup->object_id,
                                ],
                                [
                                    'seller_type' => $sale_markup->seller_type,
                                    'seller_id' => $sale_markup->seller_id,
                                    'markup' => $sale_markup->markup,
                                    'status' => $sale_markup->status,
                                    'package_plan_rate_id' => $sale_markup->package_plan_rate_id,
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s"),
                                ]
                            );
                        $this->output->progressAdvance();
                    }
                    foreach ($package_dynamic_sale_rate_copies as $sale_rate) {
                        sleep(1);
                        DB::table('package_dynamic_sale_rates')
                            ->updateOrInsert(
                                [
                                    'id' => $sale_rate->object_id,
                                ],
                                [
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
