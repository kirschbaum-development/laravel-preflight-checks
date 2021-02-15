<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks\Fixtures;

use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\Checks\Result;

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
