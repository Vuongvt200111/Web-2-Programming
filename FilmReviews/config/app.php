<?php
declare(strict_types=1);

return [
    'app_name'                    => 'Tokusatsu Universe',
    'timezone'                    => 'Asia/Ho_Chi_Minh',
    'session_name'                => 'CINEMA_SESSION',
    'internal_email_domain'       => '@tokusatsu.com',
    'root_boss_id'                => 1,
    'roles'                       => [
        'user'      => 0,
        'boss'      => 1,
        'sub_admin' => 2,
    ],
    'tab_timeout'                 => 28800,
    'password_reset_ttl'          => 3600,
    'forgot_password_window'      => 900,
    'forgot_password_max_attempt' => 5,
    'forgot_password_cooldown'    => 300,
    'block_durations'             => [
        '1_hour'     => ['spec' => '+1 hour',  'label' => 'for 1 hour'],
        '24_hours'   => ['spec' => '+1 day',   'label' => 'for 24 hours'],
        '1_day'      => ['spec' => '+1 day',   'label' => 'for 24 hours'],
        '3_days'     => ['spec' => '+3 days',  'label' => 'for 3 days'],
        '7_days'     => ['spec' => '+7 days',  'label' => 'for 7 days'],
        '14_days'    => ['spec' => '+14 days', 'label' => 'for 14 days'],
        '30_days'    => ['spec' => '+30 days', 'label' => 'for 30 days'],
        'indefinite' => ['spec' => null,       'label' => 'indefinitely'],
    ],
];
