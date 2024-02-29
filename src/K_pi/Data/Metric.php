<?php

declare(strict_types=1);

namespace K_pi\Data;

final class Metric
{
    /**
     * @var array{non-empty-string, array{non-empty-string, float|int}}
     */
    private array $datasets = [];

    public function add(string $name, \DateTimeImmutable $date, int|float $value): void
    {
        $index = $date->format('Y-m-d');

        $this->datasets[$name][$index] = $value;

        ksort($this->datasets[$name]);

        $current = null;

        foreach ($this->datasets[$name] as $date => $value) {
            if ($value === $current) {
                unset($this->datasets[$name][$date]);
            }

            $current = $value;
        }
    }

    public function total(): array
    {
        $totals = [];

        foreach ($this->datasets as $name => $dataset) {
            foreach ($dataset as $date => $value) {
                $totals[$date][$name] = $value;
            }
        }

        ksort($totals);

        $current = [];

        foreach ($totals as $date => $total) {
            $current = $totals[$date] = array_merge($current, $total);
        }

        return array_map(
            array_sum(...),
            $totals,
        );
    }

    /**
     * @return array{non-empty-string, array{non-empty-string, float|int}}
     */
    public function dump()
    {
        $datasets = $this->datasets;
    }
}
