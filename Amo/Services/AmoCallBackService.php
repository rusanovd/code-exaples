<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use Infrastructure\Events\EventDispatcherInterface;
use Infrastructure\Http\RequestInterface;
use Infrastructure\Metrics\Facade\Metrics;
use More\Amo\Data\AmoRequest\AmoLeadConsultingStatusChangedRequest;
use More\Amo\Data\AmoRequest\AmoLeadResponsibleUserChangedRequest;
use More\Amo\Data\AmoResponsible;
use More\Amo\Events\AmoCallBackChangeConsultingStatusEvent;
use More\Amo\Events\AmoCallBackChangeLeadManagerEvent;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Exception\ModelNotFoundUserException;
use More\ManagerSubstitutionLog\Service\ManagerSubstitutionLogService;
use More\Salon\DataProviders\SalonsDataProvider;
use More\SalonSettings\Services\SalonInternalSettingsService;
use More\User\Services\DataProviders\UserDataProvider;

class AmoCallBackService
{
    private const DEFAULT_AUTHOR_ID = 3;

    private EventDispatcherInterface $eventDispatcher;
    private UserDataProvider $userDataProvider;
    private SalonsDataProvider $salonDataProvider;
    private ManagerSubstitutionLogService $managerSubstitutionLogService;
    private AmoLoggerService $amoLoggerService;
    private AmoFieldsMapper $amoFieldsMapper;
    private SalonInternalSettingsService $salonInternalSettingsService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserDataProvider $userDataProvider,
        SalonsDataProvider $salonDataProvider,
        ManagerSubstitutionLogService $managerSubstitutionLogService,
        AmoLoggerService $amoLoggerService,
        AmoFieldsMapper $amoFieldsMapper,
        SalonInternalSettingsService $salonInternalSettingsService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->userDataProvider = $userDataProvider;
        $this->salonDataProvider = $salonDataProvider;
        $this->managerSubstitutionLogService = $managerSubstitutionLogService;
        $this->amoLoggerService = $amoLoggerService;
        $this->amoFieldsMapper = $amoFieldsMapper;
        $this->salonInternalSettingsService = $salonInternalSettingsService;
    }

    /**
     * @param CSalon $salon
     * @param AmoResponsible $amoResponsible
     * @throws AmoBadParamsException
     */
    private function updateSalonManagerResponsible(CSalon $salon, AmoResponsible $amoResponsible): void
    {
        $manager = $this->userDataProvider->findByAmoUserId($amoResponsible->getResponsibleAmoUserId());

        if ($manager === null) {
            throw new AmoBadParamsException('Manager not found');
        }

        if ($salon->getManagerId() === $manager->getId() && $salon->getTeamId() === $manager->getTeamId()) {
            return;
        }

        $author = $this->userDataProvider->findByAmoUserId($amoResponsible->getModifiedAmoUserId());
        $oldManagerId = $salon->getManagerId();

        $salon
            ->setManagerId($manager->getId())
            ->setTeamId($manager->getTeamId())
            ->saveChanges();

        $this->managerSubstitutionLogService->createNow(
            $salon->getId(),
            $author ? $author->getId() : self::DEFAULT_AUTHOR_ID,
            $oldManagerId,
            $manager->getId()
        );
    }

    public function processAmoWebhookResponsibleUserChanged(RequestInterface $request): void
    {
        if (! $requestParams = $request->getParams()) {
            Metrics::increment(AmoMetric::createWebhookErrorMetric(AmoMetric::METRIC_WEBHOOK_RESPONSIBLE_MANGER));

            return;
        }

        $this->amoLoggerService->logWebhookResponsibleStart($requestParams);

        try {
            $amoCallbackRequest = AmoLeadResponsibleUserChangedRequest::createFromArray($requestParams);
            $amoResponsible = $amoCallbackRequest->getAmoResponsible();
            $salon = $this->salonDataProvider->getSalonById($amoResponsible->getSalonId());
            $this->updateSalonManagerResponsible($salon, $amoResponsible);
        } catch (AmoBadParamsException | ModelNotFoundUserException | \Exception $e) {
            $this->amoLoggerService->logWebhookResponsibleError($e, $requestParams);
            Metrics::increment(AmoMetric::createWebhookErrorMetric(AmoMetric::METRIC_WEBHOOK_RESPONSIBLE_MANGER));

            return;
        }

        Metrics::increment(AmoMetric::createWebhookSuccessMetric(AmoMetric::METRIC_WEBHOOK_RESPONSIBLE_MANGER));

        $this->amoLoggerService->logWebhookResponsibleEnd($amoResponsible);

        $this->eventDispatcher->fire(new AmoCallBackChangeLeadManagerEvent($salon, $amoResponsible));
    }

    public function processAmoWebhookConsultingStatusChanged(RequestInterface $request): void
    {
        if (! $requestParams = $request->getParams()) {
            Metrics::increment(AmoMetric::createWebhookErrorMetric(AmoMetric::METRIC_WEBHOOK_CONSULTING_STATUS));

            return;
        }

        try {
            $amoCallbackRequest = AmoLeadConsultingStatusChangedRequest::createFromArray($requestParams);
        } catch (AmoBadParamsException $e) {
            return;
        }

        $status = $amoCallbackRequest->getConsultingStatus();

        if (! $status->getSalonId() || $status->isAutoLead() || $status->isWrongFieldsFormat()) {
            return;
        }

        if (! $salon = $this->salonDataProvider->findSalonById($status->getSalonId())) {
            return;
        }

        if ($salon->getAmoId() !== $status->getLeadId()) {
            return;
        }

        $this->amoLoggerService->logWebhookConsultingStatusStart($requestParams);

        try {
            $this->salonInternalSettingsService->updateSalonInternalSettings(
                $salon,
                self::DEFAULT_AUTHOR_ID,
                $this->amoFieldsMapper->getConsultingStatusId($status->getConsultingAmoStatusId()),
                $this->amoFieldsMapper->getConsultingManagerId($status->getConsultingAmoManagerId()),
                $this->amoFieldsMapper->getIntegratorManagerId($status->getIntegratorAmoManagerId()),
                $this->amoFieldsMapper->getExtraConsultingManagerId($status->getExtraConsultingAmoManagerId()),
                $this->amoFieldsMapper->getExtraConsultingStatusId($status->getExtraConsultingAmoStatusId())
            );
        } catch (\Throwable $e) {
            $this->amoLoggerService->logWebhookConsultingStatusError($e, $requestParams);
            Metrics::increment(AmoMetric::createWebhookErrorMetric(AmoMetric::METRIC_WEBHOOK_CONSULTING_STATUS));

            return;
        }

        Metrics::increment(AmoMetric::createWebhookSuccessMetric(AmoMetric::METRIC_WEBHOOK_CONSULTING_STATUS));

        $this->amoLoggerService->logWebhookConsultingStatusEnd($status);

        $this->eventDispatcher->fire(new AmoCallBackChangeConsultingStatusEvent($salon));
    }
}
