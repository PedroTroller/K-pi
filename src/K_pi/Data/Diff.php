<?php

declare(strict_types=1);

namespace K_pi\Data;

final class Diff
{
    public readonly float|int $diff;

    public readonly bool $changed;

    public function __construct(
        public readonly string $name,
        public readonly float|int $from,
        public readonly float|int $to,
    ) {
        $this->diff    = $to - $from;
        $this->changed = 0 != $this->diff;
    }
}
