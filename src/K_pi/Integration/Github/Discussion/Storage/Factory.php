<?php

declare(strict_types=1);

namespace K_pi\Integration\Github\Discussion\Storage;

use Assert\Assert;
use K_pi\Integration\Github;
use K_pi\Integration\Github\Discussion\Storage;
use K_pi\Storage as StorageInterface;
use K_pi\Storage\Factory as FactoryInterface;

/**
 * @implements FactoryInterface<Storage>
 */
final class Factory implements FactoryInterface
{
    public function __construct(
        private readonly Github $github
    ) {
    }

    public function build(array $configuration): Storage
    {
        [$owner, $repository, $discussion] = $this->parseUrl($configuration);

        return new Storage(
            owner: $owner,
            repository: $repository,
            discussion: $discussion,
            report: $this->parseBool($configuration, 'report', true),
            persist: $this->parseBool($configuration, 'persist', true),
            github: $this->github,
        );
    }

    /**
     * @param array<mixed> $configuration
     * @return array{non-empty-string, non-empty-string, int<1, max>}
     */
    public function parseUrl(array $configuration): array
    {
        Assert::that($configuration)->keyExists('url');

        $url = $configuration['url'];

        Assert::that($url)->string();

        if (!preg_match('#^https://github\.com/(.+)/(.+)/discussions/(\d+)$#', $url, $matches)) {
            throw new \Exception('Discussion url is invalid');
        }

        [, $owner, $repository, $discussion] = $matches;

        $discussion = (int) $discussion;

        Assert::that($owner)->notEmpty();
        Assert::that($repository)->notEmpty();
        Assert::that($discussion)->greaterThan(0);

        return [$owner, $repository, $discussion];
    }

    /**
     * @param array<mixed> $configuration
     */
    private function parseBool(array $configuration, string $key, bool $default): bool
    {
        if (false === array_key_exists($key, $configuration)) {
            return $default;
        }

        $bool = $configuration[$key];

        Assert::that($bool)->boolean();

        return $bool;
    }
}
