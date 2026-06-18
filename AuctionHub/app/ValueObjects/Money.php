<?php

namespace App\ValueObjects;

class Money
{
    public function __construct(
        private float $amount,
        private string $currency = 'USD'
    ) {}

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
        return new Money($this->amount + $other->amount, $this->currency);
    }

    public function isGreaterThanOrEqual(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
        return $this->amount >= $other->amount;
    }

    public function __toString()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }
}
