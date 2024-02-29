<?php

declare(strict_types=1);

namespace K_pi\Configuration\Exception;

final class AtPathException extends \Exception
{
    /**
     * @param non-empty-string $path
     * @param non-empty-string $message
     */
    public function __construct(string $path, string $message)
    {
        parent::__construct(sprintf("At path %s: %s", $path, $message));
    }
}
