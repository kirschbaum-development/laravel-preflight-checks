<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Orchestra\Testbench\TestCase;
use Kirschbaum\PreflightChecks\Checks\Result;
use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\PreflightChecksServiceProvider;
use Kirschbaum\PreflightChecks\Tests\Helpers\CanAccessProtected;

abstract class BasePreflightCheck extends TestCase
{
    use CanAccessProtected;

    /**
     * @psalm-var class-string
     */
    protected $preflightCheckClass;

    public function checkConfigValues(PreflightCheck $preflightCheck)
    {
        $config = $this->getProtectedProperty($preflightCheck, 'requiredConfig');

        if (empty($config)) {
            // No config values, so this technically passes.
            $this->assertTrue(true);
        }

        foreach ($config as $configKey) {
            $this->assertConfigKeyChecked($preflightCheck, $configKey);
        }
    }

    protected function getPackageProviders($app)
    {
        return [PreflightChecksServiceProvider::class];
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

    private function assertConfigKeyChecked(PreflightCheck $preflightCheck, string $configKey): void
    {
        $originalValue = config($configKey);

        config([$configKey => null]);

        $configResult = $this->invokeMethod($preflightCheck, 'checkConfig', [new Result('Test\Config')]);
        $this->assertTrue(in_array($configKey, array_column($configResult->getRawData(), 'key')));

        config([$configKey => $originalValue]);
    }
}
