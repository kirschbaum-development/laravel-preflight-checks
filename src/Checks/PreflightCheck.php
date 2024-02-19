<?php

namespace Kirschbaum\PreflightChecks\Checks;

use Illuminate\Support\Collection;

abstract class PreflightCheck
{
    /**
     * Required configuration keys
     */
    protected array $requiredConfig = [];

    /**
     * Configuration hints
     */
    protected array $configHints = [];

    /**
     * Is this a required step?
     */
    protected bool $required = true;

    /**
     * Options for this check
     */
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->loadOptions($options);
        $this->boot();
    }

    /**
     * Handles the check
     *
     */
    public function handle(Collection $report, \Closure $next)
    {
        $result = $this->initResult();
        $result->require($this->isRequired());

        if ($this->shouldSkip()) {
            $report->push($result->skip(true));

            return $next($report);
        }

        if (! empty($this->requiredConfig)) {
            $report->push($this->checkConfig($this->initConfigResult()));
        }

        $report->push($this->check($result));

        return $next($report);
    }

    /**
     * Performs the preflight check.
     *
     * This method should set a pass/fail on the result.
     */
    abstract public function check(Result $result): Result;

    /**
     * Boot the check
     */
    protected function boot(): void
    {
    }

    /**
     * Parses the options
     */
    protected function loadOptions(array $options = [])
    {
        if (array_key_exists('config_hints', $options)) {
            $this->configHints = $options['config_hints'];
            unset($options['config_hints']);
        }
        $this->options = $options;
    }

    /**
     * Initializes a result object.
     */
    protected function initResult(): Result
    {
        return new Result(get_called_class());
    }

    /**
     * Initializes a config result object.
     */
    protected function initConfigResult(): Result
    {
        return $this->initResult();
    }

    /**
     * Should we skip this step?
     */
    protected function shouldSkip(): bool
    {
        return false;
    }

    /**
     * Should the check still be performed, but not fail the checklist if
     * it does not pass?
     */
    protected function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Checks each required config entry to be set and non-empty.
     */
    protected function checkConfig(Result $result): Result
    {
        $missingKeys = [];

        foreach ($this->requiredConfig as $configKey) {
            $configValue = config($configKey);

            if (! config()->has($configKey) || ! is_bool($configValue) && empty($configValue)) {
                $missingKeys[] = $configKey;
            }
        }

        if (! empty($missingKeys)) {
            return $result->fail(
                $this->getConfigFailMessage($missingKeys),
                $this->getConfigFailKeyData($missingKeys),
            );
        }

        return $result->pass($this->getConfigPassMessage(), $this->requiredConfig);
    }

    /**
     * Gets the message for successful key check.
     */
    protected function getConfigPassMessage()
    {
        return 'Config keys are set!';
    }

    /**
     * Gets the message for failed key check.
     */
    protected function getConfigFailMessage(array $missingKeys = [])
    {
        return 'Missing configuration key(s).';
    }

    /**
     * Gets the key data for missing keys.
     */
    protected function getConfigFailKeyData(array $missingKeys = [])
    {
        return array_map(function ($key) {
            return array_filter([
                'key' => $key,
                'hint' => $this->configHints[$key] ?? null,
            ]);
        }, $missingKeys);
    }
}
