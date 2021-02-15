<?php

use Kirschbaum\PreflightChecks\Preflight\Configuration;
use Kirschbaum\PreflightChecks\Preflight\Database;
use Kirschbaum\PreflightChecks\Preflight\Redis;

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
