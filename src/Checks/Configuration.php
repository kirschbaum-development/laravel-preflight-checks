<?php

namespace Kirschbaum\PreflightChecks\Checks;

class Configuration extends PreflightCheck
{
    /**
     * Performs the preflight check.
     *
     * This method should set a pass/fail on the result.
     */
    public function check(Result $result): Result
    {
        $this->requiredConfig = $this->options;

        return $this->checkConfig($result);
    }
}
