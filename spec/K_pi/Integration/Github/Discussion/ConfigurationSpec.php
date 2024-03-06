<?php

namespace spec\K_pi\Integration\Github\Discussion;

use K_pi\Configuration\Exception\AtPathException;
use K_pi\Integration\Github\Discussion\Configuration;
use PhpSpec\ObjectBehavior;
use stdClass;

class ConfigurationSpec extends ObjectBehavior
{
    function let($configuration)
    {
        $configuration->url = 'https://github.com/KnpLabs/K-pi/discussions/42';

        $this->beConstructedWith('test', $configuration);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Configuration::class);
    }

    function it_is_able_to_load_minimum_configuration()
    {
        $this->owner->shouldBe('KnpLabs');
        $this->repository->shouldBe('K-pi');
        $this->discussion->shouldBe(42);
        $this->report->shouldBe(true);
        $this->persist->shouldBe(true);
    }

    function it_supports_full_configuration($configuration)
    {
        $configuration->report = false;
        $configuration->persist = false;

        $this->owner->shouldBe('KnpLabs');
        $this->repository->shouldBe('K-pi');
        $this->discussion->shouldBe(42);
        $this->report->shouldBe(false);
        $this->persist->shouldBe(false);
    }

    function it_handles_wrong_configuration()
    {
        foreach ($this->invalidDataProvider() as [$json, $exception]) {
            $configuration = json_decode(json_encode($json), false);

            $this->beConstructedWith('test', $configuration);

            $this->shouldThrow($exception)->duringInstantiation();
        }
    }

    private function invalidDataProvider()
    {
        yield [
            [
                'url' => 'https://github.com/KnpLabs/K-pi/discussions/0',
                'persist' => true,
                'report' => true,
            ],
            new AtPathException('.reports.test.storage.github-discussion.url', 'invalid Github discussion url'),
        ];

        yield [
            [
                'url' => 'https://github.com/KnpLabs//discussions/42',
                'persist' => true,
                'report' => true,
            ],
            new AtPathException('.reports.test.storage.github-discussion.url', 'invalid Github discussion url'),
        ];

        yield [
            [
                'url' => 'https://github.com/KnpLabs/K-pi/discussions/42',
                'persist' => 1,
                'report' => true,
            ],
            new AtPathException('.reports.test.storage.github-discussion.persist', 'must be a boolean'),
        ];
    }
}