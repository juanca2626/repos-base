<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quotation
    |--------------------------------------------------------------------------
    */

    'notification' => "Notification",
    'quotation' => "Quotation",
    'has_shared_a_quote_with_you' => "has shared a quote with you",
    'date' => "Date",
    'name' => "Name",
    'client' => "Client",
    'open' => "Access to Aurora",
    'customer_has_created_a_new_quote' => " :client_code customer has created a new quote",
    'validation' => [
        'quote_service_amount_not_found' => 'You must quote first to be able to download your quote',
        'rate_plan_off' => 'You must modify the type of rate, it is a deactivated rate',
    ]
];
