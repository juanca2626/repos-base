<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientSeller;
use App\SearchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchServiceController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->user_type_id == 1) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }
        if (Auth::user()->user_type_id == 4) {
            /** @var Client $client */
            $client = auth_user()->clientSellers()
                ->where('clients.status', 1)
                ->wherePivot('status', 1)
                ->first();

            $client_id = $client["id"];
        }

        $search_destinies_paginate = SearchService::where('user_id', Auth::user()->id)
            ->where('client_id', $client_id)->distinct()->orderBy('id', 'desc')->paginate(10);
//        var_export($search_destinies_paginate);die;
        $pagination = [
            "total" => $search_destinies_paginate->total(),
            "per_page" => $search_destinies_paginate->perPage(),
            "from" => 1,
            "to" => $search_destinies_paginate->lastPage(),
            "last_page" => $search_destinies_paginate->lastPage(),
            "current_page" => $search_destinies_paginate->currentPage()
        ];

        $search_destinies_id = $search_destinies_paginate->pluck('token_search');

        $search_destinies = SearchService::where('user_id', Auth::user()->id)
            ->where('client_id', $client_id)->whereIn('token_search', $search_destinies_id)->orderBy('id',
                'desc')->get()->groupBy('token_search');

//        foreach ($search_destinies as $id => $search_destinie) {
//            foreach ($search_destinie as $id2 => $search) {
//                if ($search->origin == "null" or $search->destiny == "null") {
//                    unset($search_destinies[$id]);
//                }
//            }
//        }

        return response()->json(["data" => $search_destinies, "pagination" => $pagination]);

    }


    public function store(Request $request)
    {
        if (Auth::user()->user_type_id == 1) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        if ($request->has('quantity_persons')) {
            $quantity_persons = $request->post('quantity_persons');
        } else {
            $quantity_persons = [
                'adults' => 2,
                'child' => 0,
                'age_childs' => [
                    'age' => 0
                ],
            ];
        }
        $date = Carbon::parse($request->post('date'));
        $quantity_adults = $quantity_persons['adults'];
        $quantity_child = $quantity_persons['child'];
        $faker = \Faker\Factory::create();
        $request->post('date');
        $uuid = $faker->unique()->uuid;
        $search_new = new SearchService();
        $search_new->index_search = 0;
        $search_new->origin = json_encode($request->post('origin'));
        $search_new->destiny = json_encode($request->post('destiny'));
        $search_new->date = $date;
        $search_new->quantity_adults = $quantity_adults;
        $search_new->quantity_child = $quantity_child;
        $search_new->quantity_persons =json_encode($quantity_persons);
        $search_new->type_services = json_encode($request->post('type'));
        $search_new->service_sub_categories = json_encode($request->post('category'));
        $search_new->experiences = json_encode($request->post('experience'));
        $search_new->classifications = json_encode($request->post('classification'));
        $search_new->filter_by_name = $request->post('filter');
        $search_new->user_id = Auth::user()->id;
        $search_new->client_id = $client_id;
        $search_new->token_search = $uuid;
        $search_new->quantity_services = 0;
        $search_new->save();
        return Response($uuid);

    }

    public function getSearchDestiniesByTokenSearch(Request $request)
    {
        if (Auth::user()->user_type_id == 1) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        $token_search = $request->post('token_search');
        $search_destinies = SearchService::where('token_search', $token_search)
            ->where('user_id', Auth::user()->id)
            ->where('client_id', $client_id)
            ->get();
        return $search_destinies;
    }
}
