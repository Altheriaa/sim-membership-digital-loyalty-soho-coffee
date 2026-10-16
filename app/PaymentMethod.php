<?php

namespace App;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case NonCash = 'non_cash';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::NonCash => 'Non-tunai',
        };
    }
}
