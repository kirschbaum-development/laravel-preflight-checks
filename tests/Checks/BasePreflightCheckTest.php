<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\Checks\Result;
use Kirschbaum\PreflightChecks\PreflightChecksServiceProvider;
use Kirschbaum\PreflightChecks\Tests\Helpers\CanAccessProtected;
use Orchestra\Testbench\TestCase;

abstract class BasePreflightCheckTest extends TestCase
{
    use CanAccessProtected;

    /**
     * @psalm-var class-string
     */
    protected $preflightCheckClass;

    protected PreflightCheck $preflightCheck;

    protected function getPackageProviders($app)
    {
        return [PreflightChecksServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->preflightCheck = new $this->preflightCheckClass();
    }

    /**
     * @test
     */
    public function testChecksConfigValues()
    {
        $config = $this->getProtectedProperty($this->preflightCheck, 'requiredConfig');

        if (empty($config)) {
            // No config values, so this technically passes.
            $this->assertTrue(true);
        }

        foreach ($config as $configKey) {
            $this->assertConfigKeyChecked($configKey);
        }
    }

    protected function assertPassed(Result $result): void
    {
        $this->assertTrue($result->passed());
        $this->assertFalse($result->failed());
    }

    protected function assertFailed(Result $result): void
    {
        $this->assertFalse($result->passed());
        $this->assertTrue($result->failed());
    }

    private function assertConfigKeyChecked(string $configKey): void
    {
        $originalValue = config($configKey);

        config([$configKey => null]);

        $configResult = $this->invokeMethod($this->preflightCheck, 'checkConfig', [new Result('Test\Config')]);
        $this->assertTrue(in_array($configKey, $configResult->getRawData()));

        config([$configKey => $originalValue]);
    }
}
