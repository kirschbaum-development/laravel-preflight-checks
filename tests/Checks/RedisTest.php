<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Mockery;
use Illuminate\Redis\Connections\Connection;
use Kirschbaum\PreflightChecks\Checks\Redis;
use Kirschbaum\PreflightChecks\Checks\Result;
use Illuminate\Support\Facades\Redis as RedisFacade;

class RedisTest extends BasePreflightCheck
{
    protected $preflightCheckClass = Redis::class;

    /**
     * @test
     * @dataProvider providesConnectionScenarios
     *
     * @param mixed $options
     * @param mixed $expectedConnection
     */
    public function testChecksRedisIsAccessible($options, $expectedConnection)
    {
        $mockConnection = Mockery::mock(Connection::class);
        RedisFacade::shouldReceive('connection')
            ->once()
            ->with($expectedConnection)
            ->andReturn($mockConnection);

        $connectionName = 'TestRedis';
        $connectionInfo = [
            'redis_version' => 'test-version',
            'os' => 'test-os',
        ];
        $mockConnection->shouldReceive('client->isConnected')->once()->andReturn(true);
        $mockConnection->shouldReceive('getName')->once()->andReturn($connectionName);
        $mockConnection->shouldReceive('client->info')->once()->andReturn($connectionInfo);

        $preflightCheck = new $this->preflightCheckClass($options);
        $result = $preflightCheck->check(new Result('Test\Test'));

        $this->assertPassed($result);
        $resultData = $result->getRawData();
        $this->assertEquals($connectionName, $resultData['name']);
        $this->assertEquals($connectionInfo['redis_version'], $resultData['info']['version']);
        $this->assertEquals($connectionInfo['os'], $resultData['info']['os']);
    }

    public static function providesConnectionScenarios()
    {
        return [
            'No options is default' => [
                [], 'default',
            ],
            'Default is default' => [
                [
                    'connection' => 'default',
                ],
                'default',
            ],
            'Banana is banana' => [
                [
                    'connection' => 'banana',
                ],
                'banana',
            ],
        ];
    }

    /**
     * @test
     */
    public function testChecksRedisIsDown()
    {
        RedisFacade::shouldReceive('connection')
            ->once()
            ->andThrow(\Exception::class);

        $preflightCheck = new $this->preflightCheckClass();
        $result = $preflightCheck->check(new Result('Test\Test'));

        $this->assertFailed($result);
    }

    /**
     * @test
     */
    public function testChecksRedisIsNotConnected()
    {
        $mockConnection = Mockery::mock(Connection::class);
        RedisFacade::shouldReceive('connection')
            ->once()
            ->andThrow($mockConnection);

        $mockConnection->shouldReceive('client->isConnected')
            ->once()
            ->andReturn(false);
        $mockConnection->shouldNotReceive('getName');
        $mockConnection->shouldNotReceive('client->info');

        $preflightCheck = new $this->preflightCheckClass();
        $result = $preflightCheck->check(new Result('Test\Test'));

        $this->assertFailed($result);
    }

    /**
     * @test
     */
    public function testChecksConfigValues()
    {
        $this->checkConfigValues(new $this->preflightCheckClass());
    }

    /**
     * @test
     */
    public function testDoesNotCheckPasswordForNoAuthConfig()
    {
        $check = new $this->preflightCheckClass(['auth' => false]);

        $this->assertArrayNotHasKey(
            'database.redis.default.password',
            $this->getProtectedProperty($check, 'requiredConfig')
        );
    }
}
