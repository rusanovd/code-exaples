<?php

declare(strict_types=1);

namespace More\Amo\Services;

class AmoRequestDelayService
{
    private const RPS_LIMIT = 7;

    /**
     * @var int
     */
    private $requestCount;

    /**
     * @var float
     */
    private $requestTime;

    /**
     * AmoRequestDelayService constructor.
     */
    public function __construct()
    {
        $this->requestCount = 0;
        $this->requestTime = microtime(true);
    }

    public function markRequest(): void
    {
        $this->setRequestCount();
        $this->setRequestTime();
        $this->processDelay();
    }

    /**
     * RPS limit - 7 requests in 1 second
     */
    private function processDelay(): void
    {
        if (($this->getRequestCount() > 0) && ($this->getRequestCount() % self::RPS_LIMIT === 0)) {
            $now = microtime(true);
            if (($now - $this->getRequestTime()) < 1) {
                $delay = (int) ceil(1E6 * (1 - $now + $this->getRequestTime()));
                usleep($delay);
            }
        }
    }

    /**
     * @return float
     */
    private function getRequestTime(): float
    {
        return $this->requestTime;
    }

    private function setRequestTime(): void
    {
        $this->requestTime = microtime(true);
    }

    /**
     * @return int
     */
    private function getRequestCount(): int
    {
        return $this->requestCount;
    }

    private function setRequestCount(): void
    {
        $this->requestCount++;
    }
}
