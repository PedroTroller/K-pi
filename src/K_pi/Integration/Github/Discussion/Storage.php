<?php

declare(strict_types=1);

namespace K_pi\Integration\Github\Discussion;

use K_pi\Data\Report;
use K_pi\Storage as StorageInterface;

final class Storage implements StorageInterface
{
    public function __construct(private readonly string $owner, private readonly string $repository, private readonly int $discussion, private readonly bool $report, private readonly bool $persist)
    {

    }

    public function read(): Report
    {
        if (false === $this->persist) {
            return new Report();
        }
    }

    public function write(Report $report): void
    {
    }
}
