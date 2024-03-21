<?php

declare(strict_types=1);

namespace K_pi\Container;

use Github;
use Github\Api\GraphQL;
use Github\AuthMethod;
use K_pi\Command\Compile;
use K_pi\Configuration\Extractor;
use K_pi\Configuration\Extractor\StrategyExtractor;
use K_pi\Configuration\Extractor\YamlFileExtractor;
use K_pi\Container;
use K_pi\EnvVars;
use K_pi\Integration\Github\Discussion\Storage\Factory;
use K_pi;
use K_pi\Integration\Github\Discussion\Storage\Factory as Factory2;
use K_pi\Integration\Github\Variables;
use K_pi\Integrations;
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

        $container->define(
            K_pi\Storage\Integrations::class,
            fn (Container $container) => new K_pi\Storage\Integrations(
                $container->get(K_pi\Integration\Github\Discussion\Storage\Factory::class)
            )
        );

        $container->define(
            K_pi\CheckReporter\Integrations::class,
            fn (Container $container) => new K_pi\CheckReporter\Integrations(
                $container->get(K_pi\Integration\Github\CheckRun\CheckReporter\Factory::class),
                $container->get(K_pi\Integration\Github\Status\CheckReporter\Factory::class),
            )
        );

        self::buildCLI($container);
        self::buildExtractor($container);
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
                    $container->get(K_pi\Command\CompileCommand::class),
                    $container->get(K_pi\Command\CheckCommand::class),
                ]);

                return $application;
            }
        );

        $container->define(
            K_pi\Command\CompileCommand::class,
            fn (Container $container) => new K_pi\Command\CompileCommand(
                $container->get(K_pi\Configuration\Extractor::class),
                $container->get(K_pi\Storage\Integrations::class),
            ),
        );

        $container->define(
            K_pi\Command\CheckCommand::class,
            fn (Container $container) => new K_pi\Command\CheckCommand(
                $container->get(K_pi\CheckReporter\Integrations::class),
                $container->get(K_pi\Configuration\Extractor::class),
                $container->get(K_pi\Storage\Integrations::class),
            ),
        );
    }

    private static function buildGithub(Container $container): void
    {
        $container->define(
            K_pi\Integration\Github\Variables::class,
            fn (Container $container) => new K_pi\Integration\Github\Variables(
                $container->get(K_pi\EnvVars::class)
            )
        );

        $container->define(
            Github\Client::class,
            function (Container $container) {
                $client = new Github\Client();
                $client->authenticate(
                    $container
                        ->get(K_pi\Integration\Github\Variables::class)
                        ->getToken(),
                    Github\AuthMethod::ACCESS_TOKEN,
                );

                return $client;
            }
        );

        $container->define(
            K_pi\Libs\KnpGithubApi\Github::class,
            fn (Container $container) => new K_pi\Libs\KnpGithubApi\Github(
                $container->get(
                    Github\Client::class,
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

        $container->define(
            K_pi\Integration\Github\CheckRun\CheckReporter\Factory::class,
            fn (Container $container) => new K_pi\Integration\Github\CheckRun\CheckReporter\Factory(
                $container->get(K_pi\Integration\Github::class),
                $container->get(K_pi\Integration\Github\Variables::class),
            ),
        );

        $container->define(
            K_pi\Integration\Github\Status\CheckReporter\Factory::class,
            fn (Container $container) => new K_pi\Integration\Github\Status\CheckReporter\Factory(
                $container->get(K_pi\Integration\Github::class),
                $container->get(K_pi\Integration\Github\Variables::class),
            ),
        );
    }

    private static function buildExtractor(Container $container): void
    {
        $container->define(
            K_pi\Configuration\Extractor::class,
            fn (Container $container) => $container->get(K_pi\Configuration\Extractor\StrategyExtractor::class)
        );

        $container->define(
            K_pi\Configuration\Extractor\StrategyExtractor::class,
            fn (Container $container) => new K_pi\Configuration\Extractor\StrategyExtractor(
                $container->get(K_pi\Configuration\Extractor\YamlFileExtractor::class)
            )
        );

        $container->define(
            K_pi\Configuration\Extractor\YamlFileExtractor::class,
            fn () => new K_pi\Configuration\Extractor\YamlFileExtractor()
        );
    }
}
