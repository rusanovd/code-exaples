<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Log;

use Exception;
use More\Log\LoggerFactory;
use Psr\Log\LoggerInterface;

class SmartBoxLoggerFactory
{
    private LoggerFactory $loggerFactory;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getLoggerApi(): LoggerInterface
    {
        return $this->loggerFactory->getLogger('smart_box_send_data');
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getLoggerWebhook(): LoggerInterface
    {
        return $this->loggerFactory->getLogger('smart_box_webhook_data');
    }
}
