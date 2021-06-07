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
        $this->loadRequiredConfigFromOptions();

        return $this->checkConfig($result);
    }

    /**
     * Parses the required config values from the options
     */
    protected function loadRequiredConfigFromOptions()
    {
        foreach ($this->options as $key => $value) {
            if (is_numeric($key)) {
                // Just a list of config values, no help/description
                $this->requiredConfig[] = $value;

                continue;
            }

            // config value is key
            $this->requiredConfig[] = $key;

            // Single entry is hint
            if (is_string($value)) {
                $this->configHints[$key] = $value;

                continue;
            }

            // Not a string, do nothing/reserve for future
        }
    }
}
