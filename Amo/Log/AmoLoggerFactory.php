<?php

namespace More\Amo\Log;

use Exception;
use More\Log\LoggerFactory;
use Psr\Log\LoggerInterface;

class AmoLoggerFactory
{
    private LoggerFactory $loggerFactory;

    /**
     * SipuniLoggerFactory constructor.
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getAmoLogger(): LoggerInterface
    {
        return $this->loggerFactory->getLogger('amocrm');
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getAmoScriptLogger(): LoggerInterface
    {
        return $this->loggerFactory->getLogger('amo_sync_script');
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getAmoWebhookUserResponsibleLogger(): LoggerInterface
    {
        return $this->loggerFactory->getLogger('amo_sync_responsible');
    }

    /**
     * @return LoggerInterface
     * @throws Exception
     */
    public function getAmoWebhookStatusConsultingLogger(): LoggerInterface
    {
        return $this->loggerFactory->getLogger('amo_sync_status_consulting');
    }
}
