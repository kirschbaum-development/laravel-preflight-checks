<?php

use Kirschbaum\PreflightChecks\Commands\Preflight\Configuration;
use Kirschbaum\PreflightChecks\Commands\Preflight\Database;
use Kirschbaum\PreflightChecks\Commands\Preflight\Redis;

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
