<?php

use Illuminate\Database\Seeder;
use App\Reminder; use App\TypeReminder;
use App\CategoryReminder; use App\TimeReminder;
use Illuminate\Support\Facades\DB;

class ReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reminder_types')->delete();
        DB::table('time_reminders')->delete();
        DB::table('category_reminders')->delete();
        DB::table('reminders')->delete();

        $types = [
            'email',
            'whatsapp',
            'pushNoti'
        ];

        foreach($types as $key => $value)
        {
            $type = new TypeReminder;
            $type->id = ($key + 1);
            $type->title = $value;
            $type->save();
        }

        $reminders = [
            [
                'title' => 'Información de PAXS',
                'content' => 'paxs',
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'categories' => [
                    [
                        'title' => 'FITS',


                        'times' => [
                            [
                                'time' => '3',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '5',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '7',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '15',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '30',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '45',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                        ]
                    ],
                    [
                        'title' => 'GRUPOS Y SERIES',


                        'times' => [
                            [
                                'time' => '3',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '5',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '7',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '15',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '30',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '45',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                        ]
                    ],
                ],
],
            [
                'title' => 'Información de Vuelos',
                'content' => 'flights',
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'categories' => [
                    [
                        'title' => 'FITS',


                        'times' => [
                            [
                                'time' => '3',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '5',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '7',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '15',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '30',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '45',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                        ]
                    ],
                    [
                        'title' => 'GRUPOS Y SERIES',


                        'times' => [
                            [
                                'time' => '3',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '5',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '7',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '15',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '30',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                            [
                                'time' => '45',
                                'format' => 'days',
                                'reminder_type_id' => 1,
],
                        ]
                    ],
                ],
            ],
        ];

        foreach($reminders as $key => $value)
        {
            $value = (object) $value;

            $reminder = new Reminder;
            $reminder->title = $value->title;
            $reminder->content = $value->content;
            $reminder->status = $value->status;
            $reminder->save();

            foreach($value->categories as $k => $v)
            {
                $v = (object) $v;

                $category = new CategoryReminder;
                $category->reminder_id = $reminder->id;
                $category->title = $v->title;
                $category->save();

                foreach($v->times as $i => $j)
                {
                    $j = (object) $j;

                    $time = new TimeReminder;
                    $time->category_id = $category->id;
                    $time->time = $j->time;
                    $time->format = $j->format;
                    $time->reminder_type_id = $j->reminder_type_id;
                    $time->save();
                }
            }
        }
    }
}
