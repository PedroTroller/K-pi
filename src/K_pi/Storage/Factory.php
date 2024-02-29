<?php

namespace K_pi\Storage;

use K_pi\Storage;

/**
 * @template T of Storage
 */
interface Factory
{
    /**
     * @return T
     * @param array<mixed> $configuration
     */
    public function build(array $configuration): Storage;
}
