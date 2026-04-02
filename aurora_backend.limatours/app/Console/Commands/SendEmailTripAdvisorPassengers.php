<?php

namespace App\Console\Commands;

use App\ClientMailing;
use App\EquivalenceService;
use App\File;
use App\FileService;
use App\Http\Stella\StellaService;
use App\Mail\SendReviewTripAdvisor;
use App\MasterService;
use App\ServiceTranslation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SendEmailTripAdvisorPassengers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_email:trip_advisor_passengers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email Trip Advisor Passengers';

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
        $stella_service = new StellaService();
        $services = [];

        $service_translations = ServiceTranslation::select('service_id','name','link_trip_advisor','language_id')
            ->where('link_trip_advisor','!=',null)->with(['services' => function ($query) {
                $query->select('id','aurora_code', 'name');
            }])->get();

        //llenar arreglo con codigos de servicios
        foreach ($service_translations as $service_translation){
            if (!$this->checkExistCode($service_translation["services"]["aurora_code"],$services)){

                array_push($services,[
                    "service_id"=>$service_translation["service_id"],
                    "code"=>$service_translation["services"]["aurora_code"],
                    "new_code"=>null,
                    "links"=>[]
                ]);
            }
        }
        //llenar con los links y el nombre del servicio de acuerdo al idioma
        foreach ($services as $index_service=>$service){

            foreach ($service_translations as $service_translation){

                if ($service_translation["services"]["aurora_code"] == $service["code"]){

                    array_push($services[$index_service]["links"],[
                        "language_id"=>$service_translation["language_id"],
                        "name"=>$service_translation["name"],
                        "link"=>$service_translation["link_trip_advisor"]
                    ]);
                }
            }
        }
        $today_minus_one_day = Carbon::now()->subDay()->format('Y-m-d');

        //Validar Codigos de Servicio
        $file_ids = File::select('id')
            ->where('date_in','>=','2022-01-01')
            ->where('date_out','<=',$today_minus_one_day)
            ->get()->toArray();

        foreach ($services as $index_service => $service){

            $file_services = FileService::select('code')->whereIn('file_id',$file_ids)->where('code',$service["code"])->get();

            if ($file_services->count() == 0){
                $equivalence_services = EquivalenceService::select('service_id','master_service_id')->where('service_id',$service["service_id"])->get();

                $master_service = MasterService::select('id','code')->where('id',$equivalence_services[0]["master_service_id"])->first();

                if ($equivalence_services->count() == 1){

                    $services[$index_service]["new_code"] = $master_service["code"];
                }

                if ($equivalence_services->count() > 1){

                    $service_id = $equivalence_services[0]["service_id"];

                    $validate_equivalence = true;

                    foreach ($equivalence_services as $equivalence_service){
                        if ($service_id != $equivalence_service["service_id"]){
                            $validate_equivalence = false;
                        }
                    }

                    if ($validate_equivalence){

                        $services[$index_service]["new_code"] = $master_service->code;
                    }

                }

            }

        }
        //end validar codigos de servicio

        $files = File::select('id','file_number','date_in','date_out','lang','client_id')
            ->where('date_in','>=','2023-01-01')
            ->where('date_out','<=',$today_minus_one_day)
            ->where('processed_trip_advisor',0)
            ->with('client')
            ->with(['services' => function ($query) {
                $query->select('file_id','code');
            }])->get();

        foreach ($files as $file){
            $client_mailing = ClientMailing::where('clients_id',$file["client_id"])->where('status',1)->first();

            if ($client_mailing != null){
                foreach ($services as $service){

                    if ($service["new_code"]!= null){
                        if ($this->checkExistsService($service["new_code"],$file["services"])){
                            //traer pasajeros del file
                            $passengers = $stella_service->passengers_list($file["file_number"]);


                            if (count($passengers->data) > 0){
                                foreach ($passengers->data as $passenger){
                                    if ($passenger->correo != null){
                                        if ($file["lang"] == "EN" ){
                                            $message = trans('trip_advisor.message',[],'en');
                                            $message2 = trans('trip_advisor.message2',[],'en');
                                            $message3 = trans('trip_advisor.message3',[],'en');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==2){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "ES" ){
                                            $message = trans('trip_advisor.message',[],'es');
                                            $message2 = trans('trip_advisor.message2',[],'es');
                                            $message3 = trans('trip_advisor.message3',[],'es');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==1){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "PT" ){
                                            $message = trans('trip_advisor.message',[],'pt');
                                            $message2 = trans('trip_advisor.message2',[],'pt');
                                            $message3 = trans('trip_advisor.message3',[],'pt');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==3){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "IT" ){
                                            $message = trans('trip_advisor.message',[],'it');
                                            $message2 = trans('trip_advisor.message2',[],'it');
                                            $message3 = trans('trip_advisor.message3',[],'it');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==2){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "FR" ){
                                            $message = trans('trip_advisor.message',[],'fr');
                                            $message2 = trans('trip_advisor.message2',[],'fr');
                                            $message3 = trans('trip_advisor.message3',[],'fr');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==2){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }

                                        $data = [
                                            "file" => $file,
                                            "message"=>$message,
                                            "message2"=>$message2,
                                            "message3"=>$message3,
                                            "service"=>$service_name,
                                            "link"=>$link_trip,
                                            "email_passenger"=>$passenger->correo
                                        ];
                                        $validator = Validator::make(['email'=>$passenger->correo],[
                                            'email'=>'email'
                                        ]);
                                        if ($validator->fails()){
//                                            Log::debug("no se pudo enviar el email a este correo:". $passenger->correo);
                                        }else{

                                            Mail::to($passenger->correo)->send(new SendReviewTripAdvisor($data));
                                        }
                                    }else{
//                                        Log::debug("Pasajero no tiene email".json_encode($passenger));
                                    }
                                }
                            }else{
//                                Log::debug("Este file:".$file["file_number"]. " no tiene pasajeros");
                            }
                        }
                        else{
//                            Log::debug("no se envia mail de este servicio :".$service["new_code"] );
                        }
                    }
                    else{
                        if ($this->checkExistsService($service["code"],$file["services"])){
                            //traer pasajeros del file
                            $passengers = $stella_service->passengers_list($file["file_number"]);

                            if (count($passengers->data) > 0){
                                foreach ($passengers->data as $passenger){
                                    if ($passenger->correo != null){
                                        if ($file["lang"] == "EN" ){
                                            $message = trans('trip_advisor.message',[],'en');
                                            $message2 = trans('trip_advisor.message2',[],'en');
                                            $message3 = trans('trip_advisor.message3',[],'en');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==2){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "ES" ){
                                            $message = trans('trip_advisor.message',[],'es');
                                            $message2 = trans('trip_advisor.message2',[],'es');
                                            $message3 = trans('trip_advisor.message3',[],'es');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==1){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "PT" ){
                                            $message = trans('trip_advisor.message',[],'pt');
                                            $message2 = trans('trip_advisor.message2',[],'pt');
                                            $message3 = trans('trip_advisor.message3',[],'pt');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==3){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "IT" ){
                                            $message = trans('trip_advisor.message',[],'it');
                                            $message2 = trans('trip_advisor.message2',[],'it');
                                            $message3 = trans('trip_advisor.message3',[],'it');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==2){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }
                                        if ($file["lang"] == "FR" ){
                                            $message = trans('trip_advisor.message',[],'fr');
                                            $message2 = trans('trip_advisor.message2',[],'fr');
                                            $message3 = trans('trip_advisor.message3',[],'fr');

                                            foreach ($service["links"] as $link){
                                                if ($link["language_id"]==2){
                                                    $service_name = "{$link['name']}";
                                                    $link_trip = $link["link"];
                                                }
                                            }
                                        }

                                        $data = [
                                            "file" => $file,
                                            "message"=>$message,
                                            "message2"=>$message2,
                                            "message3"=>$message3,
                                            "service"=>$service_name,
                                            "link"=>$link_trip,
                                            "email_passenger"=>$passenger->correo
                                        ];
                                        $validator = Validator::make(['email'=>$passenger->correo],[
                                            'email'=>'email'
                                        ]);
                                        if ($validator->fails()){
                                        }else{

                                            Mail::to($passenger->correo)->send(new SendReviewTripAdvisor($data));
                                        }
                                    }else{
//                                        Log::debug("Pasajero no tiene email".json_encode($passenger));
                                    }
                                }
                            }else{
//                                Log::debug("Este file:".$file["file_number"]. " no tiene pasajeros");
                            }
                        }else{
//                            Log::debug("no se envia mail de este servicio :".$service["code"] );
                        }
                    }
                }
            }else{

//                Log::debug("Este cliente :". $file["client_id"] . " no tiene activado el envio de mensajes de MASI");
            }


            $file_update = File::find($file["id"]);
            $file_update->processed_trip_advisor = 1;
            $file_update->save();
        }
    }

    private function checkExistCode($code,$services){
        foreach ($services as $service){
            if ($service["code"] == $code){
                return true;
            }
        }
        return false;
    }

    private function checkExistsService($service_code, $services){

        foreach ($services as $service){
            if ($service_code == $service["code"]){
                return true;
            }
        }
        return false;
    }
}
