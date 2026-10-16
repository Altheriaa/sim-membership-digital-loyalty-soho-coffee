<?php

namespace App\Support;

class Rupiah
{
    /**
     * Format a whole-rupiah amount, for example 25000 becomes "Rp 25.000".
     */
    public static function format(int|float|string $amount): string
    {
        $rounded = (int) round((float) $amount);

        return ($rounded < 0 ? '-' : '').'Rp '.number_format(abs($rounded), 0, ',', '.');
    }
}
