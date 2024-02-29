<?php

declare(strict_types=1);

namespace K_pi\Command;

use Assert\Assert;
use K_pi\EnvVars;
use K_pi\Storage;
use K_pi\Storage\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

final class Compile extends Command
{
    /**
     * @param array<string, Factory<Storage>> $storageFactories
     */
    public function __construct(private readonly array $storageFactories, private readonly EnvVars $envVars)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('compile')
            ->addArgument('configuration_file', InputArgument::REQUIRED)
            ->addArgument('report_name', InputArgument::REQUIRED)
            ->addArgument('values', InputArgument::REQUIRED)
            ->addOption('github-token', mode: InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureGithub($input);

        $configuration_file = $this->readArgument($input, 'configuration_file');

        if (false === file_exists($configuration_file)) {
            throw new \Exception("Configuration file $configuration_file not found.");
        }

        $configuration = Yaml::parseFile($configuration_file);

        Assert::that($configuration)->isArray();

        $report_name = $this->readArgument($input, 'report_name');

        if (false === isset($configuration['reports'][$report_name])) {
            throw new \Exception("Report named $report_name not found.");
        }

        $reportConfiguration = $configuration['reports'][$report_name];
        $storage = $this->getStorage($reportConfiguration);
        $report = $storage->read();
        $hash = $report->hash();
        $values = $this->getValues($input);
        $now = new \DateTimeImmutable();

        foreach ($values as $name => $value) {
            $report->add($name, $now, $value);
        }

        foreach ($this->getColorSet($reportConfiguration) as $name => $color) {
            $report->colorize($name, $color);
        }

        var_dump($report);

        if ($hash !== $report->hash()) {
            $storage->write($report);
        }

        return self::SUCCESS;
    }

    private function configureGithub(InputInterface $input): void
    {
        if (

            false === $input->hasOption('github-token')
        ) {
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

    private function getStorage(mixed $configuration): Storage
    {
        Assert::that($configuration)->isArray()->keyExists('storage');
        Assert::that($configuration['storage'])->isArray();

        foreach ($configuration['storage'] as $storageName => $storageConfiguration) {
            Assert::that($this->storageFactories)->keyExists($storageName);

            return $this->storageFactories[$storageName]->build($storageConfiguration);
        }

        throw new \Exception('No storage configured');
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

    /**
     * @return iterable<non-empty-string, non-empty-string>
     */
    private function getColorSet(mixed $configuration): iterable
    {
        Assert::that($configuration)->isArray();

        if (false === array_key_exists('colors', $configuration)) {
            return;
        }

        $colors = $configuration['colors'];

        Assert::that($colors)->isArray();

        foreach ($colors as $name => $color) {
            if (false === is_string($name) || '' === $name) {
                throw new \Exception;
            }

            if (false === is_string($color) || '' === $color) {
                throw new \Exception;
            }

            yield $name => $color;
        }
    }
}
