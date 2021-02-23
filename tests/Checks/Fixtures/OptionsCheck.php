<?php

namespace Kirschbaum\PreflightChecks\Tests\Checks\Fixtures;

use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\Checks\Result;

class OptionsCheck extends PreflightCheck
{
    public function check(Result $result): Result
    {
        return $this->options['pass'] ? $result->pass() : $result->fail();
    }
}
