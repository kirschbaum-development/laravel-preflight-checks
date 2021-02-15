<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands\Preflight;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDOConnection;
use Illuminate\Support\Facades\DB;
use Kirschbaum\PreflightChecks\Preflight\Database;
use Kirschbaum\PreflightChecks\Preflight\Result;
use Mockery;
use PDO;
use PDOException;

class DatabaseTest extends BasePreflightCheckTest
{
    protected $preflightCheckClass = Database::class;

    /**
     * @test
     */
    public function testChecksDatabaseAccessible()
    {
        $mockPdo = Mockery::mock(PDOConnection::class);
        DB::shouldReceive('getPdo')->once()->andReturn($mockPdo);

        $attributes = [
            PDO::ATTR_CLIENT_VERSION => 'test-version',
            PDO::ATTR_CONNECTION_STATUS => 'test-connection-status',
            PDO::ATTR_SERVER_INFO => 'test-server-info',
            PDO::ATTR_SERVER_VERSION => 'test-server-version',
        ];
        $mockPdo->shouldReceive('getAttribute')->andReturnUsing(fn ($arg) => $attributes[$arg]);

        $result = $this->preflightCheck->check(new Result('Test\Test'));
        $this->assertPassed($result);
    }

    /**
     * @test
     */
    public function testChecksDatabaseInAccessible()
    {
        DB::shouldReceive('getPdo')->once()->andThrow(new Exception(Mockery::mock(PDOException::class)));

        $result = $this->preflightCheck->check(new Result('Test\Test'));
        $this->assertFailed($result);
    }
}
