<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'conector' => [
            'driver' => 'local',
            'root' => storage_path('conector'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'siteminder_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/siteminder/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'siteminder_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/siteminder/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'siteminder_reservation_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/siteminder/reservation/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'siteminder_reservation_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/siteminder/reservation/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'siteminder_cancelation_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/siteminder/cancelation/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'siteminder_cancelation_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/siteminder/cancelation/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'travelclick_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/travelclick/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'travelclick_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/travelclick/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'travelclick_reservation_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/travelclick/reservation/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'travelclick_reservation_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/travelclick/reservation/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'travelclick_cancelation_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/travelclick/cancelation/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'travelclick_cancelation_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/travelclick/cancelation/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'erevmax_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/erevmax/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'erevmax_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/erevmax/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'erevmax_reservation_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/erevmax/reservation/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'erevmax_reservation_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/erevmax/reservation/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'erevmax_cancelation_error' => [
            'driver' => 'local',
            'root' => storage_path('conector/erevmax/cancelation/error'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],

        'erevmax_cancelation_success' => [
            'driver' => 'local',
            'root' => storage_path('conector/erevmax/cancelation/success'),
            'permissions' => [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
        ],
    ],

];
