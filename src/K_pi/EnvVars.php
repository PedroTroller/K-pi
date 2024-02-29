<?php

declare(strict_types=1);

namespace K_pi;

final class EnvVars
{
    /**
     * @var array<non-empty-string, string>
     */
    private array $defaults = [];

    public function get(string $name): string
    {
        if (is_string($env = getenv($name))) {
            return $env;
        }

        if (array_key_exists($name, $this->defaults)) {
            return $this->defaults[$name];
        }

        throw new \Exception("$name env variable not found.");
    }

    /**
     * @param non-empty-string $name
     */
    public function default(string $name, string $env): void
    {
        $this->defaults[$name] = $env;
    }
}
