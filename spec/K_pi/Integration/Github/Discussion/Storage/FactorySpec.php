<?php

namespace spec\K_pi\Integration\Github\Discussion\Storage;

use K_pi\Integration\Github\Discussion\Storage;
use K_pi\Integration\Github\Discussion\Storage\Factory;
use PhpSpec\ObjectBehavior;

class FactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Factory::class);
    }

    function it_is_able_to_parse_configuration()
    {
        foreach ($this->dataProvider() as [$configuration, $storage]) {
            $this->build($configuration)->shouldBeLike($storage);
        }
    }

    private function dataProvider(): iterable
    {
        yield [
            ['url' => 'https://github.com/KnpLabs/K-pi/discussions/42'],
            new Storage(
                owner: 'KnpLabs',
                repository: 'K-pi',
                discussion: 42,
                report: true,
                persist: true,
            )
        ];

        yield [
            ['url' => 'https://github.com/KnpLabs/K-pi/discussions/42', 'report' => true, 'persist' => false],
            new Storage(
                owner: 'KnpLabs',
                repository: 'K-pi',
                discussion: 42,
                report: true,
                persist: false,
            )
        ];

        yield [
            ['url' => 'https://github.com/KnpLabs/K-pi/discussions/42', 'report' => false, 'persist' => true],
            new Storage(
                owner: 'KnpLabs',
                repository: 'K-pi',
                discussion: 42,
                report: false,
                persist: true,
            )
        ];
    }
}
