<?php
return [
    'settings' => [
        'displayErrorDetails' => true,

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        // Database settings
        'db15' => [
            'host' => '192.168.1.15',
            'dbname' => 'IOPA',
            'user' => 'rebsolll',
            'pass' => 'iopa2019',
            'charset' => 'utf8mb4',
        ],

        'db14' => [
            'host' => '192.168.1.14',
            'dbname' => 'IOPA',
            'user' => 'rebsolhf',
            'pass' => 'iopa2019$',
            'charset' => 'utf8mb4',
        ],

        'db16' => [
            'host' => '192.168.1.16',
            'dbname' => 'IOPA',
            'user' => 'gmartinez',
            'pass' => 'iopa2018',
            'charset' => 'utf8mb4',
        ],

        'db18' => [
            'host' => '192.168.1.18',
            'dbname' => 'IOPA',
            'user' => 'lfarias',
            'pass' => 'iopa2022$',
            'charset' => 'utf8mb4',
        ],

        'dbMP' => [
            'host' => 'apis-imp.fortiddns.com',// 10.99.100.5
            'port' => 3306,//* Agregado el 18-12-2024
            'dbname' => 'IOPA',// IOPA_MP
            'user' => 'rebsolll',// rebsol
            'pass' => 'iopa2019',// #iopa20242025$
            'charset' => 'utf8mb4',
        ],
        
        // 'dbMaipu' => [
        //     'host' => '10.5.1.15',
        //     'dbname' => 'IOPA',
        //     'user' => 'rebsolll',
        //     'pass' => 'iopa2019',
        //     'charset' => 'utf8mb4',
        // ],

        'db250HF' => [
            'host' => '192.168.1.250',
            'dbname' => 'REBSOL_HF',
            'user' => 'gmartinez',
            'pass' => 'iopa2018',
            'charset' => 'utf8mb4',
        ],

        'db250LL' => [
            'host' => '192.168.1.250',
            'dbname' => 'REBSOL_LL',
            'user' => 'gmartinez',
            'pass' => 'iopa2018',
            'charset' => 'utf8mb4',
        ],

        'db250LF' => [
            'host' => '192.168.1.250',
            'dbname' => 'REBSOL_LF',
            'user' => 'gmartinez',
            'pass' => 'iopa2018',
            'charset' => 'utf8mb4',
        ],

        'dbSOFTLAND_PROD' => [
            'host' => '192.168.1.3\\SQL2022',
            'dbname' => 'IOPASA2021',
            'user' => 'nelstu',
            'pass' => 'NSloteria2015',
            'charset' => 'utf8mb4',
        ],

        'dbSOFTLAND_PROD_HF' => [
            'host' => '192.168.1.3',
            'dbname' => 'IOPAHUERFANOS',
            'user' => 'nelstu',
            'pass' => 'NSloteria2015',
            'charset' => 'utf8mb4',
        ],

        'dbSOFTLAND_DEV' => [
            'host' => '192.168.1.3\\SQL2022',
            'dbname' => 'PRUEBAIOPASA2021',
            'user' => 'nelstu',
            'pass' => 'NSloteria2015',
            'charset' => 'utf8mb4',
        ],
    ],
];
