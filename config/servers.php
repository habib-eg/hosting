<?php
return [
    'default'=>[
        'driver'=>'cpanel',
        'server'=>'main',
    ],

    'cpanel'=>[
            // Array key not necessary unless you have multiple cpanel servers
            'main' => [

                /*
                |--------------------------------------------------------------------------
                | Host of your server
                |--------------------------------------------------------------------------
                |
                | Please provide this by its full URL including its protocol and its port
                |
                */
                'host' => env('CPANEL_HOST','41-65-168-71.cprapid.com'),
                'port' => env('CPANEL_PORT','2087'),
                'protocol' => env('CPANEL_HTTP','https'),
                'headers' => [
                    'Accept'     => 'application/json',
                    'Content-Type'     => 'application/json',
                ],
                'options' => [
                    'decode_content'    => true,
                    'http_errors'       => false,
                    'debug'             => true,
                    'synchronous'       => true,
                    'verify'            => true,
                    'connect_timeout'   => 0,
                    'timeout'           => 0,
                ],

                /*
                |--------------------------------------------------------------------------
                | Remote Access key
                |--------------------------------------------------------------------------
                |
                | You can find this remote access key on your CPanel/WHM server.
                | Log in to your server using root, and find `Remote Access Key`.
                | Copy and paste all of the string
                |
                */
                'auth' => env('CPANEL_AUTH','your_long_string_hash_key'),

                /*
                |--------------------------------------------------------------------------
                | Username
                |--------------------------------------------------------------------------
                |
                | By default, it will use root as its username. If you have another username,
                | make sure it has the same privelege with the root or at least it can access
                | External API which is provided by CPanel/WHM
                |
                */
                'username' => env('CPANEL_USERNAME','root'),
                /*
                |--------------------------------------------------------------------------
                | Password
                |--------------------------------------------------------------------------
                |
                | By default, it will use root as its password. If you have another password,
                | make sure it has the same privelege with the root or at least it can access
                | External API which is provided by CPanel/WHM
                |
                */
                'password' => env('CPANEL_PASSWORD','root'),

                'auth_type' => env('CPANEL_AUTH_TYPE','root'),
            ],
            // More Servers can be listed here as a new array
    ],
];
