<?php

namespace K_pi\Integration;

interface Github
{
    /**
     * @return array{id: string, body: string}
     */
    public function readDiscussion(
        string $owner,
        string $repository,
        int $number,
    ): array;

    public function writeDiscussion(
        string $id,
        string $body,
    ): void;
}
