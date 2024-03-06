<?php

declare(strict_types=1);

namespace K_pi\Data;

final class Diff
{
    public readonly int|float $diff;

    public readonly bool $changed;

    public function __construct(public readonly string $name, public readonly int|float $from, public readonly int|float $to)
    {
        $this->diff = $to - $from;
        $this->changed = 0 != $this->diff;
    }
}
