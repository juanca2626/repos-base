<?php

namespace App\Http\Controllers;

use App\AuroraContactUs;
use App\Client;
use App\Http\Stella\StellaService;
use App\Mail\NotificationContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class AuroraContactUsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $contact = new AuroraContactUs();
            $contact->user_id = Auth::id();
            $contact->name = $request->get('user_name');
            $contact->surname = $request->get('user_surname');
            $contact->company = $request->get('user_company');
            $contact->email_from = $request->get('user_email');
            $contact->phone = $request->get('user_phone');
            $contact->email_to = $request->get('executive_email');
            $contact->subject = $request->get('subject');
            $contact->message = $request->get('message');
            $contact->accept_privacy_policies = $request->get('privacy_policy');
            $contact->accept_data_processing = $request->get('data_treatment');
            if ($contact->save()) {
//                Mail::to($contact->email_to)->send(new NotificationContactUs($contact));
                $contact = AuroraContactUs::where('id', $contact->id)->with([
                    'user' => function ($query) {
                        $query->select(['id', 'code', 'name']);
                    }
                ])->first();

                if($contact->email_to == '' OR $contact->email_to == null)
                {
                   $contact->email_to = 'jgq@limatours.com.pe';
                }

                Mail::to($contact->email_to)->bcc([
                    'kluizsv@gmail.com',
                    'jgq@limatours.com.pe',
                ])->send(new NotificationContactUs($contact));
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            DB::rollback();
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AuroraContactUs  $auroraContactUs
     * @return \Illuminate\Http\Response
     */
    public function show(AuroraContactUs $auroraContactUs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AuroraContactUs  $auroraContactUs
     * @return \Illuminate\Http\Response
     */
    public function edit(AuroraContactUs $auroraContactUs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AuroraContactUs  $auroraContactUs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AuroraContactUs $auroraContactUs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AuroraContactUs  $auroraContactUs
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuroraContactUs $auroraContactUs)
    {
        //
    }

    public function getExecutivesClient(Request $request, $client_id)
    {

        $stellaService = new StellaService();
        $client = Client::where('id', $client_id)->with([
            'client_executives' => function ($query) {
                $query->select(['email']);
                $query->first();
            }
        ])->first(['id', 'code']);
        $clientExecutives = [];
        if ($client) {
            $executivesTOM = (array)$stellaService->getExecutivesByClient($client->code);
            if (count($executivesTOM) > 0) {
                foreach ($executivesTOM as $executive) {
                    if ($executive->email != null) {
                        $clientExecutives[] = [
                            "email" => $executive->email
                        ];
                    }
                }
            } else {
                foreach ($client->client_executives as $executive) {
                    $clientExecutives[] = [
                        "email" => $executive->email
                    ];
                }
            }
        }

        return Response::json(['success' => true, 'data' => $clientExecutives]);
    }
}
