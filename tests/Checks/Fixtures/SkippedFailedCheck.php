<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks\Fixtures;

use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\Checks\Result;

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
