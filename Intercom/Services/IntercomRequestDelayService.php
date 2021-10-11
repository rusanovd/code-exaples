<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

class IntercomRequestDelayService
{
    private const RPS_LIMIT = 15;

    private int $requestCount;
    private float $requestTime;

    public function __construct()
    {
        $this->requestCount = 0;
        $this->requestTime = microtime(true);
    }

    private function setRequestTime(): void
    {
        $this->requestTime = microtime(true);
    }

    private function setRequestCount(): void
    {
        $this->requestCount++;
    }

    private function processDelay(): void
    {
        if ($this->requestCount > 0 && ($this->requestCount % self::RPS_LIMIT === 0)) {
            $now = microtime(true);
            if (($now - $this->requestTime) < 1) {
                $delay = (int) ceil(1E6 * (1 - $now + $this->requestTime));
                usleep($delay);
            }
        }
    }

    public function markRequest(): void
    {
        $this->setRequestCount();
        $this->setRequestTime();
        $this->processDelay();
    }
}
