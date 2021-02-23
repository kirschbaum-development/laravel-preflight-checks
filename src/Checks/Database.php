<?php

namespace Kirschbaum\PreflightChecks\Checks;

use Doctrine\DBAL\Driver\PDO\Exception;
use Illuminate\Support\Facades\DB;

class Database extends PreflightCheck
{
    protected array $requiredConfig = [
        'database.default',
    ];

    /**
     * Performs the preflight check.
     *
     * This method should set a pass/fail on the result.
     */
    public function check(Result $result): Result
    {
        try {
            $pdo = DB::getPdo();
        } catch (Exception $e) {
            return $result->fail($e->getMessage(), $e);
        }

        $attributes = [];

        foreach (['CLIENT_VERSION', 'CONNECTION_STATUS', 'SERVER_INFO', 'SERVER_VERSION'] as $attribute) {
            $attributes[$attribute] = $pdo->getAttribute(constant("PDO::ATTR_${attribute}"));
        }

        $connection = $this->getConnection();

        return $result->pass("Connected to DB (Connection: ${connection})", $attributes);
    }

    /**
     * Boots the check
     */
    protected function boot(): void
    {
        $connection = $this->getConnection();
        $this->requiredConfig = array_merge(
            $this->requiredConfig,
            [
                "database.connections.${connection}.host",
                "database.connections.${connection}.port",
                "database.connections.${connection}.database",
                "database.connections.${connection}.username",
                "database.connections.${connection}.password",
            ]
        );
    }

    /**
     * Gets the DB connection to check (as specified in database.config).
     */
    protected function getConnection(): string
    {
        return $this->options['connection'] ?? config('database.default');
    }
}
