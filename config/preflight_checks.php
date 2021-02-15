<?php

use Kirschbaum\PreflightChecks\Checks\Configuration;
use Kirschbaum\PreflightChecks\Checks\Database;
use Kirschbaum\PreflightChecks\Checks\Redis;

return [
    'checks' => [
        'production' => [
            // Database::class,
            // Redis::class,
            // Configuration::class => [
            //     // Essential production keys here
            // ],
        ],

        'local' => [
            // Database::class,
            // Configuration::class => [
            //     // Essential local keys here
            // ],
        ],
    ],
];
