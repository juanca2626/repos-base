<?php

namespace App\Console\Commands;

use App\Service;
use Carbon\Carbon;
use App\ServiceTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TextDuplicateServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:text-duplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar duplicados de los textos de los servicios.';

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

        //$serviceTraslations = ServiceTranslation::all();
        $serviceTraslations = DB::table('service_translations as st')
            ->whereIn(DB::raw('(service_id, language_id, created_at)'), function ($query) {
                $query->select(DB::raw('service_id, language_id, MIN(created_at)'))
                    ->from('service_translations')
                    ->groupBy('service_id', 'language_id')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->get();


        $i = 0;
        foreach ($serviceTraslations as $serviceTraslation) {
            $this->info($serviceTraslation->id);
            $data = ServiceTranslation::find($serviceTraslation->id);
            if ($data) {
                $data->deleted_at = Carbon::now();
                $data->save();
            }
            $i++;
        }

        $this->info('Total Services duplicados: '.$i);
    }
}
