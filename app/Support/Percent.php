<?php

namespace App\Support;

class Percent
{
    /**
     * A percentage as a person reads it in Indonesian: 11.50 becomes "11,5", 10.00 becomes "10".
     */
    public static function format(int|float|string $value): string
    {
        $trimmed = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return str_replace('.', ',', $trimmed);
    }
}
