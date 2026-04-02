<?php

namespace App\Console\Commands;

use App\Bag;
use App\ClientRatePlan;
use App\HotelUser;
use App\Inventory;
use App\InventoryBag;
use App\RatesPlansRooms;
use App\Room;
use App\User;
use App\Hotel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NotificationInventaryRoomHotels extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check';

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

    private function checkExists($inventories_exists_group, $rate_plan_rooms_id, $day)
    {
        foreach ($inventories_exists_group as $key => $inventory_of_rate) {
            if ($key == $rate_plan_rooms_id) {
                foreach ($inventory_of_rate as $inventory_day) {
                    if ($inventory_day["day"] == $day) {
                        return $inventory_day;
                    }
                }
            }
        }
    }

    private function checkExistsBag($inventories_bag_exists_group, $bag_room_id, $day)
    {
        foreach ($inventories_bag_exists_group as $key => $inventory_of_rate) {
            if ($key == $bag_room_id) {
                foreach ($inventory_of_rate as $inventory_day) {
                    if ($inventory_day["day"] == $day) {
                        return $inventory_day;
                    }
                }
            }
        }
    }

    private function checkSelectDay($days, $date_of_week)
    {
        foreach ($days as $day) {
            if ($day["day"] === $date_of_week && $day["selected"] === true) {
                return true;
            }
        }
    }

    private function verifyExistInventoryBagInDate($inventories_bag, $date)
    {
        foreach ($inventories_bag as $inventory) {
            if ($inventory["date"] === $date) {
                return $inventory;
            }
        }
    }

    private function verifyExistInventoryInDate($inventories, $date)
    {
        foreach ($inventories as $inventory) {
            if ($inventory["date"] === $date) {
                return $inventory;
            }
        }
    }

    public function handle()
    {
        $lang = "es";
        $allotment = 1;
        $hotel_id = "";
        $room_id = "";
        $rate_plan_id = "";
        $client_id = "";
        $client_rate_plans = [];
        $client_rate_plans = ClientRatePlan::select('rate_plan_id')->where('client_id', $client_id)->get();

        $inventories = [];

        $hotels_id = Hotel::where('status', '=', 1)->pluck('id');

        $rate_plan_rooms = RatesPlansRooms::whereHas('room', function ($query) use ($hotels_id, $room_id) {
            $query->whereIn('hotel_id', $hotels_id);
            $query->where('state', '1');
            $query->where('inventory', 1);
            if ($room_id) {
                $query->where('room_id', $room_id);
            }
        })->with(['room.hotel' => function ($query) {
            $query->where('status', '=', 1);
        },
            'room.translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->whereHas('rate_plan', function ($query) use ($allotment, $client_rate_plans, $client_id) {
            if ($allotment == 1 && $client_id != "") {
                $query->whereIn('id', $client_rate_plans);
            }
            $query->where('status',1);
            $query->where('allotment', 1);
        })->with('rate_plan')->where('bag', 0)->where('channel_id', 1)->where('status', 1);

        if ($rate_plan_id) {
            $rate_plan_rooms = $rate_plan_rooms->where('rates_plans_id', $rate_plan_id);
        }
        $rate_plan_rooms = $rate_plan_rooms->get();
        $bags = [];

        $room_ids = Room::where('state', 1)->where('inventory', 1)->pluck('id');
        if ($allotment === 0 && $rate_plan_id == null) {
            $bags = Bag::where('status', 1)
                ->with([
                    'bag_rooms' => function ($query) use ($hotel_id, $room_id, $room_ids, $lang) {

                        $query->whereIn('room_id', $room_ids);

                        $query->with([
                            'room.hotel',
                            'room.translations' => function ($query) use ($lang) {
                                $query->where('type', 'room');
                                $query->whereHas('language', function ($q) use ($lang) {
                                    $q->where('iso', $lang);
                                });
                            }
                        ]);
                    }
                ])->get();

        }

        $year = date("Y"); $month = date("m");

        $fecha_carbon = Carbon::parse($year."-".$month."-01");

        $days = cal_days_in_month(CAL_GREGORIAN, $fecha_carbon->month, $fecha_carbon->year);

        $inventories_exists = Inventory::whereBetween('date', [
            $year."-".$month."-01",
            $year."-".$month."-".$days
        ])->get();

        $inventories_bags_exists = InventoryBag::whereBetween('date', [
            $year."-".$month."-01",
            $year."-".$month."-".$days
        ])->get();

        $inventories_exists_group = $inventories_exists->groupBy('rate_plan_rooms_id');
        $inventories_bag_exists_group = $inventories_bags_exists->groupBy('bag_room_id');

        $index_inventory = 0;

        // echo json_encode($inventories_bag_exists_group);
        // die('..');
        if (count($bags) > 0) {
            for ($k = 0; $k < $bags->count(); $k++) {
                if (count($bags[$k]["bag_rooms"]) > 0) {
                    for ($l = 0; $l < count($bags[$k]["bag_rooms"]); $l++) {

                        // if($bags[$k]["bag_rooms"][$l]["id"] != '170')continue;

                        $inventories[$index_inventory] = [
                            "rate_name" => $bags[$k]["bag_rooms"][$l]["room"]["translations"][0]["value"].' '.$bags[$k]["name"]
                        ];
                        for ($m = 1; $m <= $days; $m++) {
                            $inventory_day_exists = $this->checkExistsBag(
                                $inventories_bag_exists_group,
                                $bags[$k]["bag_rooms"][$l]["id"],
                                $m
                            );
                            if ($inventory_day_exists === null) {
                                $inventories[$index_inventory]["inventory"][$m] = [
                                    "hotel" => $bags[$k]["bag_rooms"][$l]["room"]["hotel"],
                                    "day" => $m,
                                    "room_id" => $bags[$k]["bag_rooms"][$l]["room_id"],
                                    "bag_room_id" => $bags[$k]["bag_rooms"][$l]["id"],
                                    "rate_plan_rooms_id" => "",
                                    "id" => "",
                                    "date" => $year."-".substr('00'.$month,
                                            -2)."-".substr('00'.$m, -2),
                                    "inventory_num" => '',
                                    "selected" => false,
                                    "class_selected" => false,
                                    "locked" => false,
                                    "class_locked" => false,
                                    "class_intermediate" => false,
                                    "class_normal" => true,
                                ];
                            } else {
                                $inventories[$index_inventory]["inventory"][$m] = [
                                    "hotel" => $bags[$k]["bag_rooms"][$l]["room"]["hotel"],
                                    "day" => $inventory_day_exists["day"],
                                    "room_id" => $bags[$k]["bag_rooms"][$l]["room_id"],
                                    "bag_room_id" => $bags[$k]["bag_rooms"][$l]["id"],
                                    "rate_plan_rooms_id" => "",
                                    "id" => $inventory_day_exists["id"],
                                    "date" => $inventory_day_exists["date"],
                                    "inventory_num" => $inventory_day_exists["inventory_num"] - $inventory_day_exists["total_booking"],
                                    "selected" => false,
                                    "class_selected" => false,
                                    "locked" => $inventory_day_exists["locked"],
                                    "class_locked" => false,
                                    "class_intermediate" => false,
                                    "class_normal" => true,
                                ];
                                if ($inventory_day_exists["locked"] === 1) {
                                    $inventories[$index_inventory]["inventory"][$m]["class_locked"] = true;
                                    $inventories[$index_inventory]["inventory"][$m]["class_normal"] = false;
                                }
                            }
                        }
                        $index_inventory++;
                    }
                }
            }
        }

        if ($rate_plan_rooms->count() > 0) {
            for ($j = 0; $j < $rate_plan_rooms->count(); $j++) {
                $inventories[$index_inventory] = [
                    "rate_name" => $rate_plan_rooms[$j]["room"]["translations"][0]["value"].' '.$rate_plan_rooms[$j]["rate_plan"]["name"],
                    "rate_plan_rooms_id" => $rate_plan_rooms[$j]["id"]
                ];
                for ($i = 1; $i <= $days; $i++) {
                    $inventory_day_exists = $this->checkExists(
                        $inventories_exists_group,
                        $rate_plan_rooms[$j]["id"],
                        $i,
                        $rate_plan_rooms[$j]["room_id"]
                    );
                    if ($inventory_day_exists === null) {

                        $inventories[$index_inventory]["inventory"][$i] = [
                            "hotel" => $rate_plan_rooms[$j]["room"]["hotel"],
                            "day" => $i,
                            "bag_room_id" => "",
                            "room_id" => $rate_plan_rooms[$j]["room_id"],
                            "rate_plan_rooms_id" => $rate_plan_rooms[$j]["id"],
                            "id" => "",
                            "date" => $year."-".substr('00'.$month,
                                    -2)."-".substr('00'.$i, -2),
                            "inventory_num" => '',
                            "selected" => false,
                            "class_selected" => false,
                            "locked" => false,
                            "class_locked" => false,
                            "class_intermediate" => false,
                            "class_normal" => true,
                        ];

                    } else {
                        $inventories[$index_inventory]["inventory"][$i] = [
                            "hotel" => $rate_plan_rooms[$j]["room"]["hotel"],
                            "day" => $inventory_day_exists["day"],
                            "bag_room_id" => "",
                            "room_id" => $rate_plan_rooms[$j]["room_id"],
                            "rate_plan_rooms_id" => $inventory_day_exists["rate_plan_rooms_id"],
                            "id" => $inventory_day_exists["id"],
                            "date" => $inventory_day_exists["date"],
                            "inventory_num" => $inventory_day_exists["inventory_num"] - $inventory_day_exists["total_booking"],
                            "selected" => false,
                            "class_selected" => false,
                            "locked" => $inventory_day_exists["locked"],
                            "class_locked" => false,
                            "class_intermediate" => false,
                            "class_normal" => true,
                        ];

                        if ($inventory_day_exists["locked"] === 1) {
                            $inventories[$index_inventory]["inventory"][$i]["class_locked"] = true;
                            $inventories[$index_inventory]["inventory"][$i]["class_normal"] = false;
                        }
                    }
                }
                $index_inventory++;
            }
        }

        $langs = ['', 'es', 'en', 'pt', 'it']; $inventories_send = []; $rates_send = [];

        foreach($inventories as $key => $value)
        {
            if(is_array(@$value['inventory']) AND count(@$value['inventory']) > 0)
            {
                foreach($value['inventory'] as $k => $v)
                {
                    $rates_send[$v['hotel']['id']] = (isset($rates_send[$v['hotel']['id']])) ? $rates_send[$v['hotel']['id']] : [];

                    $hotel = Hotel::where('id', '=', $v['hotel']['id'])->where('status', '=', 1)->first();

                    if($hotel != '' AND $hotel != NULL)
                    {
                        // $v['inventory_num'] != ''
                        if(@$v['locked'] !== 1 AND !empty($v['inventory_num']) AND $v['inventory_num'] < 1 AND $v['day'] >= (int) date("d"))
                        {
                            $value['hotel'] = $hotel;
                            $rates_send[$v['hotel']['id']][] = $value['rate_name'];
                            $inventories_send[$v['hotel']['id']] = $value; break;
                        }
                    }
                }
            }
        }

        $response = [];

        foreach($inventories_send as $key => $value)
        {
            $hotel = $value['hotel'];

            $contact = HotelUser::with(['user'])
                ->where('hotel_id', $hotel['id'])
                ->orderBy('id', 'ASC')->first();

            $lang = ($hotel['language_id'] > 0) ? $hotel['language_id'] : 1;

            if(!empty($contact->user))
            {
                $data = [
                    'rate_name' => (array) @$rates_send[$hotel['id']],
                    'hotel' => $hotel,
                    'email' => $contact->user->email
                ];

                $response[] = [
                    'hotel' => $hotel['id'],
                    'email' => $contact->user->email,
                ];
            }

            if(!empty($contact->user->email))
            {
                $mail = Mail::to($contact->user->email);
                $mail->bcc('neg@limatours.com.pe');

                $mail->send(new \App\Mail\NotificationInventory($langs[$lang], $data));
            }
        }

        // dd($response);
    }
}
