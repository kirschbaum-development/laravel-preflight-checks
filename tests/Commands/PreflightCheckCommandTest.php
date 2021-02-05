<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\App;
use Kirschbaum\PreflightChecks\Commands\Preflight\Exceptions\NoPreflightChecksDefinedException;
use Kirschbaum\PreflightChecks\PreflightChecksServiceProvider;
use Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures\FailedCheck;
use Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures\PassedCheck;
use Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures\SkippedCheck;
use Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures\SkippedFailedCheck;
use Mockery;
use Orchestra\Testbench\TestCase;

class PreflightCheckCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [PreflightChecksServiceProvider::class];
    }

    /**
     * @test
     * @dataProvider providesCommandScenarios
     */
    public function testPerformsPreflightChecks(array $config, int $expectedExitCode)
    {
        App::detectEnvironment(function () {
            return 'banana';
        });

        config(['preflight.checks.banana' => $config]);

        $this->artisan('preflight:check')
            ->assertExitCode($expectedExitCode);
    }

    public function providesCommandScenarios()
    {
        return [
            'Passes with no checks' => [
                [], 0,
            ],
            'Passes with passing check' => [
                [PassedCheck::class], 0,
            ],
            'Fails with failed check' => [
                [FailedCheck::class], 1,
            ],
            'Passes with skipped check' => [
                [SkippedCheck::class], 0,
            ],
            'Passes with failed skipped check' => [
                [SkippedFailedCheck::class], 0,
            ],
            'Fails with mix' => [
                [PassedCheck::class, FailedCheck::class], 1,
            ],
            'Passes with mix of failed skipped' => [
                [PassedCheck::class, SkippedFailedCheck::class], 0,
            ],
        ];
    }

    /**
     * @test
     */
    public function testThrowsErrorIfNoConfig()
    {
        App::detectEnvironment(function () {
            return 'banana';
        });

        $configMock = Mockery::mock(Repository::class)->makePartial();
        $configMock->shouldReceive('has')
            ->with('preflight.checks.banana')
            ->andReturn(false);
        $this->instance('config', $configMock);

        $this->expectException(NoPreflightChecksDefinedException::class);
        $this->artisan('preflight:check');
    }

    /**
     * @test
     */
    public function testEnvironmentIsCaseInsensitive()
    {
        App::detectEnvironment(function () {
            return 'BaNaNa';
        });
        config(['preflight.checks.banana' => [PassedCheck::class]]);

        // No exception thrown
        $this->artisan('preflight:check')
            ->assertExitCode(0);
    }
}
