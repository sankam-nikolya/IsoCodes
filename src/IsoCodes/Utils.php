<?php

namespace IsoCodes;

/**
 * Utils.
 */
class Utils
{
    /**
     * @param string $input
     * @param array  $hyphens
     */
    public static function unDecorate($input, $hyphens = []): string
    {
        $hyphensLength = count($hyphens);
        // removing hyphens
        for ($i = 0; $i < $hyphensLength; ++$i) {
            $input = str_replace($hyphens[$i], '', $input);
        }

        return $input;
    }

    public static function Luhn(string $value, int $length, int $weight, int $divider, $hyphens): bool
    {
        $value = self::unDecorate($value, $hyphens);
        $digits = substr($value, 0, $length - 1);
        $check = substr($value, $length - 1, 1);
        $expr = sprintf('/\\d{%d}/i', $length);
        if (!preg_match($expr, $value)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < strlen($digits); ++$i) {
            if (0 === $i % 2) {
                $add = (int) substr($digits, $i, 1);
            } else {
                $add = $weight * (int) substr($digits, $i, 1);
                if (10 <= $add) { // '18' = 1+8 = 9, etc.
                    $strAdd = strval($add);
                    $add = intval($strAdd[0]) + intval($strAdd[1]);
                }
            }
            $sum += $add;
        }

        return 0 === ($sum + $check) % $divider;
    }

    public static function LuhnforGTIN($value, $length, $unDecorate = true, $hyphens = []): bool
    {
        $value = $unDecorate ? self::unDecorate($value, $hyphens) : $value;
        $divider = 10;
        $multiplier = 3;

        if (strlen($value) != $length) {
            return false;
        }
        $expr = sprintf('/\\d{%d}/i', $length);
        if (!preg_match($expr, $value)) {
            return false;
        }
        if (0 === (int) $value) {
            return false;
        }

        $sum = 0;
        // the multiplier get applied differently (even or odd) according the value length
        // see https://blog.datafeedwatch.com/hubfs/blog/calculate-14-digit-gtin.png
        for ($i = 0; $i < $length; $i += 2) {
            if (0 === $length % 2) {
                $sum += $multiplier * (int) substr($value, $i, 1);
                $sum += (int) substr($value, $i + 1, 1);
            } else {
                $sum += (int) substr($value, $i, 1);
                $sum += $multiplier * (int) substr($value, $i + 1, 1);
            }
        }

        return 0 === $sum % $divider;
    }

    public static function LuhnWithWeights(string $value, int $length, array $weights, int $divider, $hyphens): bool
    {
        $value = self::unDecorate($value, $hyphens);
        $digits = substr($value, 0, $length - 1);
        $check = substr($value, $length - 1, 1);
        $expr = sprintf('/\\d{%d}/i', $length);
        if (!preg_match($expr, $value)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < strlen($digits); ++$i) {
            if (!is_numeric($digits[$i])) {
                return false;
            }
            $sum += $weights[$i] * intval($digits[$i]);
        }

        $rest = $sum % $divider;

        if (0 === $rest) {
            $check = $divider;
        }

        return intval($check) === $divider - $rest;
    }
}
