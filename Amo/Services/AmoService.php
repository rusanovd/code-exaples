<?php

namespace More\Amo\Services;

use AmoCRM\Exception;
use CMaster;
use CSalon;
use Infrastructure\Metrics\Facade\Metrics;
use Infrastructure\Models\ChangeItem;
use More\Amo\Data\AmoContact;
use More\Amo\Data\Dto\AmoLeadStartReactivationDto;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Exceptions\AmoConfigDisabledException;
use More\Exception\HasUserMessageException;
use More\Integration\SmsProvider\Data\SalonSmsProviderSetting;
use More\Registration\Service\OnboardingSpamRegistrationService;
use More\SalonTariff\Data\SalonTariffLink;
use More\User\Dto\UserRegisterTargetMetricsDto;
use More\User\Interfaces\UserInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class AmoService
{
    private AmoClient $amoClient;
    private AmoContactService $amoContactService;
    private AmoLeadService $amoLeadService;
    private AmoTaskService $amoTaskService;
    private AmoQueueService $amoQueueService;
    private AmoLoggerService $amoLoggerService;

    private const AMO_HOST = 'https://yclients.amocrm.ru/';

    /**
     * @var string[]
     */
    protected static array $amoUrlTemplates = [
        AmoFieldsMapper::ENTITY_TYPE_DEAL => 'https://#DOMAIN#.amocrm.ru/leads/detail/#DEAL_ID#',
    ];
    private OnboardingSpamRegistrationService $onboardingSpamRegistrationService;

    public function __construct(
        AmoClient $amoClient,
        AmoContactService $amoContactService,
        AmoLeadService $amoLeadService,
        AmoTaskService $amoTaskService,
        AmoQueueService $amoQueueService,
        AmoLoggerService $amoLoggerService,
        OnboardingSpamRegistrationService $onboardingSpamRegistrationService
    ) {
        $this->amoClient = $amoClient;
        $this->amoContactService = $amoContactService;
        $this->amoLeadService = $amoLeadService;
        $this->amoTaskService = $amoTaskService;
        $this->amoQueueService = $amoQueueService;
        $this->amoLoggerService = $amoLoggerService;
        $this->onboardingSpamRegistrationService = $onboardingSpamRegistrationService;
    }

    /**
     * @param string $eventName
     */
    public function logEventName(string $eventName): void
    {
        if ($eventName === '' || ! $this->amoClient->isAmoEnabled()) {
            return;
        }
        $this->amoLoggerService->log('Amo event' . "\n\n" . $eventName);
    }

    /**
     * @deprecated (use getAmoLeadUrl)
     * @return string
     * @param int $dealId
     */
    public function makeAmoDealUrlById(int $dealId): string
    {
        $domain = $this->amoClient->getAmoHost();

        $template = self::$amoUrlTemplates[AmoFieldsMapper::ENTITY_TYPE_DEAL] ?? '';

        return str_replace(['#DOMAIN#', '#DEAL_ID#'], [$domain, $dealId], $template);
    }

    /**
     * @return string
     * @param int $leadId
     */
    public function getAmoLeadUrl(int $leadId): string
    {
        if (! $leadId) {
            return '';
        }

        return self::AMO_HOST . 'leads/detail/' . $leadId;
    }

    /**
     * @param int $contactId
     * @return string
     */
    public function getAmoContactUrl(int $contactId): string
    {
        if (! $contactId) {
            return '';
        }

        return self::AMO_HOST . 'contacts/detail/' . $contactId;
    }

    /**
     * @param CSalon $salon
     * @param UserInterface $user
     * @param UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function createAmoRelatedEntities(
        CSalon $salon,
        UserInterface $user,
        UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto
    ): void {
        if (! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $amoLead = $this->amoClient->findAmoLeadById($salon->getAmoId());

        if ($amoLead === null) {
            if ($salon->isTrash()) {
                $this->onboardingSpamRegistrationService->logSpamRegistration($salon->getId());
                return;
            }
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadCreatingDto($salon, $userRegisterTargetMetricsDto);
            try {
                $amoLeadId = $this->amoClient->createAmoLeadByDto($amoEntityFieldsDto);
                $this->amoLoggerService->logApi('createAmoLeadByDto', $amoLeadId, $amoEntityFieldsDto->toArray());
            } catch (AmoConfigDisabledException | AmoBadParamsException | Exception $e) {
                $this->amoLoggerService->logExceptionError($e, $amoEntityFieldsDto->toArray());

                return;
            }
        } else {
            $amoLeadId = $amoLead->getId();
            if ($amoLead->getSalonId() !== $salon->getId()) {
                $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadLinkSettingsDto($salon, $amoLead->getId());
                try {
                    $this->amoClient->updateAmoLeadByDto($amoEntityFieldsDto);
                    $this->amoLoggerService->logApi('updateAmoLeadByDto', $amoLead->getId(), $amoEntityFieldsDto->toArray());
                } catch (AmoConfigDisabledException | AmoBadParamsException | Exception  $e) {
                    $this->amoLoggerService->logExceptionError($e, $amoEntityFieldsDto->toArray());

                    return;
                }
            }
        }

        if ($amoLeadId > 0 && $salon->getAmoId() !== $amoLeadId) {
            $salon->setAmoId($amoLeadId)->save(['amo_id']);
            $this->amoLoggerService->logApi('updated salon salons.amo_id', $amoLeadId, ['salonId' => $salon->getId()]);
        }

        try {
            $amoContactId = $this->amoContactService->resolveAmoContactByUserAndSalon($user, $salon);
            $this->amoLoggerService->logApi('resolveAmoContactByUserAndSalon', $user->getId(), [
                'userId'       => $user->getId(),
                'amoLeadId'    => $amoLeadId,
                'amoContactId' => $amoContactId,
            ]);
        } catch (AmoConfigDisabledException $e) {
            $logData = isset($amoEntityFieldsDto) ? $amoEntityFieldsDto->toArray() : [];
            $this->amoLoggerService->logExceptionError($e, $logData);
        }
    }

    /**
     * @param CSalon $salon
     * @param CMaster $master
     */
    public function createAmoContactOnAddMaster(
        CSalon $salon,
        CMaster $master
    ): void {
        if (! $this->amoClient->isAmoEnabled() || ! $salon->getAmoId()) {
            return;
        }

        try {
            $amoContactId = $this->amoContactService->resolveAmoContactByMasterAndSalon($master, $salon);
            $this->amoLoggerService->logApi('resolveAmoContactByMasterAndSalon', $amoContactId, [
                'masterId'     => $master->getId(),
                'userId'       => $master->getUserId(),
                'amoContactId' => $amoContactId,
            ]);
        } catch (AmoConfigDisabledException $e) {
            $this->amoLoggerService->logExceptionError($e, [
                'salonId'   => $salon->getId(),
                'masterId'  => $master->getId(),
                'userId'    => $master->getUserId(),
            ]);
        }
    }

    /**
     * @param CSalon $salon
     * @param int $taskType
     * @param string $text
     * @param LoggerInterface|null $logger
     */
    public function createTaskForSalon(CSalon $salon, int $taskType, string $text, LoggerInterface $logger = null): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $this->amoTaskService->createTaskForSalon($salon, $taskType, $text, $logger);
    }

    /**
     * Обновить дополнительные поля лида при реактивации
     *
     * @param CSalon $salon
     * @param AmoLeadStartReactivationDto $amoLeadStartReactivationDto
     */
    public function updateAmoLeadStartReactivation(Csalon $salon, AmoLeadStartReactivationDto $amoLeadStartReactivationDto): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadStartReactivationDto($salon, $amoLeadStartReactivationDto);
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);

            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);
    }

    /**
     * @param UserInterface $user
     * @param CSalon $salon
     */
    public function updateAmoContactOnUserAccessUpdated(UserInterface $user, Csalon $salon): void
    {
        if (! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoContactId = $this->amoContactService->resolveAmoContactByUserAndSalon($user, $salon);
        } catch (AmoConfigDisabledException $e) {
            return;
        }

        if (! $amoContactId) {
            return;
        }

        if ($amoContactId !== $user->getAmoContactId()) {
            $user->setAmoContactId($amoContactId);
        }

        try {
            $amoEntityFieldsDto = $this->amoContactService->getAmoContactWithAccessDto($user);
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, ['userId' => $user->getId()]);
            $amoEntityFieldsDto = null;
        }

        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoContactToQueue($amoEntityFieldsDto);
    }

    public function updateAmoContactOnChangeLeads(int $userId, int $salonId): void
    {
        if (! $userId || ! $salonId || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoContactService->getAmoContactDtoByUserAndSalon($userId, $salonId);
        } catch (AmoConfigDisabledException $e) {
            $this->amoLoggerService->logExceptionError($e, [
                'userId'  => $userId,
                'salonId' => $salonId,
            ]);
            $amoEntityFieldsDto = null;
        }

        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoContactToQueue($amoEntityFieldsDto);
    }

    public function updateAmoContactOnContactLeadsResolved(AmoContact $contact, UserInterface $user, Csalon $salon): void
    {
        if (! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $amoEntityFieldsDto = $this->amoContactService->getAmoContactDtoByContact($contact, $user, $salon);
        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoContactToQueue($amoEntityFieldsDto);
    }

    /**
     * @param CSalon $salon
     */
    public function updateAmoLeadOnBusinessUpdated(Csalon $salon): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadBusinessChangedDto($salon);
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);

            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_business_updated'));
    }

    /**
     * @param CSalon $salon
     */
    public function updateAmoLeadOnSubscriptionUpdated(Csalon $salon): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadSubscriptionChangedDto($salon);
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);

            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_subscription_updated'));
    }

    /**
     * @param CSalon $salon
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function updateAmoLeadOnWizardFirstStepComplete(Csalon $salon): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadLocationChangedDto($salon);
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);

            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_wizard_first_step'));
    }

    /**
     * @param int $licenseId
     */
    public function updateAmoLeadOnChangeLicenseSettings(int $licenseId): void
    {
        if (! $licenseId || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadLicenseSettingsDto($licenseId);
        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);
        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_license_changed'));
    }

    public function updateAmoLeadOnChangeLicenseActivity(SalonTariffLink $license): void
    {
        if (! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadLicenseActivityChangedDto($license);
        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);
        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_license_activity_changed'));
    }

    public function updateAmoLeadOnChangeSalonActivity(CSalon $salon): void
    {
        if (! $salon->getSalonTariffLinkId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadSalonActivityChangedDto($salon);
        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);
        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_salon_activity_changed'));
    }

    /**
     * @param CSalon $salon
     */
    public function updateAmoLeadOnChangeTariffSettings(Csalon $salon): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadTariffSettingsDto($salon);
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);

            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_tariff_changed'));
    }

    /**
     * @param CSalon $salon
     * @param ChangeItem[] $changeItems
     * @psalm-suppress InvalidCatch Psr\Cache\InvalidArgumentException можно отловить
     */
    public function updateAmoLeadOnChangeSalonBaseSettings(Csalon $salon, array $changeItems): void
    {
        if (empty($changeItems) || ! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadBaseSettingsDto($salon, $changeItems);
        } catch (AmoBadParamsException | InvalidArgumentException $e) {
            $this->amoLoggerService->logExceptionError($e, [
                'salonId' => $salon->getId(),
                'changes' => $changeItems,
            ]);
            $amoEntityFieldsDto = null;
        }

        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_base_settings_changed'));
    }

    /**
     * @param CSalon $salon
     * @param SalonSmsProviderSetting $salonSmsProviderSetting
     */
    public function updateAmoLeadOnSalonSmsProviderSettingsUpdated(Csalon $salon, SalonSmsProviderSetting $salonSmsProviderSetting): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadSmsProviderSettingsDto($salon, $salonSmsProviderSetting);
        } catch (InvalidConfigurationException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);

            return;
        }

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_sms_provider_changed'));
    }

    /**
     * @param CSalon $salon
     */
    public function updateAmoLeadOnSalonContactInfoUpdated(Csalon $salon): void
    {
        if (! $salon->getAmoId() || ! $this->amoClient->isAmoEnabled()) {
            return;
        }

        $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadContactInfoDto($salon);

        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createQueueLeadMetric('update_on_salon_info_changed'));
    }
}
