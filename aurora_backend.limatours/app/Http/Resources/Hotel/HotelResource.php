<?php

namespace App\Http\Resources\Hotel;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Services\Traits\ClientTrait;

class HotelResource extends JsonResource
{
    use ClientTrait;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $hotel_description = "";
        $hotel_address = "";
        $hotel_logo = "";
        $amenities = [];
        $hotel_gallery = [];
        $rooms = [];

        //cargar traducciones
        foreach ($this["translations"] as $translation) {
            if ($translation["slug"] === "hotel_address") {
                $hotel_address = $translation["value"];
            }
            if ($translation["slug"] === "hotel_description") {
                $hotel_description = $translation["value"];
            }
        }

        //cargar galeria de hotel
        foreach ($this["galeries"] as $image) {
            if ($image["slug"] === "hotel_logo") {
                $hotel_logo = url('/') . '/images/' . $image["url"];
            }
            if ($image["slug"] === "hotel_gallery") {
                $find_cloudinary = strpos($image["url"], "cloudinary");
                if (!$find_cloudinary) {
                    array_push($hotel_gallery, url('/') . '/images/' . $image["url"]);
                } else {
                    array_push($hotel_gallery, $image["url"]);
                }
            }
        }

        //cargar amenities de hotel
        if (count($this["amenity"]) > 0) {
            foreach ($this["amenity"] as $amenity) {
                // dd($amenity);
                array_push($amenities,
                    [
                        "name" => $amenity["translations"][0]["value"],
                        "image" => count($amenity["galeries"]) > 0 ? url('/') . '/images/' . $amenity["galeries"][0]["url"] : ''
                    ]);


            }
        }

        //cargar habitaciones de hotel
        foreach ($this["rooms"] as $room) {
            $room_name = "";
            $room_description = "";
            $room_gallery = [];
            $rates = [];
            //cargar traducciones de habitacion
            foreach ($room["translations"] as $translation) {
                if ($translation["slug"] == "room_name") {
                    $room_name = $translation["value"];
                }
                if ($translation["slug"] == "room_description") {
                    $room_description = $translation["value"];
                }
            }
            //cargar galeria de habitacion
            foreach ($room["galeries"] as $image) {

                $find_cloudinary = strpos($image["url"], "cloudinary");
                if (!$find_cloudinary) {
                    array_push($room_gallery, url('/') . '/images/' . $image["url"]);
                } else {
                    array_push($room_gallery, $image["url"]);
                }


            }

            array_push($rooms, [
                'room_id' => $room["id"],
                'room_type' => $room['room_type']['translations'][0]['value'],
                'occupation' => $room['room_type']['occupation'],
                'name' => $room_name,
                'description' => $room_description,
                'gallery' => $room_gallery,
                'max_capacity' => $room["max_capacity"],
                'max_adults' => $room["max_adults"],
                'max_child' => $room["max_child"],
                'rates' => $rates,
            ]);
        }

        return  [
            "id" => $this["id"],
            "name" => $this["name"],
            "stars" => $this["stars"],
            "web_site" => $this["web_site"],
            "country_code" => $this["country"]["iso"],
            "country" => $this["country"]["translations"][0]["value"],
            "state_code" => $this["state"]["iso"],
            "state" => $this["state"]["translations"][0]["value"],
            "city_code" => $this["city"]["id"],
            "city" => $this["city"]["translations"][0]["value"],
            "district" => isset($this["district"]["translations"]) ? $this["district"]["translations"][0]["value"] : '',
            "zone" => isset($this["zone"]["translations"]) ? $this["zone"]["translations"][0]["value"] : '',
            "description" => $hotel_description,
            "address" => $hotel_address,
            "chain" => $this["chain"]["name"],
            "logo" => $hotel_logo,
            "category" => $this["stars"],
            "type" => $this["hoteltype"]["translations"][0]["value"],
            "class" => $this["typeclass"]["translations"][0]["value"],
            "color_class" => $this["typeclass"]["color"],
            "coordinates" => [
                'latitude' => $this["latitude"],
                'longitude' => $this["longitude"],
            ],
            "popularity" => $this["preferential"],
            "favorite" => $this->checkHotelFavorite($this["id"]),
            "checkIn" => $this["check_in_time"],
            "checkOut" => $this["check_out_time"],
            "amenities" => $amenities,
            "galleries" => $hotel_gallery,
            "rooms" => $rooms,
            "best_option_taken" => false,
            "best_option_cart_items_id" => [],
            "alerts" => $this["alerts"],
        ];







    }

}
