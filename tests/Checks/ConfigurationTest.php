<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Kirschbaum\PreflightChecks\Checks\Configuration;
use Kirschbaum\PreflightChecks\Checks\Result;

class ConfigurationTest extends BasePreflightCheckTest
{
    protected $preflightCheckClass = Configuration::class;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function testPassesWhenConfigIsSet()
    {
        $config = ['test1' => 'banana', 'test2' => 'apple'];
        config($config);
        $preflightCheck = new Configuration(array_keys($config));

        $result = $preflightCheck->check(new Result('Test\Test'));
        $this->assertPassed($result);
    }

    /**
     * @test
     */
    public function testFailsWhenConfigIsNotSet()
    {
        $config = ['test1', 'test2'];
        $preflightCheck = new Configuration($config);

        $result = $preflightCheck->check(new Result('Test\Test'));
        $this->assertFailed($result);
        $data = $result->getRawData();
        $this->assertTrue(in_array($config[0], $data));
        $this->assertTrue(in_array($config[1], $data));
    }
}
