<?php

declare(strict_types=1);

namespace K_pi\Configuration\Extractor;

use K_pi\Configuration;
use K_pi\Configuration\Exception\AtPathException;
use K_pi\Configuration\Extractor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

final class YamlFileExtractor implements Extractor
{
    private const EXTENSIONS = [
        '.dist.yaml',
        '.dist.yml',
        '.yaml',
        '.yaml.dist',
        '.yml',
        '.yml.dist',
    ];

    public function extract(InputInterface $input): ?Configuration
    {
        if (false === $input->hasOption('configuration-file')) {
            return null;
        }

        if (false === is_string($configurationFilePath = $input->getOption('configuration-file'))) {
            return null;
        }

        $supported = false;

        foreach (self::EXTENSIONS as $extension) {
            if (true === $supported) {
                continue;
            }

            $supported = str_ends_with($configurationFilePath, $extension);
        }

        if (false === $supported) {
            return null;
        }

        $yaml = Yaml::parseFile($configurationFilePath);
        $json = json_decode(
            json_encode(
                $yaml,
                flags: JSON_THROW_ON_ERROR,
            ),
            false,
            flags: JSON_THROW_ON_ERROR,
        );

        if (false === is_object($json)) {
            throw new AtPathException('.', 'configuration root must be an object');
        }

        return new Configuration($json);
    }
}
