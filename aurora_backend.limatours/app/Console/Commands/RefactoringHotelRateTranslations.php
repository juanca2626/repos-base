<?php

namespace App\Console\Commands;

use App\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefactoringHotelRateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:refactoring_hotel_rate_translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soluciona error en la duplicidad de los nombres de la tarifas';

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
            $totalRegister = DB::table('translations')
                ->select('object_id', DB::raw('count(*) as total'))
                ->where('type', '=', 'rates_plan')
                ->whereNull('deleted_at')
                ->groupBy('object_id')
                ->havingRaw('count(*) > 12')
                ->orderBy('id', 'desc')
                ->get();
            $this->output->progressStart($totalRegister->count());
            foreach ($totalRegister as $item) {
                sleep(1);
                $rowsCountDelete = $item->total - 12;
                $rowsDelete = DB::table('translations')
                    ->select('*')
                    ->where('object_id', '=', $item->object_id)
                    ->where('type', '=', 'rates_plan')
                    ->orderBy('created_at', 'asc')
                    ->take($rowsCountDelete)
                    ->get();
                foreach ($rowsDelete as $deleteRow) {
                    $delete = Translation::find($deleteRow->id);
                    $delete->delete();
                }
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
        });
    }
}
