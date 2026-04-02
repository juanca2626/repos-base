<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class TimeReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reminders')->insert([
            [
                'id' => 1,
                'title' => 'Información de PAXS',
                'content' => 'paxs',
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 2,
                'title' => 'Información de Vuelos',
                'content' => 'vuelos',
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ]);

        DB::table('reminder_types')->insert([
            [
                'id' => 1,
                'title' => 'Email',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 2,
                'title' => 'WhatsApp',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 3,
                'title' => 'Notificación Push',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ]);

        DB::table('category_reminders')->insert([
            [
                'id' => 1,
                'title' => 'FIT',
                'reminder_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 2,
                'title' => 'GRUPOS',
                'reminder_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 3,
                'title' => 'FIT',
                'reminder_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 4,
                'title' => 'GRUPOS',
                'reminder_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ]);

        DB::table('time_reminders')->insert([
            [
                'id' => 1,
                'category_id' => 1,
                'time' => '5',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'time' => '7',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'time' => '15',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 4,
                'category_id' => 1,
                'time' => '30',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 5,
                'category_id' => 2,
                'time' => '3',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 6,
                'category_id' => 2,
                'time' => '5',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 7,
                'category_id' => 2,
                'time' => '7',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 8,
                'category_id' => 2,
                'time' => '15',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 9,
                'category_id' => 2,
                'time' => '30',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 10,
                'category_id' => 2,
                'time' => '45',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 11,
                'category_id' => 3,
                'time' => '5',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 12,
                'category_id' => 3,
                'time' => '7',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 13,
                'category_id' => 3,
                'time' => '15',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 14,
                'category_id' => 3,
                'time' => '30',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 15,
                'category_id' => 4,
                'time' => '3',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 16,
                'category_id' => 4,
                'time' => '5',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 17,
                'category_id' => 4,
                'time' => '7',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 18,
                'category_id' => 4,
                'time' => '15',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 19,
                'category_id' => 4,
                'time' => '30',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 20,
                'category_id' => 4,
                'time' => '45',
                'format' => 'days',
                'reminder_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
        ]);
    }
}
