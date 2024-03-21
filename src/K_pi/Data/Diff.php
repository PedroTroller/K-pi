<?php

declare(strict_types=1);

namespace K_pi\Data;

use K_pi\ValueNormalizer;

final class Diff
{
    public readonly int|float $diff;

    public readonly bool $changed;

    /**
     * @param int<0, max> $precision
     */
    public function __construct(
        public readonly string $name,
        public readonly int|float $from,
        public readonly int|float $to,
        int $precision
    ) {
        $this->diff    = ValueNormalizer::normalize($to - $from, $precision);
        $this->changed = 0 !== $this->diff;
    }
}
