<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use App\ValueObjects\Money;

class MoneyCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return new Money($value / 100, 'USD');
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Money) {
            return $value->getAmount() * 100;
        }
        return $value * 100;
    }
}
