<?php

namespace spec\K_pi\Data;

use K_pi\Data\Report;
use PhpSpec\ObjectBehavior;

class ReportSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }
}
