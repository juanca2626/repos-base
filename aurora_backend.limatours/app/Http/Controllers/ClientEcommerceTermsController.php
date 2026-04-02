<?php

namespace App\Http\Controllers;

use App\ClientEcommerceTerms;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientEcommerceTermsController extends Controller
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

        $terms = ClientEcommerceTerms::with([
            'translations' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->where('client_id', $client_id)->get();

        return Response::json(['success' => true, 'data' => $terms]);
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
                $terms = new ClientEcommerceTerms();
                $terms->client_id = $request->input('client_id');
                $terms->save();
                $this->saveTranslation($request->input("translations"), 'ecommerce_terms_conditions',
                    $terms->id);
                DB::commit();
                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response::json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientEcommerceTerms  $clientEcommerceTerms
     * @return \Illuminate\Http\Response
     */
    public function show($client_id, $id)
    {
        $terms = ClientEcommerceTerms::with('translations')
            ->where('client_id', $client_id)
            ->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $terms]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientEcommerceTerms  $clientEcommerceTerms
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientEcommerceTerms $clientEcommerceTerms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientEcommerceTerms  $clientEcommerceTerms
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
                $terms = ClientEcommerceTerms::where('id', $id)->where('client_id',
                    $request->input('client_id'))->first();
                $terms->save();
                $this->saveTranslation($request->input("translations"), 'ecommerce_terms_conditions',
                    $terms->id);
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
     * @param  \App\ClientEcommerceTerms  $clientEcommerceTerms
     * @return \Illuminate\Http\Response
     */
    public function destroy($client_id, $id)
    {
        try {
            DB::beginTransaction();
            $terms = ClientEcommerceTerms::where('id', $id)->where('client_id',
                $client_id)->first();
            $terms->delete();
            $this->deleteTranslation('ecommerce_terms_conditions', $terms->id);
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

        $terms = ClientEcommerceTerms::with([
            'translations' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->where('client_id', $client_id)->get();

        $policies = [];
        foreach ($terms as $key => $term) {
            $policies[] = [
                'content' => (count($term['translations']) > 0) ? $term['translations'][0]['value'] : '',
            ];
        }

        return Response::json(['success' => true, 'data' => $policies]);
    }

}
