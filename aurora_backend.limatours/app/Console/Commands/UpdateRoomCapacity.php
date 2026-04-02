<?php

namespace App\Console\Commands;

use App\Hotel;
use App\RatesPlans;
use App\Room;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateRoomCapacity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:room_capacity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar todas las ocupaciones de las habitaciones en base a niños y la capacidad maxima';

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
        $rooms = DB::table('hotels')->leftJoin('rooms', 'rooms.hotel_id', '=', 'hotels.id') 
        ->leftJoin('room_types', 'rooms.room_type_id', '=', 'room_types.id')
        ->select('hotels.name as hotel_name', 'hotels.allows_child', 'hotels.allows_teenagers','rooms.id','rooms.max_capacity','rooms.min_adults','rooms.max_adults','rooms.max_child', 'room_types.occupation')
        ->where('hotels.status','1')->where('rooms.state','1')
        ->get();


        foreach ($rooms as $room) {

            // if($room->min_adults == $room->max_adults and $room->occupation>1 ){
            //     echo $room->hotel_name." ".$room->id."\n";
            // }
            if($room->allows_child == "1" or $room->allows_teenagers == "1"){
                Room::where('id',$room->id)->update(['min_adults'=>0,'max_child'=>$room->max_capacity]);
            }else{
                Room::where('id',$room->id)->update(['min_adults'=>1,'max_child'=>0]);
            }
            
        }
    }
}
