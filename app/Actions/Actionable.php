<?php

namespace App\Actions;

abstract class Actionable
{
    abstract public function handle(): void;

    /**
     * @throws \Exception
     *
     * @see static::handle()
     */
    public static function run(...$arguments): void
    {
        app(static::class)->handle(...$arguments);
    }

    /**
     * Validate required parameters
     *
     * @throws \InvalidArgumentException
     */
    protected function validateParams(array $requiredParams, array $arguments): void
    {
        foreach ($requiredParams as $param) {
            if (!isset($arguments[$param])) {
                throw new \InvalidArgumentException("Missing required parameter: {$param}");
            }
        }
    }
}
