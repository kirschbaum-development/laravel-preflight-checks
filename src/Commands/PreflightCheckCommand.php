<?php

namespace Kirschbaum\PreflightChecks\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Kirschbaum\PreflightChecks\Checks\PreflightCheck;
use Kirschbaum\PreflightChecks\Exceptions\NoPreflightChecksDefinedException;
use Symfony\Component\Console\Output\OutputInterface;

class PreflightCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'preflight:check
                            {--only-show-failures : Hides output from passing checks only show errors (incompatible with verbose)}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks that the application is configured and aligned correctly to begin accepting requests.';

    /**
     * @psalm-var array<class-string>
     */
    protected array $preflightSteps = [];

    protected Pipeline $pipeline;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Pipeline $pipeline)
    {
        $this->bootChecks();

        $this->info('Starting Preflight Checks');
        $results = $pipeline
            ->send(collect())
            ->through($this->preflightSteps)
            ->thenReturn();

        $onlyFails = $this->option('only-show-failures');
        $passed = $results->reduce(function ($carry, $result) use ($onlyFails) {
            $this->line(
                sprintf(
                    '[%s] %s: %s',
                    $result->passed() ? 'PASS' : 'FAIL',
                    $result->getName(),
                    $result->getMessage()
                ),
                $result->failed() ? 'error' : 'info',
                ($onlyFails && $result->passed()) ? OutputInterface::VERBOSITY_VERBOSE : null
            );
            $this->line(
                $result->getDisplayData() ?? '',
                null,
                $result->failed() ? null : OutputInterface::VERBOSITY_VERBOSE
            );

            return ($result->skipped())
                ? $carry
                : $carry && $result->passed();
        }, true);

        return $passed ? 0 : 1;
    }

    /**
     * Boots the checks
     */
    protected function bootChecks()
    {
        $this->environment = $environment = strtolower(App::environment());

        try {
            if (! config()->has("preflight.checks.{$environment}")) {
                throw new NoPreflightChecksDefinedException("No preflight checks defined for this environment ({$environment})!");
            }
        } catch (InvalidArgumentException $exception) {
            // Catch for Laravel 6.x
            throw new NoPreflightChecksDefinedException("No preflight checks defined for this environment ({$environment})!");
        }

        foreach (config("preflight.checks.{$environment}") as $class => $options) {
            if (is_numeric($class) && is_subclass_of($options, PreflightCheck::class, true)) {
                $class = $options;
                $options = [];
            }

            if (is_array($options) && array_key_exists('check', $options)) {
                $class = $options['check'];
                $options = $options['options'] ?? [];
            }

            $this->preflightSteps[] = App::makeWith($class, compact('options'));
        }
    }
}
