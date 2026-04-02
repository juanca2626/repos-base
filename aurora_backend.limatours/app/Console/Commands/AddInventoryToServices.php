<?php

namespace App\Console\Commands;

use App\ProgressBar;
use App\Service;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddInventoryToServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:addInventoryToServices {service_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agrega inventario a todos los servicios que no tengan o a uno en especifico';

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
            $service_id = (int)$this->argument('service_id');
            DB::transaction(function () use ($service_id) {
                $services = Service::with('service_rate')
                    ->with('service_rate')
                    ->whereHas('service_rate')
                    ->whereDoesntHave('service_rate.inventory',function($query){
                        $query->whereYear('date',2020);
                    });
                if ($service_id) {
                    $services->where('id', $service_id);
                }
                $services = $services->get();

                if($services->count() > 0){
                    $year = Carbon::now()->format('Y');
                    $date_from = Carbon::createFromFormat('d/m/Y', date('d/m/Y'))->setTimezone('America/Lima');
                    $date_to = Carbon::createFromFormat('d/m/Y', '31/12/' . $year)->setTimezone('America/Lima');
                    $difference_days = $date_from->diffInDays($date_to);

                    $created_at = date("Y-m-d H:i:s");

                    foreach ($services as $service) {

                        $service->service_rate->each(function ($item, $key) use (
                            $difference_days,
                            $created_at,
                            $date_from
                        ) {

                            for ($i = 0; $i <= $difference_days; $i++) {
                                $date = ($i === 0) ? $date_from : $date_from->addDay();
                                DB::table('service_inventories')->insert([
                                    'day' => $date->day,
                                    'date' => $date->format('Y-m-d'),
                                    'inventory_num' => 100,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => false,
                                    'service_rate_id' => $item->id,
                                    'created_at' => $created_at,
                                    'updated_at' => $created_at
                                ]);
                            }
                        });

//                        // Progress Disponibilidad
//                        ProgressBar::updateOrCreate([
//                            'slug' => 'service_progress_availability',
//                            'value' => 10,
//                            'type' => 'service',
//                            'object_id' => $service->id
//                        ]);
                    }
                }
                $this->info('Se agregaron correctamente. quedan:'. $services->count());
            });

        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
