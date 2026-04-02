<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;

class ProcessUpdateConfirmationCodeHyperguestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;    
 
    /**
     * Create a new job instance.
     */
    public function __construct( )
    {                        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {    

        FileHotelRoomEloquentModel::whereNull('confirmation_code')
        ->where(function($query){
            $query->whereNotNull('channel_reservation_code_master');
            $query->where('channel_reservation_code_master','<>','');
        })->chunk(10000, function ($rooms) {
            foreach ($rooms as $room) {                 
                $room->confirmation_code = $room->channel_reservation_code_master;
                $room->updated_at = date('Y-m-d H:i:s');
                $room->save();                            
            }
        });              
    }
    
}
