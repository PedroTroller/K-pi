<?php

declare(strict_types=1);

namespace K_pi\Libs\KnpGithubApi;

use Github\Api\GraphQL;
use K_pi;

final class Github implements K_pi\Integration\Github
{
    public function __construct(private readonly GraphQL $github)
    {
    }

    public function readDiscussion(
        string $owner,
        string $repository,
        int $number
    ): array {
        $response = $this->github->execute(
            <<<'GRAPHQL'
                query($owner: String!, $repository: String!, $number: Int!) {
                  repository(owner: $owner, name: $repository) {
                    discussion(number: $number) {
                      id
                      body
                    }
                  }
                }
                GRAPHQL,
            [
                'owner' => $owner,
                'repository' => $repository,
                'number' => $number,
            ]
        );

        if (isset($response['errors'])) {
            throw new \Exception(json_encode($response['errors'], JSON_THROW_ON_ERROR));
        }

        if (\is_string($id = $response['data']['repository']['discussion']['id'] ?? null) === false) {
            throw new \Exception('Unable to get discussion ID from '.json_encode($response, JSON_THROW_ON_ERROR));
        }

        if (\is_string($body = $response['data']['repository']['discussion']['body'] ?? null) === false) {
            throw new \Exception('Unable to get discussion body from '.json_encode($response, JSON_THROW_ON_ERROR));
        }

        return ['id' => $id, 'body' => $body];
    }

    public function writeDiscussion(
        string $id,
        string $body,
    ): void {
        $response = $this->github->execute(
            <<<'GRAPHQL'
                mutation($id: ID!, $body: String!) {
                  updateDiscussion(input: {discussionId: $id, body: $body}) {
                    clientMutationId
                  }
                }
                GRAPHQL,
            [
                'id'   => $id,
                'body' => $body
            ]
        );

        if (isset($response['errors'])) {
            throw new \Exception(json_encode($response['errors'], JSON_THROW_ON_ERROR));
        }
    }
}
