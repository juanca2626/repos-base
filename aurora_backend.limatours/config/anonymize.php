<?php
// config/anonymize.php
// Tipos soportados: hash, token, mask, email, phone, name, first_name, last_name, company, address, nullable, static, copy_from

return [
    'users' => [
        'name'  => ['type' => 'name'],
        'email' => ['type' => 'email', 'domain' => 'dev.aurora'],
    ],

    'clients' => [
        'name'           => ['type' => 'company'],
        'business_name'  => ['type' => 'company'],
        'address'        => ['type' => 'address'],
        'web'            => ['type' => 'static', 'value' => 'https://example.dev'],
        'postal_code'    => ['type' => 'static', 'value' => '00000'],
        'ruc'            => ['type' => 'hash'], // determinístico; mantiene consistencia
        'email'          => ['type' => 'email', 'domain' => 'dev.clients'],
        'phone'          => ['type' => 'phone'],
        'credit_line'    => ['type' => 'static', 'value' => 0],
        'general_markup' => ['type' => 'static', 'value' => 0],
        // 'logo' => ['type' => 'nullable'],
    ],

    'client_representatives' => [
        'name'     => ['type' => 'name'],
        'document' => ['type' => 'hash'], // DNI/RUC/pasaporte
        'email'    => ['type' => 'email', 'domain' => 'dev.reps'],
        'position' => ['type' => 'static', 'value' => 'Representante'],
    ],

    'hotels' => [
        'name'      => ['type' => 'company'],
        'web_site'  => ['type' => 'static', 'value' => 'https://hotel.dev'],
        'latitude'  => ['type' => 'static', 'value' => 0.0],
        'longitude' => ['type' => 'static', 'value' => 0.0],
    ],

    'contacts' => [
        'name'     => ['type' => 'first_name'],
        'surname'  => ['type' => 'last_name'],
        'lastname' => ['type' => 'last_name'],
        'position' => ['type' => 'static', 'value' => 'Contacto'],
        'email'    => ['type' => 'email', 'domain' => 'dev.hotels'],
    ],

    'chains' => [
        'name' => ['type' => 'company'],
    ],

    'quote_passengers' => [
        'first_name'      => ['type' => 'first_name'],
        'last_name'       => ['type' => 'last_name'],
        'document_number' => ['type' => 'hash'],
    ],

    'reservations_hotels' => [
        // executive_name = users.name por executive_id
        'executive_name' => [
            'type'        => 'copy_from',
            'table'       => 'users',
            'local_key'   => 'executive_id',
            'foreign_key' => 'id',
            'column'      => 'name',
        ],
        // hotel_name = hotels.name por hotel_id
        'hotel_name' => [
            'type'        => 'copy_from',
            'table'       => 'hotels',
            'local_key'   => 'hotel_id',
            'foreign_key' => 'id',
            'column'      => 'name',
        ],
    ],

    'reservations_hotels_rates_plans_rooms' => [
        'executive_name' => [
            'type'        => 'copy_from',
            'table'       => 'users',
            'local_key'   => 'executive_id',
            'foreign_key' => 'id',
            'column'      => 'name',
        ],
        'hotel_name' => [
            'type'        => 'copy_from',
            'table'       => 'hotels',
            'local_key'   => 'hotel_id',
            'foreign_key' => 'id',
            'column'      => 'name',
        ],
    ],

    'reservations_services' => [
        'executive_name' => [
            'type'        => 'copy_from',
            'table'       => 'users',
            'local_key'   => 'executive_id',
            'foreign_key' => 'id',
            'column'      => 'name',
        ],
        // Si aquí tienes otros PII, añádelos:
        // 'contact_email' => ['type' => 'email', 'domain' => 'dev.reservations'],
        // 'contact_phone' => ['type' => 'phone'],
    ],

    'reservations_emails_logs' => [
        // email_to es VARCHAR
        'email_to' => ['type' => 'email', 'domain' => 'dev.reservations'],

        // emails es JSON con arrays to/cc/bcc/reply_to
        'emails' => [
            'type'   => 'json_emails',
            'domain' => 'dev.reservations',
            'keys'   => ['to','cc','bcc','reply_to'],
        ],
    ],

    // ⚠️ Alineado a los checks del comando (plural).
    // Si tu tabla real es singular (reservation_passengers), cámbialo también en el comando.
    'reservation_passengers' => [
        'document_number' => ['type' => 'hash'],
        'name'            => ['type' => 'name'],
        'surnames'        => ['type' => 'last_name'],
        'email'           => ['type' => 'email', 'domain' => 'dev.reservations'],
        'phone'           => ['type' => 'phone'],
    ],


    'integration_hyperguests' => [
        'subscription_id'   => ['type' => 'hash'],
        'token'             => ['type' => 'token'],
        'username'          => ['type' => 'username'],
        'email'             => ['type' => 'email', 'domain' => 'dev.reservations'],
        'endpoint'          => ['type' => 'static', 'value' => 'https://hotel-hypeguest.dev'],
        'email_contact'     => ['type' => 'email', 'domain' => 'dev.hyperguest'],
        'commission_amount' => ['type' => 'phone'],
    ],
];
