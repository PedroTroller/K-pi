<?php

declare(strict_types=1);

namespace K_pi\Container;

use Github;
use Github\Api\GraphQL;
use K_pi\Command\Compile;
use K_pi\Container;
use K_pi\EnvVars;
use K_pi\Integration\Github\Discussion\Storage\Factory;
use K_pi;
use Symfony;
use Symfony\Component\Console\Application;

final class Definitions
{
    public static function build(): K_pi\Container
    {
        $container = new K_pi\Container();

        $container->define(
            K_pi\EnvVars::class,
            fn () => new K_pi\EnvVars(),
        );

        self::buildCLI($container);
        self::buildGithub($container);

        return $container;
    }

    private static function buildCLI(Container $container): void
    {
        $container->define(
            Symfony\Component\Console\Application::class,
            function (Container $container) {
                $application = new Symfony\Component\Console\Application();
                $application->addCommands([
                    $container->get(K_pi\Command\Compile::class),
                ]);

                return $application;
            }
        );

        $container->define(
            K_pi\Command\Compile::class,
            fn (Container $container) => new K_pi\Command\Compile(
                [
                    'github-discussion' => $container->get(
                        K_pi\Integration\Github\Discussion\Storage\Factory::class,
                    ),
                ],
                $container->get(K_pi\EnvVars::class),
            ),
        );

    }

    private static function buildGithub(Container $container): void
    {
        $container->define(
            Github\Client::class,
            function (Container $container) {
                $client = new Github\Client();
                $client->authenticate(
                    $container
                        ->get(K_pi\EnvVars::class)
                        ->get('GITHUB_TOKEN'),
                    Github\Client::AUTH_ACCESS_TOKEN,
                );

                return $client;
            }
        );

        $container->define(
            Github\Api\GraphQL::class,
            fn (Container $container) => $container->get(Github\Client::class)->graphql(),
        );

        $container->define(
            K_pi\Libs\KnpGithubApi\Github::class,
            fn (Container $container) => new K_pi\Libs\KnpGithubApi\Github(
                $container->get(
                    Github\Api\GraphQL::class,
                )
            ),
        );

        $container->define(
            K_pi\Libs\Lazy\Github::class,
            fn (Container $container) => new K_pi\Libs\Lazy\Github(
                fn () => $container->get(K_pi\Libs\KnpGithubApi\Github::class),
            ),
        );

        $container->define(
            K_pi\Integration\Github::class,
            fn (Container $container) => $container->get(K_pi\Libs\Lazy\Github::class)
        );

        $container->define(
            K_pi\Integration\Github\Discussion\Storage\Factory::class,
            fn (Container $container) => new K_pi\Integration\Github\Discussion\Storage\Factory(
                $container->get(K_pi\Integration\Github::class),
            ),
        );
    }
}
