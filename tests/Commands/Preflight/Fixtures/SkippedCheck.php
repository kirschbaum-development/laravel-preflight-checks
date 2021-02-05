<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures;

use Kirschbaum\PreflightChecks\Commands\Preflight\PreflightCheck;
use Kirschbaum\PreflightChecks\Commands\Preflight\Result;

class SkippedCheck extends PreflightCheck
{
    public function check(Result $result): Result
    {
        return $result->pass();
    }

    public function shouldSkip(): bool
    {
        return true;
    }
}
