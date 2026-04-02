<?php

namespace App\Console\Commands;

use App\DeactivatableEntity;
use App\Http\Traits\ClientServices;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDeactivatables extends Command
{

    use ClientServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deactivatables:check';

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
        DB::transaction(function () {
            $deactivatables = DeactivatableEntity::all();

            foreach ( $deactivatables as $deactivatable ){

                $date_now = Carbon::now();
                $difference_in_hours = Carbon::parse($deactivatable->created_at)->diffInHoursFiltered(function(Carbon $date) {
                    return !$date->isWeekend();
                }, $date_now);

                if( $difference_in_hours >= (int)($deactivatable->after_hours) ){ // 48 hrs
                    $model_name = $deactivatable->entity;
                    $entity = $model_name::find( $deactivatable->object_id );
                    // block_service
                    if( $deactivatable->action === 'block_service' ){ // alguna acción en particular
                        if( $this->block_in_clients($entity->id, $entity->exclusive_client_id ) ){
                            $deactivatable->delete();
                        }
                    } else {
                        if($deactivatable->action === 'delete'){ // Elimina
                            if( $entity->delete() ){
                                $deactivatable->delete();
                            }
                        } else { // Cambia el valor de un parametro, por lo general status = 0 desactivar
                            $param = $deactivatable->param;
                            $entity->$param = $deactivatable->value;
                            if( $entity->save() ){
                                $deactivatable->delete();
                            }
                        }
                    }
                }

            }

        });
    }
}
