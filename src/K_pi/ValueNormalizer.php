<?php

declare(strict_types=1);

namespace K_pi;

final class ValueNormalizer
{
    public static function normalize(int|float $value, int $precision): int|float
    {
        if (0 == $value) {
            return 0;
        }

        $value = round($value, $precision);

        if ((int) $value == $value) {
            return (int) $value;
        }

        return $value;
    }
}
