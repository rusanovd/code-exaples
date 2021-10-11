<?php

declare(strict_types=1);

namespace More\Amo\Services;

use More\Amo\Data\AmoConsultingStatus;
use More\Amo\Data\AmoResponsible;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Log\AmoLoggerFactory;
use Psr\Log\LoggerInterface;

class AmoLoggerService
{
    private LoggerInterface $logger;
    private LoggerInterface $loggerScript;
    private LoggerInterface $loggerWebhookUser;
    private LoggerInterface $loggerWebhookStatus;

    public function __construct(
        AmoLoggerFactory $amoLoggerFactory
    ) {
        $this->logger = $amoLoggerFactory->getAmoLogger();
        $this->loggerScript = $amoLoggerFactory->getAmoScriptLogger();
        $this->loggerWebhookUser = $amoLoggerFactory->getAmoWebhookUserResponsibleLogger();
        $this->loggerWebhookStatus = $amoLoggerFactory->getAmoWebhookStatusConsultingLogger();
    }

    public function log(string $message, array $data = []): void
    {
        $this->logger->info($message, $data);
    }

    public function logScript(string $message, array $data = []): void
    {
        $this->loggerScript->info('AmoScript' . "\n" . $message, $data);
    }

    public function logScriptException(\Exception $e, array $data = []): void
    {
        $this->loggerScript->info('AmoScript' . "\n" . $e->getMessage(), ['exception' => $e, 'data' => $data]);
    }

    public function logApi(string $method, int $id, array $data = []): void
    {
        $this->logger->info('Amo integration' . "\n\n" . $method, [
            'amoId' => $id,
            'data'  => $data,
        ]);
    }

    public function logAccess(int $userId, int $salonId, bool $isMasterOnlyAccess, array $extendedAccess): void
    {
        $this->logger->info('hasMasterOnlyAccess', [
            'userId'               => $userId,
            'salonId'              => $salonId,
            'isMasterOnlyAccess'   => $isMasterOnlyAccess,
            'userAccess'           => json_encode($extendedAccess),
        ]);
    }

    public function logExceptionError(\Exception $e, array $data = []): void
    {
        $this->logger->info('Amo integration error' . "\n\n" . $e->getMessage(), ['exception' => $e, 'data' => $data]);
    }

    public function logApiResult(AmoEntityFieldsDto $amoEntityFieldsDto, string $entityType, bool $result): void
    {
        $message = 'Amo integration result' . "\n\n" . $entityType . ' ' . ($result ? '' : 'not ') . 'updated by api';
        $this->logger->info($message, $amoEntityFieldsDto->toArray());
    }

    public function logWebhookResponsibleError(\Exception $e, array $data = []): void
    {
        $this->loggerWebhookUser->error('AmoCallbackError' . "\n\n" . $e->getMessage(), ['exception' => $e, 'data' => $data]);
    }

    public function logWebhookResponsibleStart(array $requestParams): void
    {
        $this->loggerWebhookUser->info('Amo responsible manager change request', $requestParams);
    }

    public function logWebhookResponsibleEnd(AmoResponsible $amoResponsible): void
    {
        $this->loggerWebhookUser->info('Amo responsible manager change request completed successfully', [
            'salon_id'                => $amoResponsible->getSalonId(),
            'amo_responsible_user_id' => $amoResponsible->getResponsibleAmoUserId(),
            'amo_modified_user_id'    => $amoResponsible->getModifiedAmoUserId(),
        ]);
    }

    public function logWebhookConsultingStatusError(\Exception $e, array $data = []): void
    {
        $this->loggerWebhookStatus->error('AmoCallbackError' . "\n\n" . $e->getMessage(), ['exception' => $e, 'data' => $data]);
    }

    public function logWebhookConsultingStatusStart(array $requestParams): void
    {
        $this->loggerWebhookStatus->info('Amo consulting status change request', $requestParams);
    }

    public function logWebhookConsultingStatusEnd(AmoConsultingStatus $amoConsultingStatus): void
    {
        $this->loggerWebhookStatus->info('Amo consulting status change request completed successfully', [
            'salon_id'              => $amoConsultingStatus->getSalonId(),
            'consulting_status_id'  => $amoConsultingStatus->getConsultingAmoStatusId(),
            'consulting_manager_id' => $amoConsultingStatus->getConsultingAmoManagerId(),
            'integrator_manager_id' => $amoConsultingStatus->getIntegratorAmoManagerId(),
            'amo_modified_user_id'  => $amoConsultingStatus->getModifiedAmoUserId(),
        ]);
    }
}
