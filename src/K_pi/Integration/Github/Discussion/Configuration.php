<?php

declare(strict_types=1);

namespace K_pi\Integration\Github\Discussion;

use K_pi\Configuration\Exception\AtPathException;
use K_pi\Data\Integration;
use K_pi\Data\StorageIntegration;

final class Configuration
{
    /**
     * @var non-empty-string
     */
    public readonly string $owner;

    /**
     * @var non-empty-string
     */
    public readonly string $repository;

    /**
     * @var positive-int
     */
    public readonly int $discussion;

    public readonly bool $report;

    public readonly bool $persist;

    public function __construct(private readonly string $reportName, mixed $configuration)
    {
        if (false === is_object($configuration)) {
            throw new AtPathException(
                sprintf('.reports.%s.storage.%s', $this->reportName, StorageIntegration::GITHUB_DISCUSSION->value),
                'object is expected'
            );
        }

        [$this->owner, $this->repository, $this->discussion] = $this->parseUrl($configuration);
        $this->report = $this->parseBool($configuration, 'report', true);
        $this->persist = $this->parseBool($configuration, 'persist', true);
    }

    /**
     * @return array{non-empty-string, non-empty-string, int<1, max>}
     */
    public function parseUrl(object $configuration): array
    {
        if (false === property_exists($configuration, 'url')) {
            throw new AtPathException(
                sprintf(
                    '.reports.%s.storage.%s',
                    $this->reportName,
                    StorageIntegration::GITHUB_DISCUSSION->value,
                ),
                'property "url" is mandatory'
            );
        }

        $url = $configuration->url;

        $path = sprintf(
            '.reports.%s.storage.%s.url',
            $this->reportName,
            StorageIntegration::GITHUB_DISCUSSION->value,
        );

        $message = 'invalid Github discussion url';

        if (false === is_string($url)) {
            throw new AtPathException($path, $message);
        }

        if (!preg_match('#^https://github\.com/(.+)/(.+)/discussions/(\d+)$#', $url, $matches)) {
            throw new AtPathException($path, $message);
        }

        [, $owner, $repository, $discussion] = $matches;

        if ('' === $owner || '' === $repository) {
            throw new AtPathException($path, $message);
        }

        $discussion = (int) $discussion;

        if (0 >= $discussion) {
            throw new AtPathException($path, $message);
        }

        return [$owner, $repository, $discussion];
    }

    private function parseBool(object $configuration, string $key, bool $default): bool
    {
        if (false === property_exists($configuration, $key)) {
            return $default;
        }

        $bool = $configuration->$key;

        if (false === is_bool($bool)) {
            throw new AtPathException(
                sprintf(
                    '.reports.%s.storage.%s.%s',
                    $this->reportName,
                    StorageIntegration::GITHUB_DISCUSSION->value,
                    $key
                ),
                'must be a boolean'
            );
        }

        return $bool;
    }
}
