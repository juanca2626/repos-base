<?php

namespace App\Http\Controllers;

use App\ClientEcommercePrivacyPolicies;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientEcommercePrivacyPoliciesController extends Controller
{
    use Translations;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $client_id)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();

        $policies = ClientEcommercePrivacyPolicies::with([
            'translations_title' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->with([
            'translations_privacy_policy' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->where('client_id', $client_id)
            ->orderBy('order', 'asc')->get();

        return Response::json(['success' => true, 'data' => $policies]);
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
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            try {
                DB::beginTransaction();
                $privacyPolicies = new ClientEcommercePrivacyPolicies();
                $privacyPolicies->client_id = $request->input('client_id');
                $privacyPolicies->order = 1;
                $privacyPolicies->status = 1;
                $privacyPolicies->save();
                $this->saveTranslation($request->input("translations"), 'ecommerce_privacy_policy',
                    $privacyPolicies->id);
                $this->saveTranslation($request->input("translations_content"), 'ecommerce_privacy_policy',
                    $privacyPolicies->id);
                DB::commit();
                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response::json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    public function show($client_id, $id)
    {
        $questionCategory = ClientEcommercePrivacyPolicies::with('translations_title')
            ->with('translations_privacy_policy')
            ->where('client_id', $client_id)
            ->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $questionCategory]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientEcommercePrivacyPolicies  $clientEcommercePrivacyPolicies
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }

        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            try {
                DB::beginTransaction();
                $privacyPolicies = ClientEcommercePrivacyPolicies::where('id', $id)->where('client_id',
                    $request->input('client_id'))->first();
                $privacyPolicies->save();
                $this->saveTranslation($request->input("translations"), 'ecommerce_privacy_policy',
                    $privacyPolicies->id);
                $this->saveTranslation($request->input("translations_content"), 'ecommerce_privacy_policy',
                    $privacyPolicies->id);
                DB::commit();
                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response::json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientEcommercePrivacyPolicies  $clientEcommercePrivacyPolicies
     * @return \Illuminate\Http\Response
     */
    public function destroy($client_id, $id)
    {
        try {
            DB::beginTransaction();
            $privacyPolicies = ClientEcommercePrivacyPolicies::where('id', $id)->where('client_id',
                $client_id)->first();
            $privacyPolicies->delete();
            $this->deleteTranslation('ecommerce_privacy_policy', $privacyPolicies->id);
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }

    }

    public function getPrivacyPolicies(Request $request, $client_id)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();

        $policies_query = ClientEcommercePrivacyPolicies::with([
            'translations_title' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->with([
            'translations_privacy_policy' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->where('client_id', $client_id)->orderBy('order', 'asc')->get();

        $policies = [];
        foreach ($policies_query as $key => $policy) {
            $policies[] = [
                'title' => (count($policy['translations_title']) > 0) ? $policy['translations_title'][0]['value'] : '',
                'content' => (count($policy['translations_privacy_policy']) > 0) ? $policy['translations_privacy_policy'][0]['value'] : '',
            ];
        }
        return Response::json(['success' => true, 'data' => $policies]);
    }
}
