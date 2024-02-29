<?php

declare(strict_types=1);

namespace K_pi\Libs\Lazy;

use K_pi;
use K_pi\Libs\Lazy;

/**
 * @extends Lazy<K_pi\Libs\KnpGithubApi\Github>
 */
final class Github extends Lazy implements K_pi\Integration\Github
{
    public function readDiscussion(
        string $owner,
        string $repository,
        int $number,
    ): array {
        return $this->load()->readDiscussion($owner, $repository, $number);
    }

    public function writeDiscussion(
        string $id,
        string $body,
    ): void {
        $this->load()->writeDiscussion($id, $body);
    }
}
