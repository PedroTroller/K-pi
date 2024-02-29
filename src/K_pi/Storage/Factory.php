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
     */
    public function build(string $reportName, mixed $configuration): Storage;
}
