<?php

namespace App\Services;

use App\Contracts\CircuitBreakerInterface;
use Illuminate\Support\Facades\Cache;

class CircuitBreaker implements CircuitBreakerInterface
{
    private $failureThreshold = 5;

    private $timeout = 60; // 1 minute

    private $halfOpenTimeout = 30; // 30 seconds

    public function __construct(private string $serviceName)
    {
        //
    }

    public function isAvailable(): bool
    {
        $state = $this->getState();

        return $state === 'CLOSED' || $state === 'HALF_OPEN';
    }

    public function markSuccess(): void
    {
        Cache::forget("circuit:{$this->serviceName}:failures");
        Cache::forget("circuit:{$this->serviceName}:state");
        Cache::forget("circuit:{$this->serviceName}:opened_at");
    }

    public function markFailure(): void
    {
        $failures = Cache::get("circuit:{$this->serviceName}:failures", 0) + 1;
        Cache::put("circuit:{$this->serviceName}:failures", $failures, 300); // 5 minutes

        if ($failures >= $this->failureThreshold) {
            Cache::put("circuit:{$this->serviceName}:state", 'OPEN', 300);
            Cache::put("circuit:{$this->serviceName}:opened_at", now(), 300);
        }
    }

    private function getState()
    {
        $state = Cache::get("circuit:{$this->serviceName}:state", 'CLOSED');

        if ($state === 'OPEN') {
            $openedAt = Cache::get("circuit:{$this->serviceName}:opened_at");
            if ($openedAt && now()->diffInSeconds($openedAt) > $this->timeout) {
                $state = 'HALF_OPEN';
                Cache::put("circuit:{$this->serviceName}:state", $state, 300);
            }
        }

        return $state;
    }

    public function getStats()
    {
        return [
            'service' => $this->serviceName,
            'state' => $this->getState(),
            'failures' => Cache::get("circuit:{$this->serviceName}:failures", 0),
            'is_available' => $this->isAvailable(),
        ];
    }
}
