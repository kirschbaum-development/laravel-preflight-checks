<?php

namespace Kirschbaum\PreflightChecks\Commands\Preflight;

use Illuminate\Support\Facades\Redis as RedisFacade;

class Redis extends PreflightCheck
{
    protected array $requiredConfig = [
        'database.redis.default',
        'database.redis.default.host',
        'database.redis.default.password',
        'database.redis.default.port',
    ];

    /**
     * Performs the preflight check.
     *
     * This method should set a pass/fail on the result.
     */
    public function check(Result $result): Result
    {
        try {
            $connection = RedisFacade::connection();
        } catch (\Exception $e) {
            return $result->fail($e->getMessage(), $e);
        }

        if (! $connection->client()->isConnected()) {
            return $result->fail('Could not connect to Redis');
        }
        $info = $connection->client()->info();

        return $result->pass('Connected to Redis.', [
            'name' => $connection->getName(),
            'info' => [
                'version' => $info['redis_version'],
                'os' => $info['os'],
            ],
        ]);
    }
}
