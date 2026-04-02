<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientSeller;
use App\SearchDestiny;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchDestinyController extends Controller
{
    public function store(Request $request)
    {
        if (Auth::user()->user_type_id == 1) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }
        $faker = \Faker\Factory::create();

        $uuid = $faker->unique()->uuid;

        $destinies = $request->post('destinies');

        foreach ($destinies as $index => $destiny) {
            $destiny_new = new SearchDestiny();
            $destiny_new->index_search = $index;
            $destiny_new->destiny = json_encode($destiny["destiny"]);
            $destiny_new->date_range = json_encode($destiny["dateRange"]);
            $destiny_new->quantity_rooms = $destiny["quantity_rooms"];
            $destiny_new->quantity_adults = $destiny["quantity_adults"];
            $destiny_new->quantity_child = $destiny["quantity_child"];
            $destiny_new->quantity_persons_rooms = json_encode($destiny["quantity_persons_rooms"]);
            $destiny_new->typeclass_id = $destiny["typeclass_id"];
            $destiny_new->token_search = $uuid;
            $destiny_new->user_id = Auth::user()->id;
            $destiny_new->client_id = $client_id;
            $destiny_new->quantity_hotels = 0;
            $destiny_new->save();
        }

        return Response($uuid);
    }

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

        $search_destinies_paginate = SearchDestiny::where('user_id', Auth::user()->id)
            ->where('client_id', $client_id)->distinct()->orderBy('id', 'desc')->paginate(10);

        $pagination = [
            "total" => $search_destinies_paginate->total(),
            "per_page" => $search_destinies_paginate->perPage(),
            "from" => 1,
            "to" => $search_destinies_paginate->lastPage(),
            "last_page" => $search_destinies_paginate->lastPage(),
            "current_page" => $search_destinies_paginate->currentPage()
        ];

        $search_destinies_id = $search_destinies_paginate->pluck('token_search');

        $search_destinies = SearchDestiny::where('user_id', Auth::user()->id)
            ->where('client_id', $client_id)->whereIn('token_search', $search_destinies_id)->orderBy('id', 'desc')->get()->groupBy('token_search');

        foreach ($search_destinies as $id => $search_destinie){
            foreach ($search_destinie as $id2 => $search){
                if($search->destiny == "null"){
                    unset($search_destinies[$id]);
                }
            }
        }

        return response()->json(["data" => $search_destinies, "pagination" => $pagination]);
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

        $search_destinies = SearchDestiny::where('token_search', $token_search)
            ->where('user_id', Auth::user()->id)
            ->where('client_id', $client_id)
            ->get();

        return $search_destinies;
    }
}
