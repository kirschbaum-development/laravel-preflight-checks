<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks;

use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\Checks\Result;
use Orchestra\Testbench\TestCase;

class PreflightCheckTest extends TestCase
{
    /**
     * @test
     */
    public function testHandlesNormalCheck()
    {
        $check = new class extends PreflightCheck {
            public function check(Result $result): Result
            {
                return $result->pass();
            }
        };

        $report = $check->handle(collect(), fn ($report) => $report);

        $this->assertCount(1, $report);
        $this->assertTrue($report->first()->passed());
        $this->assertFalse($report->first()->skipped());
        $this->assertFalse($report->first()->failed());
    }

    /**
     * @test
     */
    public function testHandlesSkippedPassCheck()
    {
        $check = new class extends PreflightCheck {
            public function check(Result $result): Result
            {
                return $result->pass();
            }
        };

        $report = $check->handle(collect(), fn ($report) => $report);

        $this->assertCount(1, $report);
        $this->assertTrue($report->first()->passed());
        $this->assertFalse($report->first()->failed());
    }

    /**
     * @test
     */
    public function testHandlesSkippedFailCheck()
    {
        $check = new class extends PreflightCheck {
            public function check(Result $result): Result
            {
                return $result->fail();
            }

            protected function shouldSkip(): bool
            {
                return true;
            }
        };

        $report = $check->handle(collect(), fn ($report) => $report);

        $this->assertCount(1, $report);
        $this->assertFalse($report->first()->passed());
        $this->assertTrue($report->first()->failed());
        $this->assertTrue($report->first()->skipped());
    }

    /**
     * @test
     */
    public function testHandlesOptionalFail()
    {
        $check = new class extends PreflightCheck {
            protected bool $required = false;

            public function check(Result $result): Result
            {
                return $result->fail();
            }
        };

        $report = $check->handle(collect(), fn ($report) => $report);

        $this->assertCount(1, $report);
        $this->assertFalse($report->first()->passed());
        $this->assertTrue($report->first()->failed());
        $this->assertFalse($report->first()->required());
        $this->assertFalse($report->first()->skipped());
    }

    /**
     * @test
     */
    public function testChecksRequiredConfig()
    {
        config(['test-test-test' => 'banana']);
        $check = new class extends PreflightCheck {
            protected array $requiredConfig = [
                'test-test-test',
            ];

            public function check(Result $result): Result
            {
                return $result->pass();
            }
        };

        $report = $check->handle(collect(), fn ($report) => $report);

        $this->assertCount(2, $report);

        foreach ($report as $reportResult) {
            $this->assertTrue($reportResult->passed());
            $this->assertFalse($reportResult->failed());
            $this->assertTrue($reportResult->required());
            $this->assertFalse($reportResult->skipped());
        }
    }

    /**
     * @test
     */
    public function testFailsOnMissingConfig()
    {
        // Sanity check
        $this->assertFalse(config()->has('test-test-test'));

        $check = new class extends PreflightCheck {
            protected array $requiredConfig = [
                'test-test-test',
            ];

            public function check(Result $result): Result
            {
                return $result->pass();
            }
        };

        $report = $check->handle(collect(), fn ($report) => $report);

        $this->assertCount(2, $report);
        // First should be the config
        $this->assertFalse($report[0]->passed());
        $this->assertTrue($report[0]->failed());
        $this->assertTrue($report[0]->required());
        $this->assertFalse($report[0]->skipped());

        $this->assertTrue($report[1]->passed());
        $this->assertFalse($report[1]->failed());
        $this->assertTrue($report[1]->required());
        $this->assertFalse($report[1]->skipped());
    }
}
