<?php

namespace App\Http\Controllers;

use App\HyperguestProperty;
use App\IntegrationHyperguest;
use Illuminate\Http\Request;

class SuscriptionHyperguestController extends Controller
{
    public function index(){
       $integration = IntegrationHyperguest::first();

       return response()->json($integration,200);
    }

    public function getSubscriptionDetails(){
        $integration = IntegrationHyperguest::first();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pdm.hyperguest.io/api/pdm/subscriptions/'.$integration->subscription_id.'/getSubscriptionDetails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer {$integration->token}",
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return response()->json($data);
    }

    public function enableSubscription(){
        $integration = IntegrationHyperguest::first();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pdm.hyperguest.io/api/pdm/subscriptions/'.$integration->subscription_id.'/enableSubscription');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer {$integration->token}",
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return response()->json($data);
    }

    public function disableSubscription(){
        $integration = IntegrationHyperguest::first();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pdm.hyperguest.io/api/pdm/subscriptions/'.$integration->subscription_id.'/disableSubscription');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer {$integration->token}",
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return response()->json($data);
    }

    public function fullAriSync(){
        $integration = IntegrationHyperguest::first();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pdm.hyperguest.io/api/pdm/subscriptions/'.$integration->subscription_id.'/fullARISync');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer {$integration->token}",
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        curl_close($ch);
        return response()->json($data);
    }

    public function updateSubscription(){
        $propertyIds = HyperguestProperty::pluck('property_id')->toArray();

        if (count($propertyIds) > 0){
            $fields = array('propertyIds' => $propertyIds);

            $integration = IntegrationHyperguest::first();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://pdm.hyperguest.io/api/pdm/subscriptions/'.$integration->subscription_id.'/updateSubscription');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields) );
            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$integration->token}",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $data = curl_exec($ch);
            curl_close($ch);
        }else{
            $data = ["response"=>"no existen propiedades en la subscripcion se recomienda desactivar la subscripcion"];
        }

        return response()->json($data);
    }

}
