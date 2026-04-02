<?php

namespace App\Console\Commands;

use App\Galery;
use App\Service;
use Cloudinary\Search;
use Illuminate\Console\Command;
use JD\Cloudder\Facades\Cloudder;

class updateCloudinaryServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:services;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = Sincronizar carpetas de servicios con cloudinary';

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
        $services = Service::where('status',1)->get();

       Cloudder::getCloudinary();

        $search = new Search();

        foreach ($services as $service) {
            $search_all_images_service = $search->expression("(folder=".'services/'.$service["aurora_code"].")")
                ->max_results(50)
                ->with_field('context')
                ->with_field('tags');

            $search_all_images_service = $search_all_images_service->execute();
            //Desactivar todas las imagenes de hoteles existentes anteriores al 14-09-2020
            Galery::where('type','service')->where('object_id',$service["id"])->where('created_at','<','2020-10-13')->update(['state'=>0]);

            $images_service = Galery::where('type','service')->where('object_id',$service["id"])->where('created_at','>=','2020-10-13 00:00:00')->where('state',1)->get();

            foreach ($search_all_images_service["resources"] as $image)
            {
                $check_exists_image_service = false;
                foreach ($images_service as $image_service)
                {
                    if ($image["secure_url"] == $image_service["url"])
                    {
                        $check_exists_image_service = true;
                    }
                }
                if (!$check_exists_image_service)
                {
                    $max_position_image_service = 0;
                    $max_position_image_service =  Galery::where('type','service')->where('slug','service_gallery')->where('object_id',$service["id"])->max('position');
                    $max_position_image_service+=1;
                    $image_service_new = new Galery();
                    $image_service_new->type = 'service';
                    $image_service_new->object_id = $service["id"];
                    $image_service_new->url = $image["secure_url"];
                    $image_service_new->slug = 'service_gallery';
                    $image_service_new->position = $max_position_image_service;
                    $image_service_new->state = 1;
                    $image_service_new->save();
                }
            }
        }


//            foreach ($rooms as $room)
//            {
//               Cloudder::upload(
//                    './public/images/hotel-default.jpg',
//                    null,
//                    array(
//                        "folder" => 'hotels/' . $hotel["channel"][0]["code"] . '/rooms/' . $room["id"],
//                        "public_id" => 'hotel_example'
//                    )
//                )->getResult();
//            }
//        }
    }
}
