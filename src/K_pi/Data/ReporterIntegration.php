<?php

declare(strict_types=1);

namespace K_pi\Data;

enum ReporterIntegration: string
{
    case GITHUB_CHECK_RUN = 'github-check-run';
}
