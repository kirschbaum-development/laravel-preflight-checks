<?php

namespace Kirschbaum\PreflightChecks\Checks;

use Illuminate\Support\Facades\Redis as RedisFacade;

class Redis extends PreflightCheck
{
    protected array $requiredConfig = [
        // @see boot()
    ];

    /**
     * Performs the preflight check.
     *
     * This method should set a pass/fail on the result.
     */
    public function check(Result $result): Result
    {
        try {
            $connection = RedisFacade::connection($this->getConnection());
        } catch (\Exception $e) {
            return $result->fail($e->getMessage(), $e);
        }

        if (! $connection->client()->isConnected()) {
            return $result->fail('Could not connect to Redis ('.$this->getConnection().')');
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

    /**
     * Gets the DB connection to check (as specified in database.config).
     */
    protected function getConnection(): string
    {
        return $this->options['connection'] ?? 'default';
    }

    /**
     * Boots the check
     */
    protected function boot(): void
    {
        $connection = $this->getConnection();
        $usesAuth = $this->options['auth'] ?? true;

        $this->requiredConfig = array_merge(
            $this->requiredConfig,
            [
                "database.redis.{$connection}.host",
                "database.redis.{$connection}.port",
            ],
            $usesAuth ? [
                "database.redis.{$connection}.password",
            ] : []
        );
    }
}
