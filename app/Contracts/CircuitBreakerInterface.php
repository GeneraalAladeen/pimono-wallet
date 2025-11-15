<?php

namespace App\Contracts;

interface CircuitBreakerInterface
{
    public function markSuccess(): void;

    public function markFailure(): void;

    public function isAvailable(): bool;
}
