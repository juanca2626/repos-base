<?php

namespace App\Http\Controllers;

use App\ExecutiveSubstitute;
use App\ExecutiveSubstituteClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExecutiveSubstitutesController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');
        $response = ExecutiveSubstitute::where('user_id', $user_id)
            ->with(['executive', 'substitute_clients'])
            ->withCount('substitute_clients')
            ->get();
        return Response::json($response);
    }

    public function store(Request $request)
    {

        $user_id = $request->input('user_id');
        $executives_ids = $request->input('executives_ids');
        $clients_ids = $request->input('clients_ids');

        foreach ($executives_ids as $executive_id) {
            $executives_substitute_for_delete =
                ExecutiveSubstitute::where('user_id', $user_id)->where('executive_id', $executive_id)->get();
            foreach ($executives_substitute_for_delete as $ex) {
                ExecutiveSubstituteClient::where('executive_substitute_id', $ex->id)->delete();
                $ex->delete();
            }

            $new_substitute_executive = new ExecutiveSubstitute();
            $new_substitute_executive->user_id = $user_id;
            $new_substitute_executive->executive_id = $executive_id;
            $new_substitute_executive->save();

            foreach ($clients_ids as $client_id) {
                $new_substitute_client = new ExecutiveSubstituteClient();
                $new_substitute_client->executive_substitute_id = $new_substitute_executive->id;
                $new_substitute_client->client_id = $client_id;
                $new_substitute_client->save();
            }
        }
        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function delete($id)
    {

        ExecutiveSubstituteClient::where('executive_substitute_id', $id)->delete();
        ExecutiveSubstitute::find($id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function getUsersForSupplant($executive_id)
    {

        $executives = ExecutiveSubstitute::where('executive_id', $executive_id)
            ->with(['user', 'substitute_clients.client'])
            ->get()->toArray();

        for ($e = 0; $e < count($executives); $e++) {
            $c = 0;
            foreach ($executives[$e]['substitute_clients'] as $client) {
                $_client = [];
                $_client['client_id'] = $client['client']['id'];
                $_client['client_code'] = $client['client']['code'];
                $_client['label'] = '(' . $client['client']['code'] . ') ' . $client['client']['name']; //* Para v-select
                $_client['code'] = $client['client']['id']; //* Para v-select
                $executives[$e]['substitute_clients'][$c] = $_client;
                $c++;
            }
        }

        return Response::json($executives);
    }

}
