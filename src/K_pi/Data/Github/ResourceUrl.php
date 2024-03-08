<?php

declare(strict_types=1);

namespace K_pi\Data\Github;

use InvalidArgumentException;

final class ResourceUrl
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
     * @var non-empty-string
     */
    public readonly string $type;

    /**
     * @var positive-int
     */
    public readonly int $number;

    /**
     * @throw InvalidArgumentException
     */
    public function __construct(string $url)
    {
        if (!preg_match('#^https://github\.com/(.+)/(.+)/(\w+)/(\d+)$#', $url, $matches)) {
            throw new InvalidArgumentException('Invalid Github resource url.');
        }

        [, $owner, $repository, $type, $number] = $matches;

        if ('' === $owner) {
            throw new InvalidArgumentException('Github resource owner is empty.');
        }

        $this->owner = $owner;

        if ('' === $repository) {
            throw new InvalidArgumentException('Github resource repository is empty.');
        }

        $this->repository = $repository;

        if ('' === $type) {
            throw new InvalidArgumentException('Github resource type is empty.');
        }

        $this->type = $type;

        if (false === is_numeric($number)) {
            throw new InvalidArgumentException('Github resource number is not a positive integer.');
        }

        $number = (int) $number;

        if (0 >= $number) {
            throw new InvalidArgumentException('Github resource number is not a positive integer.');
        }

        $this->number = $number;
    }
}
