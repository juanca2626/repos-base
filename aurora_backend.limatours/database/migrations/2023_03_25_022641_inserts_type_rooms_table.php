<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Traits\Translations;
use App\TypeRoom;
use App\RoomType;

class InsertsTypeRoomsTable extends Migration
{
    use Translations;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $type_room = new TypeRoom();
        $type_room->save();
        $standard = $type_room->id;

        $translation = [
            1 => [
                'id' => '',
                'type_room_name' => "Estándar"
            ],
            2 => [
                'id' => '',
                'type_room_name' => "Standard"
            ],
            3 => [
                'id' => '',
                'type_room_name' => "Padrão"
            ],
            4 => [
                'id' => '',
                'type_room_name' => "Standard"
            ]
        ];

        $this->saveTranslation($translation, 'typeroom', $standard);


        $type_room = new TypeRoom();
        $type_room->save();
        $superior = $type_room->id;

        $translation = [
            1 => [
                'id' => '',
                'type_room_name' => "Superior"
            ],
            2 => [
                'id' => '',
                'type_room_name' => "Superior"
            ],
            3 => [
                'id' => '',
                'type_room_name' => "Superior"
            ],
            4 => [
                'id' => '',
                'type_room_name' => "Superiore"
            ]
        ];

        $this->saveTranslation($translation, 'typeroom', $superior);


        $type_room = new TypeRoom();
        $type_room->save();
        $junior_suite = $type_room->id;

        $translation = [
            1 => [
                'id' => '',
                'type_room_name' => "Junior Suite"
            ],
            2 => [
                'id' => '',
                'type_room_name' => "Junior Suite"
            ],
            3 => [
                'id' => '',
                'type_room_name' => "Suíte Júnior"
            ],
            4 => [
                'id' => '',
                'type_room_name' => "Suite júnior"
            ]
        ];

        $this->saveTranslation($translation, 'typeroom', $junior_suite);   
        

        $type_room = new TypeRoom();
        $type_room->save();
        $suite = $type_room->id;

        $translation = [
            1 => [
                'id' => '',
                'type_room_name' => "Suite"
            ],
            2 => [
                'id' => '',
                'type_room_name' => "Suite"
            ],
            3 => [
                'id' => '',
                'type_room_name' => "Suíte"
            ],
            4 => [
                'id' => '',
                'type_room_name' => "Suíte"
            ]
        ];

        $this->saveTranslation($translation, 'typeroom', $suite);          



        $room_type = RoomType::find(1);
        $room_type->type_room_id = $standard;
        $room_type->save();

        $room_type = RoomType::find(2);
        $room_type->type_room_id = $standard;
        $room_type->save();        

        $room_type = RoomType::find(3);
        $room_type->type_room_id = $standard;
        $room_type->save();

        $room_type = RoomType::find(4);
        $room_type->type_room_id = $superior;
        $room_type->save();

        $room_type = RoomType::find(5);
        $room_type->type_room_id = $superior;
        $room_type->save();        

        $room_type = RoomType::find(6);
        $room_type->type_room_id = $superior;
        $room_type->save();


        $room_type = RoomType::find(7);
        $room_type->type_room_id = $junior_suite;
        $room_type->save();

        $room_type = RoomType::find(8);
        $room_type->type_room_id = $junior_suite;
        $room_type->save();        

        $room_type = RoomType::find(9);
        $room_type->type_room_id = $junior_suite;
        $room_type->save();
        
        $room_type = RoomType::find(10);
        $room_type->type_room_id = $suite;
        $room_type->save();

        $room_type = RoomType::find(11);
        $room_type->type_room_id = $suite;
        $room_type->save();        

        $room_type = RoomType::find(12);
        $room_type->type_room_id = $suite;
        $room_type->save();


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
