<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Indonesian mobile numbers in the stored form: 62, then 8, then 8 to 12 more digits.
     */
    public const PATTERN = '/^628\d{8,12}$/';

    /**
     * Bring the ways a cashier types a WhatsApp number (0812..., +62 812..., 812...) to one stored form, 62812....
     * Input that cannot be read as a number comes back with only its digits, so validation can reject it.
     */
    public static function normalize(?string $input): string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        return match (true) {
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    public static function isValid(string $normalized): bool
    {
        return preg_match(self::PATTERN, $normalized) === 1;
    }

    /**
     * The local form a person reads, grouped by four: 0812-3456-7890.
     */
    public static function display(string $normalized): string
    {
        $local = '0'.substr($normalized, 2);

        return implode('-', str_split($local, 4));
    }

    /**
     * The form printed on paper receipts, hiding the middle digits: 0812****7890.
     */
    public static function masked(string $normalized): string
    {
        $local = '0'.substr($normalized, 2);

        return substr($local, 0, 4).str_repeat('*', max(strlen($local) - 8, 0)).substr($local, -4);
    }
}
