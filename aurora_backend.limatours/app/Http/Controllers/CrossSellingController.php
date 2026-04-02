<?php

namespace App\Http\Controllers;

use App\Service;
use App\CrossSelling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CrossSellingController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:crossselling.read')->only('index');
        $this->middleware('permission:crossselling.create')->only('store');
        $this->middleware('permission:crossselling.update')->only('update');
        $this->middleware('permission:crossselling.delete')->only('delete');
    }

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $hotel_id = $request->input('hotel_id');

        $cross_selling_frontend = [];

        $cross_selling_database = CrossSelling::select(['id', 'hotel_id', 'service_id'])->
        whereHas('service', function ($query) use ($querySearch) {
            $query->where('name', 'like', '%' . $querySearch . '%');
        })->where('hotel_id', $hotel_id);

        $count = $cross_selling_database->count();

        if ($paging === 1) {
            $cross_selling_database = $cross_selling_database->take($limit)->get();
        } else {
            $cross_selling_database = $cross_selling_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {

            for ($j = 0; $j < $cross_selling_database->count(); $j++) {
                $cross_selling_frontend[$j]["cross_selling_id"] = $cross_selling_database[$j]["id"];
                $cross_selling_frontend[$j]["service_id"] = $cross_selling_database[$j]["service_id"];
                $cross_selling_frontend[$j]["name"] = $cross_selling_database[$j]["service"]["name"];
                $cross_selling_frontend[$j]["hotel_id"] = $cross_selling_database[$j]["hotel_id"];
                $cross_selling_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $cross_selling_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function store(Request $request)
    {
        $cross_selling = new CrossSelling();
        $cross_selling->service_id = $request->input('service_id');
        $cross_selling->hotel_id = $request->input('hotel_id');
        $cross_selling->save();

        return Response::json(['success' => true, 'cross_selling_id' => $cross_selling->id]);
    }

    public function storeAll(Request $request)
    {
        $hotel_id = $request->input('hotel_id');

        $cross_selling_database = CrossSelling::select(['service_id'])->where('hotel_id', $hotel_id);

        $services_database = Service::select(['id','name']);

        if ($cross_selling_database->count() > 0) {
            $services_database->whereNotIn('id', $cross_selling_database);
        }

        $services_database = $services_database->get();

        $services_transaction_save = [];

        date_default_timezone_set("America/Lima");

        foreach ($services_database as $key => $service) {
            $services_transaction_save[$key] = [
                'service_id' => $service['id'],
                'hotel_id' => $hotel_id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
        }

        DB::transaction(function () use ($services_transaction_save) {

            foreach ($services_transaction_save as $hotel) {

                DB::table('cross_sellings')->insert($hotel);
            }
        });
        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $cross_selling = CrossSelling::find($request->input('cross_selling_id'));
        $cross_selling->delete();

        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {
        $hotel_id = $request->input('hotel_id');

        DB::transaction(function () use ($hotel_id) {

            CrossSelling::where('hotel_id', $hotel_id)->delete();
        });
        return Response::json(['success' => true]);
    }
}
