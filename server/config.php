<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'diancan',
        'user' => 'root',
        'pass' => '123123',
        'charset' => 'utf8mb4',
        'prefix' => 'dc_'
    ],
    'wx' => [
        'appid' => '',
        'secret' => '',
        'mch_id' => '',
        'mch_key' => '',
        'notify_url' => ''
    ],
    'upload' => [
        'path' => __DIR__ . '/../uploads/',
        'max_size' => 5242880,
        'allow_ext' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'jwt' => [
        'secret' => 'diancan_secret_key_2024',
        'expire' => 7200
    ],
    'points_rate' => 1,
    'site' => [
        'name' => '智慧点餐',
        'logo' => '',
        'phone' => ''
    ]
];
