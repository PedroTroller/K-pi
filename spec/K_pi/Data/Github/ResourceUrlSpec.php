<?php

namespace spec\K_pi\Data\Github;

use K_pi\Data\Github\ResourceUrl;
use PhpSpec\ObjectBehavior;

class ResourceUrlSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('https://github.com/KnpLabs/K-pi/pull/11');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceUrl::class);
    }

    function it_can_get_data_from_an_url()
    {
        $this->owner->shouldBe('KnpLabs');
        $this->repository->shouldBe('K-pi');
        $this->type->shouldBe('pull');
        $this->number->shouldBe(11);
    }
}
