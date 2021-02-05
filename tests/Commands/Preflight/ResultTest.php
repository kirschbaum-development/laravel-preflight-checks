<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands\Preflight;

use Kirschbaum\PreflightChecks\Commands\Preflight\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /**
     * @test
     */
    public function testGetsRawData()
    {
        $rawData = ['raw', 'data' => 98];
        $result = new Result('Test\Test');
        $result->setData($rawData);

        $this->assertEquals($rawData, $result->getRawData());
    }

    /**
     * @test
     */
    public function testGetsDisplayData()
    {
        $rawData = ['raw', 'data' => 98];
        $result = new Result('Test\Test');
        $result->setData($rawData);

        $this->assertEquals(json_encode($rawData, JSON_PRETTY_PRINT), $result->getDisplayData());
    }

    /**
     * @test
     */
    public function testSetsAndGetsName()
    {
        $result = new Result('Test\Test');
        $name = 'test name';
        $result->setName($name);

        $this->assertEquals($name, $result->getName());
    }

    /**
     * @test
     */
    public function testGuessesName()
    {
        $result = new Result('Test\Test');

        // Guessed name is the class name in kebab-case
        $this->assertEquals('Test', $result->getName());
    }

    /**
     * @test
     */
    public function testPassesOrFails()
    {
        $result = new Result('Test\Test');
        $result->fail();
        $this->assertFalse($result->passed());
        $this->assertTrue($result->failed());

        $result->pass();
        $this->assertTrue($result->passed());
        $this->assertFalse($result->failed());
    }

    /**
     * @test
     */
    public function testSetsMessage()
    {
        $result = new Result('Test\Test');
        $message = 'test message';
        $result->setMessage($message);

        $this->assertEquals($message, $result->getMessage());
    }

    /**
     * @test
     */
    public function testSkipsStep()
    {
        $result = new Result('Test\Test');
        $result->skip(true);

        $this->assertTrue($result->skipped());

        $result->skip(false);
        $this->assertFalse($result->skipped());
    }

    /**
     * @test
     */
    public function testRequiredStep()
    {
        $result = new Result('Test\Test');
        $result->require(true);

        $this->assertTrue($result->required());

        $result->require(false);
        $this->assertFalse($result->required());
    }
}
