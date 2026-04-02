<?php

namespace App\Console\Commands;

use App\Galery;
use App\Hotel;
use App\Http\Controllers\GaleriesController;
use App\Room;
use Cloudinary\Search;
use Illuminate\Console\Command;
use JD\Cloudder\Facades\Cloudder;

class updateCloudinaryHotels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:hotels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar carpetas de hoteles con cloudinary';

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
        $hotels = Hotel::where('status',1)->with(['channel'=>function($query){
            $query->where('channel_id',1);
        }])->get();

        Cloudder::getCloudinary();

        $search = new Search();

        foreach ($hotels as $hotel) {
            $search_all_images_hotel = $search->expression("(folder=".'hotels/'.$hotel["channel"][0]["code"].")")
                ->max_results(50)
                ->with_field('context')
                ->with_field('tags');

            $search_all_images_hotel_resources = $search_all_images_hotel->execute();
            //Desactivar todas las imagenes de hoteles existentes anteriores al 14-09-2020
//            Galery::where('type','hotel')->where('object_id',$hotel["id"])->where('created_at','<','2020-09-14')->update(['state'=>0]);

            $images_hotel = Galery::where('type','hotel')->where('object_id',$hotel["id"])->where('created_at','>=','2020-09-14 00:00:00')->where('state',1)->get();

            foreach ($search_all_images_hotel_resources["resources"] as $image)
            {

                $check_exists_image_hotel = false;
                foreach ($images_hotel as $image_hotel)
                {
                    if ($image["secure_url"] == $image_hotel["url"])
                    {
                        $check_exists_image_hotel = true;
                    }
                }
                if (!$check_exists_image_hotel)
                {
                    $max_position_image_hotel = 0;
                    $max_position_image_hotel =  Galery::where('type','hotel')->where('object_id',$hotel["id"])->max('position');
                    $max_position_image_hotel+=1;
                    $image_hotel_new = new Galery();
                    $image_hotel_new->type = 'hotel';
                    $image_hotel_new->object_id = $hotel["id"];
                    $image_hotel_new->url = $image["secure_url"];
                    $image_hotel_new->slug = 'hotel_gallery';
                    $image_hotel_new->position = $max_position_image_hotel;
                    $image_hotel_new->state = 1;
                    $image_hotel_new->save();
                }
            }

            $rooms = Room::where('hotel_id', $hotel["id"])->where('state', 1)->get();

            foreach ($rooms as $room)
            {
                $search_all_images_room = $search->expression("(folder=".'hotels/'.$hotel["channel"][0]["code"]."/rooms/".$room["id"].")")
                    ->max_results(50)
                    ->with_field('context')
                    ->with_field('tags');

                $search_all_images_room_resources = $search_all_images_room->execute();
                //Desactivar todas las imagenes de las habitaciones existentes anteriores al 14-09-2020
//                Galery::where('type','room')->where('object_id',$room["id"])->where('created_at','<','2020-09-14')->update(['state'=>0]);

                $images_room= Galery::where('type','room')->where('object_id',$room["id"])->where('created_at','>=','2020-09-14 00:00:00')->where('state',1)->get();

                foreach ($search_all_images_room_resources["resources"] as $image)
                {

                    $check_exists_image_room = false;
                    foreach ($images_room as $image_room)
                    {
                        if ($image["secure_url"] == $image_room["url"])
                        {
                            $check_exists_image_room = true;
                        }
                    }
                    if (!$check_exists_image_room)
                    {
                        $max_position_image_room = 0;
                        $max_position_image_room =  Galery::where('type','room')->where('object_id',$room["id"])->max('position');
                        $max_position_image_room+=1;
                        $image_room_new = new Galery();
                        $image_room_new->type = 'room';
                        $image_room_new->object_id = $room["id"];
                        $image_room_new->url = $image["secure_url"];
                        $image_room_new->slug = 'room_gallery';
                        $image_room_new->position = $max_position_image_room;
                        $image_room_new->state = 1;
                        $image_room_new->save();
                    }
                }
            }
        }
    }
}
