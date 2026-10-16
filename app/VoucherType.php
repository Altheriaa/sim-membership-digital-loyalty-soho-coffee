<?php

namespace App;

enum VoucherType: string
{
    case FreeItem = 'free_item';
    case DiscountAmount = 'discount_amount';

    public function label(): string
    {
        return match ($this) {
            self::FreeItem => 'Item gratis',
            self::DiscountAmount => 'Diskon nominal',
        };
    }
}
