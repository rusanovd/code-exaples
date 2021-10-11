<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Log;

use Exception;
use More\Log\LoggerFactory;
use Psr\Log\LoggerInterface;

class IntercomLoggerFactory
{
    private const LOGGER_CHANNEL_NAME = 'intercom';
    private const LOGGER_SCRIPT_CHANNEL_NAME = 'intercom_script';

    private LoggerFactory $loggerFactory;
    private LoggerFactory $loggerScriptFactory;

    public function __construct(LoggerFactory $loggerFactory, LoggerFactory $loggerScriptFactory)
    {
        $this->loggerFactory = $loggerFactory;
        $this->loggerScriptFactory = $loggerScriptFactory;
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getIntercomLogger(): LoggerInterface
    {
        return $this->loggerFactory->getLogger(static::LOGGER_CHANNEL_NAME);
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getIntercomScriptLogger(): LoggerInterface
    {
        return $this->loggerScriptFactory->getLogger(static::LOGGER_SCRIPT_CHANNEL_NAME);
    }
}
