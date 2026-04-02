<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;    
use Src\Modules\File\Presentation\Http\Traits\PaymentStatement;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class UpdateChannelHyperguest extends Command
{
    use PaymentStatement;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update_channel_hyperguest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    /**
     * Execute the job.
     */
    public function handle()
    {
        $query="           
                select 
                file_hotel_room_units.reservations_rates_plans_rooms_id,
                file_hotel_room_units.file_hotel_room_id
                from file_hotel_room_units left join file_hotel_rooms on file_hotel_room_units.file_hotel_room_id = file_hotel_rooms.id  
                where file_hotel_room_units.channel_id=6 and file_hotel_rooms.channel_reservation_code_master is null
                group by file_hotel_room_units.file_hotel_room_id";

        $results = DB::select($query);
        $result_params = collect($results)->pluck('reservations_rates_plans_rooms_id');

        $aurora = new AuroraExternalApiService();
        $channel_hyperguest_codes = (array) $aurora->searchChannelCodeHyperguest(['reservations_rates_plans_rooms_id' =>$result_params->toArray()]);  
        $channel_hyperguest_codes = collect($channel_hyperguest_codes);
       
        foreach($results as $result)
        {
            $hyperguest = $channel_hyperguest_codes->firstWhere('id', $result->reservations_rates_plans_rooms_id);
       
            if($hyperguest)
            {                 
                FileHotelRoomEloquentModel::where('id', $result->file_hotel_room_id)
                ->update(['channel_reservation_code_master'=>$hyperguest->channel_reservation_code_master]);
            } 
        }
  
    }     

}

