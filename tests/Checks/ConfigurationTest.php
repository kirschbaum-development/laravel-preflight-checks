<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Kirschbaum\PreflightChecks\Checks\Result;
use Kirschbaum\PreflightChecks\Checks\Configuration;

class ConfigurationTest extends BasePreflightCheck
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
    public function testPassesWhenConfigIsBooleanAndSetToFalse()
    {
        $config = ['test1' => false];
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
        $foundKeys = array_column($data, 'key');
        $this->assertTrue(in_array($config[0], $foundKeys));
        $this->assertTrue(in_array($config[1], $foundKeys));
    }

    /**
     * @test
     */
    public function testLoadsConfigHints()
    {
        $config = [
            'test1' => uniqid('test1'),
            'test2',
        ];
        $preflightCheck = new Configuration($config);

        $result = $preflightCheck->check(new Result('Test\Test'));
        $this->assertFailed($result);
        $data = $result->getRawData();

        // first key
        $this->assertEquals('test1', $data[0]['key']);
        $this->assertEquals($config['test1'], $data[0]['hint']);

        // second key
        $this->assertEquals('test2', $data[1]['key']);
        // No hint set, so this is filtered out
        $this->assertArrayNotHasKey('hint', $data[1]);
    }
}
