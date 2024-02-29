<?php

declare(strict_types=1);

namespace K_pi\Integration\Github\Discussion;

use DateTimeImmutable;
use K_pi\Data\Report;
use K_pi\Integration\Github;
use K_pi\Storage as StorageInterface;
use QuickChart;

/**
 * @phpstan-type Data array<
 *   non-empty-string,
 *   array{
 *      color: ?non-empty-string,
 *      dataset: array<non-empty-string, int|float>
 *   }
 * >
 */
final class Storage implements StorageInterface
{
    public function __construct(
        private readonly string $owner,
        private readonly string $repository,
        private readonly int $discussion,
        private readonly bool $report,
        private readonly bool $persist,
        private readonly Github $github
    ) {
    }

    public function read(): Report
    {
        if (false === $this->persist) {
            return new Report();
        }

        $discussion = $this->github->readDiscussion(
            owner: $this->owner,
            repository: $this->repository,
            number: $this->discussion,
        );

        return $this->contentToReport($discussion['body']);
    }

    public function write(Report $report): void
    {
        $discussion = $this->github->readDiscussion(
            owner: $this->owner,
            repository: $this->repository,
            number: $this->discussion,
        );

        $this->github->writeDiscussion(
            id: $discussion['id'],
            body: $this->reportToContent($report),
        );
    }

    private function contentToReport(string $content): Report
    {
        $inJson  = false;
        $extract = [];

        foreach (explode("\n", $content) as $line) {
            if (trim($line, " \n\r") === '```json') {
                $inJson = true;

                continue;
            }

            if (trim($line, " \n\r") === '```') {
                $inJson = false;

                continue;
            }

            if ($inJson) {
                $extract[] = $line;
            }
        }

        if ($extract === []) {
            return new Report();
        }


        try {
            $export = json_decode(
                implode("\n", $extract),
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            if (false === is_array($export)) {
                return new Report();
            }

            $data = [];

            foreach ($export as $name => $datasetAndColor) {
                if (false === is_string($name)) {
                    continue;
                }

                if ('' === $name) {
                    continue;
                }

                if (false === is_array($datasetAndColor)) {
                    continue;
                }

                $dataset = $datasetAndColor['dataset'] ?? [];

                if (false === is_array($dataset) || [] === $dataset) {
                    continue;
                }

                foreach ($dataset as $date => $value) {
                    if (false === is_int($value) && false === is_float($value)) {
                        continue;
                    }

                    if (false === is_string($date) || '' === $date) {
                        continue;
                    }

                    $data[$name]['dataset'][$date] = $value;
                    $data[$name]['color'] = $datasetAndColor['color'] ?? null;
                }
            }

            return $this->dataToReport($data);
        } catch(\Exception) {
            return new Report();
        }
    }

    private function reportToContent(Report $report): string
    {
        $data = null;
        $chart = null;

        if ($this->persist) {
            $data = $this->reportToData($report);
        }

        if ($this->report) {
            $chart = $this->reportToChart($report);
        }

        if (null === $data && null !== $chart) {
            $template = <<<'MKD'
            ![](%s)
            MKD;

            return sprintf($template, $chart->getShortUrl());
        }

        if (null !== $data && null === $chart) {
            $template = <<<'MKD'
            ```json
            %s
            ```
            MKD;

            return sprintf(
                $template,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            );
        }

        if (null !== $data && null !== $chart) {
            $template = <<<'MKD'
            ![](%s)

            <details>
              <summary>Data (do not edit)</summary>

              ```json
            %s
              ```
            </details>
            MKD;

            return sprintf(
                $template,
                $chart->getShortUrl(),
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            );
        }

        return '';
    }

    /**
     * @param Data $data
     */
    private function dataToReport(array $data): Report
    {
        $report = new Report();

        foreach ($data as $name => $datasetAndColor) {
            $color = $datasetAndColor['color'];
            $dataset = $datasetAndColor['dataset'];

            if (null !== $color) {
                $report->colorize($name, $color);
            }

            foreach ($dataset as $date => $value) {
                $report->add($name, new DateTimeImmutable($date), $value);
            }
        }

        return $report;
    }

    /**
     * @return Data
     */
    private function reportToData(Report $report): array
    {
        $data = [];

        foreach ($report as $name => $dataset) {
            $data[$name] = [
                'color' => $report->getColor($name),
                'dataset' => $dataset,
            ];
        }

        return $data;
    }

    private function reportToChart(Report $report): QuickChart
    {
        $config = [
            'type'    => 'line',
            'options' => [
                'scales' => [
                    'xAxes' => [
                        [
                            'type' => 'time',
                            'time' => [
                                'parser' => 'YYYY/MM/DD',
                            ],
                        ],
                    ],
                    'yAxes' => [
                        [
                            'ticks' => [
                                'min' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            'data' => [
                'datasets' => []
            ]
        ];

        foreach ($report as $name => $dataset) {
            $color = $report->getColor($name);

            $config['data']['datasets'][] = array_merge(
                [
                'label' => $name,
                'fill' => false,
                'data' => array_map(
                    fn (string $date, int|float $value) => ['x' => $date, 'y' => $value],
                    array_keys($dataset),
                    array_values($dataset),
                ),
            ],
                $color === null ? [] : ['borderColor' => $color]
            );
        }

        $chart = new \QuickChart([
            'width'   => 870,
            'height'  => 600,
            'version' => '2',
        ]);

        $chart->setConfig($config);

        return $chart;
    }
}
