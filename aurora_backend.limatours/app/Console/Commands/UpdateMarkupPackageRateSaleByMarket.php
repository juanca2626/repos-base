<?php

namespace App\Console\Commands;

use App\Http\Traits\Package;
use App\PackagePlanRateCategory;
use App\PackageRateSaleMarkup;
use Illuminate\Console\Command;

class UpdateMarkupPackageRateSaleByMarket extends Command
{
    use Package;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:update-markup-package-rate-sale-by-market';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza la tabla de precios de venta por mercado de los paquetes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->updateMarketMarkupPackageRateSale();
    }

    public function updateMarketMarkupPackageRateSale()
    {
        try {
            $startTime = microtime(true);

            $sales = PackageRateSaleMarkup::where("seller_type", "App\\Market")
                ->where("seller_id", 21)->get();

            $this->output->progressStart($sales->count());

            foreach ($sales as $sale) {
                $sale_id = $sale->id;
                $markup = $sale->markup;
                $prices = collect();
                $category_ids = PackagePlanRateCategory::where('package_plan_rate_id', $sale->package_plan_rate_id)->pluck('id');
                $ages_children = $this->getPackageChildrenByCategory($category_ids[0]);

                foreach ($category_ids as $category_id) {
                    // Eliminar el dd() para evitar la interrupción del comando
                    $this->calculatePricePackageCopy($category_id, $sale_id, $markup);
                    if ($ages_children->count() > 0) {
                        $prices->add($this->updateDynamicSaleRatesPackageChildren($category_id, $ages_children, $sale_id));
                    }
                }
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            // Convertir el tiempo a horas:minutos:segundos
            $hours = floor($executionTime / 3600);
            $minutes = floor(($executionTime / 60) % 60);
            $seconds = $executionTime % 60;

            $this->info('Markup actualizado correctamente.');
            $this->info(sprintf('Tiempo total de ejecución: %02d:%02d:%02d', $hours, $minutes, $seconds));

        } catch (\Exception $e) {
            // Finalizar la barra de progreso si hay un error
            $this->output->progressFinish();
            $this->error('Error: ' . $e->getMessage());
        }
    }

}
