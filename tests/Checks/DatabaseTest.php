<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use PDO;
use Mockery;
use PDOException;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDOConnection;
use Kirschbaum\PreflightChecks\Checks\Result;
use Kirschbaum\PreflightChecks\Checks\Database;

class DatabaseTest extends BasePreflightCheckTest
{
    private const TEST_DEFAULT_DB_CONNECTION = 'test_default';

    protected $preflightCheckClass = Database::class;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => static::TEST_DEFAULT_DB_CONNECTION]);
    }

    /**
     * @test
     * @dataProvider providesDatabaseScenarios
     */
    public function testChecksDatabaseAccessible(?array $options, string $expectedConnection)
    {
        $mockPdo = Mockery::mock(PDOConnection::class);
        DB::shouldReceive('connection')
            ->once()
            ->with($expectedConnection)
            ->andReturn(
                Mockery::mock(Connection::class)
                    ->shouldReceive('getPdo')
                    ->once()
                    ->andReturn($mockPdo)
                    ->getMock()
            );

        $attributes = [
            PDO::ATTR_CLIENT_VERSION => 'test-version',
            PDO::ATTR_CONNECTION_STATUS => 'test-connection-status',
            PDO::ATTR_SERVER_INFO => 'test-server-info',
            PDO::ATTR_SERVER_VERSION => 'test-server-version',
        ];
        $mockPdo->shouldReceive('getAttribute')->andReturnUsing(fn ($arg) => $attributes[$arg]);

        $preflightCheck = is_null($options) ? new $this->preflightCheckClass : new $this->preflightCheckClass($options);
        $result = $preflightCheck->check(new Result('Test\Test'));
        $this->assertPassed($result);
    }

    public static function providesDatabaseScenarios()
    {
        yield 'No options checks default' => [
            null,
            static::TEST_DEFAULT_DB_CONNECTION,
        ];

        yield 'Empty options checks default' => [
            [],
            static::TEST_DEFAULT_DB_CONNECTION,
        ];

        yield 'Default checks default' => [
            ['connection' => static::TEST_DEFAULT_DB_CONNECTION],
            static::TEST_DEFAULT_DB_CONNECTION,
        ];

        $testConnection = 'test_connection_' . mt_rand(100, 99999);

        yield 'Connection checks connection' => [
            ['connection' => $testConnection],
            $testConnection,
        ];
    }

    /**
     * @test
     */
    public function testChecksDatabaseInaccessible()
    {
        DB::shouldReceive('connection')
            ->once()
            ->with(static::TEST_DEFAULT_DB_CONNECTION)
            ->andReturn(
                Mockery::mock(Connection::class)
                    ->shouldReceive('getPdo')
                    ->once()
                    ->andThrow(new Exception(PDOException::class))
                    ->getMock()
            );

        $preflightCheck = new $this->preflightCheckClass();
        $result = $preflightCheck->check(new Result('Test\Test'));
        $this->assertFailed($result);
    }

    /**
     * @test
     * @dataProvider providesDatabaseScenarios
     */
    public function testChecksConfigValues(?array $options, string $expectedConnection)
    {
        $preflight = is_null($options) ? new $this->preflightCheckClass : new $this->preflightCheckClass($options);
        $this->checkConfigValues($preflight);
    }
}
