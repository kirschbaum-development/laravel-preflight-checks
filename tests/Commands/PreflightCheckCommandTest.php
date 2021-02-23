<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\App;
use Kirschbaum\PreflightChecks\Checks\Exceptions\NoPreflightChecksDefinedException;
use Kirschbaum\PreflightChecks\PreflightChecksServiceProvider;
use Kirschbaum\PreflightChecks\Tests\Checks\Fixtures\FailedCheck;
use Kirschbaum\PreflightChecks\Tests\Checks\Fixtures\OptionsCheck;
use Kirschbaum\PreflightChecks\Tests\Checks\Fixtures\PassedCheck;
use Kirschbaum\PreflightChecks\Tests\Checks\Fixtures\SkippedCheck;
use Kirschbaum\PreflightChecks\Tests\Checks\Fixtures\SkippedFailedCheck;
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
        App::detectEnvironment(fn () => 'banana');

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
            'Provides options and passes' => [
                [
                    OptionsCheck::class => ['pass' => true],
                ], 0,
            ],
            'Provides options and fails' => [
                [
                    OptionsCheck::class => ['pass' => false],
                ], 1,
            ],
            'Can pass full config without options' => [
                [
                    [
                        'check' => PassedCheck::class,
                    ],
                ], 0,
            ],
            'Can fail full config with options' => [
                [
                    [
                        'check' => OptionsCheck::class,
                        'options' => ['pass' => false],
                    ],
                ], 1,
            ],
            'Full config allows duplicates' => [
                [
                    [
                        'check' => OptionsCheck::class,
                        'options' => ['pass' => false],
                    ],
                    [
                        'check' => OptionsCheck::class,
                        'options' => ['pass' => true],
                    ],
                ], 1,
            ],
        ];
    }

    /**
     * @test
     */
    public function testThrowsErrorIfNoConfig()
    {
        $this->markTestSkipped('Test setup not working on Laravel 6.x and 7.x');
        App::detectEnvironment(fn () => 'banana');

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
        App::detectEnvironment(fn () => 'BaNaNa');
        config(['preflight.checks.banana' => [PassedCheck::class]]);

        // No exception thrown
        $this->artisan('preflight:check')
            ->assertExitCode(0);
    }
}
