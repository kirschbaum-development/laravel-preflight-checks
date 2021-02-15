<?php

namespace Kirschbaum\PreflightChecks\Tests\Commands\Preflight\Fixtures;

use Kirschbaum\PreflightChecks\Preflight\PreflightCheck;
use Kirschbaum\PreflightChecks\Preflight\Result;

class FailedCheck extends PreflightCheck
{
    public function check(Result $result): Result
    {
        return $result->fail();
    }
}
