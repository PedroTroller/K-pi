<?php

namespace K_pi\Configuration;

use K_pi\Configuration;
use Symfony\Component\Console\Input\InputInterface;

interface Extractor
{
    public function extract(InputInterface $input): ?Configuration;
}
