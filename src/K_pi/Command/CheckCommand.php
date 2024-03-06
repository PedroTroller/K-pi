<?php

declare(strict_types=1);

namespace K_pi\Command;

use Assert\Assert;
use K_pi\Configuration\Extractor;
use K_pi\Data\Diff;
use K_pi\Data\Integration;
use K_pi\Data\StorageIntegration;
use K_pi\EnvVars;
use K_pi\Integrations;
use K_pi\Storage;
use K_pi\CheckReporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckCommand extends Command
{
    public function __construct(
        private readonly Storage\Integrations $storageIntegrations,
        private readonly CheckReporter\Integrations $checkReporterIntegrations,
        private readonly EnvVars $envVars,
        private Extractor $extractor
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('check')
            ->addArgument('report-name', InputArgument::REQUIRED)
            ->addArgument('values', InputArgument::REQUIRED)
            ->addOption('configuration-file', mode: InputOption::VALUE_OPTIONAL)
            ->addOption('github-token', mode: InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureGithub($input);

        $reportName = $this->readArgument($input, 'report-name');

        if ('' === $reportName) {
            throw new \Exception('"report-name" argument can\'t be an empty string.');
        }

        $configuration = $this->extractor->extract($input);

        if (null === $configuration) {
            throw new \Exception("Unable to read configuration, no configuration file found.");
        }

        $reportConfiguration = $configuration->get($reportName);
        $storage = $this->getStorage($reportName, ...$reportConfiguration->getStorageConfiguration());
        $report = $storage->read();
        $values = $this->getValues($input);

        $notifications = array_map(
            fn (int|float $value, string $name): Diff => new Diff(
                name: $name,
                from: $report->last($name) ?? 0,
                to: $value,
            ),
            array_values($values),
            array_keys($values),
        );

        if ([] === $notifications) {
            return self::SUCCESS;
        }

        foreach ($reportConfiguration->getCheckReportersConfiguration() as $checkReporterIntegration => $checkReporterConfiguration) {
            $this
                ->checkReporterIntegrations
                ->get($checkReporterIntegration)
                ->build($checkReporterConfiguration, $reportName)
                ->send(...$notifications)
            ;
        }

        return self::SUCCESS;
    }

    private function configureGithub(InputInterface $input): void
    {
        if (false === $input->hasOption('github-token')) {
            return;
        }


        if (false === is_string(
            $option = $input->getOption('github-token')
        )) {
            return;
        }

        $this->envVars->default('GITHUB_TOKEN', $option);
    }

    private function readArgument(InputInterface $input, string $argumentName): string
    {
        $argument = $input->getArgument($argumentName);

        Assert::that($argument)->string();

        return $argument;
    }

    private function getStorage(string $reportName, StorageIntegration $integration, mixed $configuration): Storage
    {
        return $this->storageIntegrations->get($integration)->build($reportName, $configuration);
    }

    /**
     * @return array<non-empty-string, int|float>
     */
    private function getValues(InputInterface $input): array
    {
        $values = json_decode($this->readArgument($input, 'values'), true, flags: JSON_THROW_ON_ERROR);

        Assert::that($values)->isArray();
        Assert::that(array_keys($values))->all()->string()->notEmpty();

        return array_map(
            function ($value): int|float {
                if (is_int($value) || is_float($value)) {
                    return $value;
                }

                if (is_string($value) && is_numeric($value)) {
                    return (float) $value;
                }

                throw new \Exception(
                    sprintf(
                        "%s is not an integer, a float or a numeric string.",
                        match(gettype($value)) {
                            'string' => sprintf('"%s"', addslashes($value)),
                            'boolean' => $value ? 'true' : 'false',
                            default => gettype($value),
                        }
                    )
                );
            },
            $values,
        );
    }
}
