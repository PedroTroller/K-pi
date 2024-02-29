<?php

declare(strict_types=1);

namespace K_pi;

interface CheckReporter
{
    public function send(): void;
}
