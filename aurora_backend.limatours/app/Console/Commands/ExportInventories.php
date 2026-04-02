<?php

namespace App\Console\Commands;


use App\Hotel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportInventories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:invetories';

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
        $year = '2023';
        $hotels = Hotel::where('chain_id',1)->where('status',1)->get();

        Excel::store(new  \App\Exports\HotelInvetoryExport($hotels,$year), 'hotels_inventories.xlsx', 'public');

    }


}
