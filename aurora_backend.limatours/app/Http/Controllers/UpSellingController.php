<?php

namespace App\Http\Controllers;

use App\Hotel;
use App\Http\Traits\ManageSearchHotel;
use App\UpSelling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class UpSellingController extends Controller
{
    use ManageSearchHotel;

    public function __construct()
    {
        $this->middleware('permission:upselling.read')->only('index');
        $this->middleware('permission:upselling.create')->only('store');
        $this->middleware('permission:upselling.update')->only('update');
        $this->middleware('permission:upselling.delete')->only('delete');
    }

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $hotel_id = $request->input('hotel_id');

        $up_selling_frontend = [];

        $up_selling_database = UpSelling::select(['id', 'hotel_id', 'hotel_child_id'])->
        whereHas('hotel_child', function ($query) use ($querySearch) {
            $query->where('name', 'like', '%' . $querySearch . '%');
        })->where('hotel_id', $hotel_id);

        $count = $up_selling_database->count();

        if ($paging === 1) {
            $up_selling_database = $up_selling_database->take($limit)->get();
        } else {
            $up_selling_database = $up_selling_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {

            for ($j = 0; $j < count($up_selling_database); $j++) {
                $up_selling_frontend[$j]["up_selling_id"] = $up_selling_database[$j]["id"];
                $up_selling_frontend[$j]["hotel_child_id"] = $up_selling_database[$j]["hotel_child_id"];
                $up_selling_frontend[$j]["name"] = $up_selling_database[$j]["hotel_child"]["name"];
                $up_selling_frontend[$j]["hotel_id"] = $up_selling_database[$j]["hotel_id"];
                $up_selling_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $up_selling_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function store(Request $request)
    {
        $up_selling = new UpSelling();
        $up_selling->hotel_child_id = $request->input('hotel_child_id');
        $up_selling->hotel_id = $request->input('hotel_id');
        $up_selling->save();

        return Response::json(['success' => true, 'up_selling_id' => $up_selling->id]);
    }

    public function storeAll(Request $request)
    {
        $hotel_id = $request->input('hotel_id');

        $up_selling_database = UpSelling::select(['hotel_child_id'])->where('hotel_id', $hotel_id);

        $hotels_database = Hotel::select(['id']);

        if ($up_selling_database->count() > 0) {
            $hotels_database->whereNotIn('id', $up_selling_database);
        }

        $hotels_database = $hotels_database->get();

        $hotels_transaction_save = [];

        date_default_timezone_set("America/Lima");

        foreach ($hotels_database as $key => $hotel) {
            $hotels_transaction_save[$key] = [
                'hotel_child_id' => $hotel['id'],
                'hotel_id' => $hotel_id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
        }

        DB::transaction(function () use ($hotels_transaction_save) {

            foreach ($hotels_transaction_save as $hotel) {

                DB::table('up_sellings')->insert($hotel);
            }
        });
        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $up_selling = UpSelling::find($request->input('up_selling_id'));
        $up_selling->delete();

        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {
        $hotel_id = $request->input('hotel_id');

        DB::transaction(function () use ($hotel_id) {

            UpSelling::where('hotel_id', $hotel_id)->delete();
        });
        return Response::json(['success' => true]);
    }

    public function upsellingFrontend(Request $request)
    {
        $token_search = $request->post('token_search_frontend');
        $hotel_id = $request->post('hotel_id');
        $hotels_upselling =UpSelling::where('hotel_id', $hotel_id)->pluck('hotel_child_id')->toArray();

        array_unshift($hotels_upselling,$hotel_id);
        $hotels = $this->getHotelsByTokenSearch($token_search);

        $hotels[0]["city"]["search_parameters"]["hotels_id"] = $hotels_upselling;

        return \response()->json($hotels[0]["city"]["search_parameters"],200);
    }
}
