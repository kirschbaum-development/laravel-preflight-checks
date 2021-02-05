<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures;

use Kirschbaum\PreflightChecks\Commands\Preflight\PreflightCheck;
use Kirschbaum\PreflightChecks\Commands\Preflight\Result;

class SkippedFailedCheck extends PreflightCheck
{
    public function check(Result $result): Result
    {
        return $result->fail();
    }

    public function shouldSkip(): bool
    {
        return true;
    }
}
