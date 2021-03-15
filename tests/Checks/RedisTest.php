<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis as RedisFacade;
use Kirschbaum\PreflightChecks\Checks\Redis;
use Kirschbaum\PreflightChecks\Checks\Result;
use Mockery;

class RedisTest extends BasePreflightCheckTest
{
    protected $preflightCheckClass = Redis::class;

    /**
     * @test
     */
    public function testChecksRedisIsAccessible()
    {
        $mockConnection = Mockery::mock(Connection::class);
        RedisFacade::shouldReceive('connection')
            ->once()
            ->andReturn($mockConnection);

        $connectionName = 'TestRedis';
        $connectionInfo = [
            'redis_version' => 'test-version',
            'os' => 'test-os',
        ];
        $mockConnection->shouldReceive('client->isConnected')->once()->andReturn(true);
        $mockConnection->shouldReceive('getName')->once()->andReturn($connectionName);
        $mockConnection->shouldReceive('client->info')->once()->andReturn($connectionInfo);

        $preflightCheck = new $this->preflightCheckClass();
        $result = $preflightCheck->check(new Result('Test\Test'));

        $this->assertPassed($result);
        $resultData = $result->getRawData();
        $this->assertEquals($connectionName, $resultData['name']);
        $this->assertEquals($connectionInfo['redis_version'], $resultData['info']['version']);
        $this->assertEquals($connectionInfo['os'], $resultData['info']['os']);
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
}
