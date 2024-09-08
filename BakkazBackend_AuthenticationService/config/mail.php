<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [
        'g_smtp' => [

            'transport' => env('G_MAIL_MAILER', 'smtp'),
            'host' => env('G_MAIL_HOST', 'recenthpost.com'),
            'port' => env('G_MAIL_PORT', 587),
            'encryption' => env('G_MAIL_ENCRYPTION', 'tls'),
            'username' => env('G_MAIL_USERNAME'),
            'password' => env('G_MAIL_PASSWORD'),
            'from' => [
                'address' => env('G_MAIL_FROM_ADDRESS','jessedan160@gmail.com'),
                'name' => env('G_MAIL_FROM_NAME','RecenthPost App'),
            ],
        ],

        'support_smtp' => [
            'transport' => env('SUPPORT_MAIL_MAILER', 'smtp'),
            'host' => env('SUPPORT_MAIL_HOST', 'recenthpost.com'),
            'port' => env('SUPPORT_MAIL_PORT', 587),
            'encryption' => env('SUPPORT_MAIL_ENCRYPTION', 'tls'),
            'username' => env('SUPPORT_MAIL_USERNAME'),
            'password' => env('SUPPORT_MAIL_PASSWORD'),
            'from' => [
                'address' => env('SUPPORT_MAIL_FROM_ADDRESS', 'support@recenthpost.com'),
                'name' => env('SUPPORT_MAIL_FROM_NAME', 'RecenthPost App'),
            ],
        ],

        'hello_smtp' => [
            'transport' => env('HELLO_MAIL_MAILER', 'smtp'),
            'host' => env('HELLO_MAIL_HOST', 'recenthpost.com'),
            'port' => env('HELLO_MAIL_PORT', 587),
            'encryption' => env('HELLO_MAIL_ENCRYPTION', 'tls'),
            'username' => env('HELLO_MAIL_USERNAME'),
            'password' => env('HELLO_MAIL_PASSWORD'),
            'from' => [
                'address' => env('HELLO_MAIL_FROM_ADDRESS', 'hello@recenthpost.com'),
                'name' => env('HELLO_MAIL_FROM_NAME', 'RecenthPost App'),
            ],
        ],

        'dnr_smtp' => [
            'transport' => env('DoNotReply_MAIL_MAILER', 'smtp'),
            'host' => env('DoNotReply_MAIL_HOST', 'recenthpost.com'),
            'port' => env('DoNotReply_MAIL_PORT', 587),
            'encryption' => env('DoNotReply_MAIL_ENCRYPTION', 'tls'),
            'username' => env('DoNotReply_MAIL_USERNAME'),
            'password' => env('DoNotReply_MAIL_PASSWORD'),
            'from' => [
                'address' => env('DoNotReply_MAIL_FROM_ADDRESS', 'donotreply@recenthpost.com'),
                'name' => env('DoNotReply_MAIL_FROM_NAME', 'RecenthPost App'),
            ],
        ],
        'smtp2' => [
            // SMTP settings for the second mailer
        ],
        'smtp3' => [
            // SMTP settings for the third mailer
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];
