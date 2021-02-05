<?php

namespace Kirschbaum\PreflightChecks\Commands\Preflight;

class Result
{
    /**
     * The preflight check class.
     */
    protected string $preflightCheck;

    /**
     * The display name of this check.
     */
    protected string $name;

    /**
     * Did the preflight check pass?
     */
    protected bool $passed = false;

    /**
     * Is this check required/optional?
     */
    protected bool $required = true;

    /**
     * Was this check skipped?
     */
    protected bool $skipped = false;

    /**
     * The short message result.
     */
    protected string $message = '';

    /**
     * The detailed data of the check.
     *
     * @var mixed
     */
    protected $data;

    public function __construct(string $preflightCheck)
    {
        $this->preflightCheck = $preflightCheck;
        $this->setName(class_basename($this->preflightCheck));
    }

    /**
     * Gets the raw data.
     *
     * @return mixed
     */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * Gets the data in a string format.
     */
    public function getDisplayData(): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }

    /**
     * Sets the data
     *
     * @param mixed $data
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Gets the name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the check name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Did the check pass?
     */
    public function passed(): bool
    {
        return $this->passed;
    }

    /**
     * Did the check fail?
     */
    public function failed(): bool
    {
        return ! $this->passed;
    }

    /**
     * Was the check required?
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * Set the result as required
     */
    public function require(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Was the check skipped?
     */
    public function skipped(): bool
    {
        return $this->skipped;
    }

    /**
     * Set this result as skipped
     */
    public function skip(bool $skip): self
    {
        $this->skipped = $skip;

        return $this;
    }

    /**
     * Creates a new passing result.
     *
     * @param null|mixed $data
     */
    public function pass(string $message = '', $data = null): self
    {
        if (! empty($message)) {
            $this->message = $message;
        }
        $this->data = $data;
        $this->passed = true;

        return $this;
    }

    /**
     * Creates a new failing result.
     *
     * @param null|mixed $data
     */
    public function fail(string $message = '', $data = null): self
    {
        if (! empty($message)) {
            $this->message = $message;
        }
        $this->data = $data;
        $this->passed = false;

        return $this;
    }

    /**
     * Sets the message
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the message
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
