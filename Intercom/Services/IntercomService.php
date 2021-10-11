<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CMaster;
use CSalon;
use Exception;
use More\Exception\HasUserMessageException;
use More\Exception\ModelNotFoundException;
use More\Integration\Intercom\Exceptions\IntercomApiException;
use More\Integration\Intercom\Exceptions\IntercomBadTypeException;
use More\Integration\Intercom\Exceptions\IntercomConfigDisabledException;
use More\Integration\Intercom\Exceptions\IntercomEmptyOptionsException;
use More\Integration\Intercom\Log\IntercomLoggerFactory;
use More\SalonTariff\Data\SalonTariffLink;
use More\User\Dto\UserRegisterTargetMetricsDto;
use More\User\Interfaces\UserInterface;
use More\User\Services\UserService;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class IntercomService
{
    private IntercomTypeResolverService $intercomTypeResolverService;
    private IntercomContactService $intercomContactService;
    private IntercomCompanyService $intercomCompanyService;
    private UserService $userService;
    private LoggerInterface $logger;

    /**
     * IntercomService constructor.
     * @param IntercomTypeResolverService $intercomTypeResolverService
     * @param IntercomContactService $intercomContactService
     * @param IntercomCompanyService $intercomCompanyService
     * @param IntercomLoggerFactory $intercomLoggerFactory
     * @param UserService $userService
     * @throws Exception
     */
    public function __construct(
        IntercomTypeResolverService $intercomTypeResolverService,
        IntercomContactService $intercomContactService,
        IntercomCompanyService $intercomCompanyService,
        UserService $userService,
        IntercomLoggerFactory $intercomLoggerFactory
    ) {
        $this->intercomTypeResolverService = $intercomTypeResolverService;
        $this->intercomContactService = $intercomContactService;
        $this->intercomCompanyService = $intercomCompanyService;
        $this->userService = $userService;
        $this->logger = $intercomLoggerFactory->getIntercomLogger();
    }

    /**
     * @return bool
     */
    private function isEnabled(): bool
    {
        return $this->intercomTypeResolverService->isEnabled();
    }

    /**
     * @throws IntercomConfigDisabledException
     */
    public function checkIsEnabled(): void
    {
        $this->intercomTypeResolverService->checkIsEnabled();
    }

    /**
     * @param CSalon $salon
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function updateCompanyOnChangeMainSettings(Csalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }
        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeMainSettings($salon);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param CSalon $salon
     */
    public function updateCompanyOnChangeTariffSettings(Csalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }
        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeTariffSettings($salon);
        $this->updateCompanyByQueue($options);
    }

    public function updateCompanyOnLicensePaid(SalonTariffLink $license): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $options = $this->intercomCompanyService->getCompanyOptionsOnLicensePaid($license);
        if (! $options) {
            return;
        }

        $this->updateCompanyByQueue($options);
    }

    public function updateCompanyOnChangeLicenseSettings(int $licenseId): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeLicenseSettings($licenseId);
        if (! $options) {
            return;
        }

        $this->updateCompanyByQueue($options);
    }

    public function updateCompanyOnChangeLicenseActivity(SalonTariffLink $license): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeLicenseActivity($license);
        if (! $options) {
            return;
        }

        $this->updateCompanyByQueue($options);
    }

    public function updateCompanyOnChangeSalonActivity(CSalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeSalonActivity($salon);
        if (! $options) {
            return;
        }

        $this->updateCompanyByQueue($options);
    }

    /**
     * @param CSalon $salon
     */
    public function updateCompanyOnChangeInfoSettings(Csalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }
        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeInfoSettings($salon);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param CSalon $salon
     * @throws HasUserMessageException
     */
    public function updateCompanyOnWizardFirstStep(Csalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }
        $options = $this->intercomCompanyService->getCompanyOptionsOnWizardFirstStep($salon);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param CMaster $master
     */
    public function updateIntercomCompanyStaff(CMaster $master): void
    {
        if (! $this->isEnabled()) {
            return;
        }
        $options = $this->intercomCompanyService->getCompanyOptionsOnStaffChange($master);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param CSalon $salon
     */
    public function updateIntercomBusinessChanged(CSalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }
        $options = $this->intercomCompanyService->getCompanyOptionsOnBusinessChanged($salon);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param UserInterface $user
     * @param CSalon $salon
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function updateIntercomEntitiesOnWizardDone(
        UserInterface $user,
        CSalon $salon
    ): void {
        if (! $this->isEnabled()) {
            return;
        }

        $contactOptions = $this->intercomContactService->getContactOptionsByUser($user);
        $companyOptions = $this->intercomCompanyService->getCompanyOptionsOnCreating($salon);
        $this->intercomContactService->setCompanyOptionsToContactOptions($contactOptions, $companyOptions);
        $this->updateContactByQueue($contactOptions);
    }

    /**
     * @param CSalon $salon
     */
    public function updateCompanyOnChangeManager(Csalon $salon): void
    {
        if (! $salon->getId() || ! $this->isEnabled()) {
            return;
        }

        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeManager($salon);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param CSalon $salon
     */
    public function updateCompanyOnChangeConsultingStatus(Csalon $salon): void
    {
        if (! $salon->getId() || ! $this->isEnabled()) {
            return;
        }

        $options = $this->intercomCompanyService->getCompanyOptionsOnChangeConsultingStatus($salon);
        $this->updateCompanyByQueue($options);
    }

    /**
     * @param array $options
     */
    public function updateCompanyByQueue(array $options): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->intercomTypeResolverService->setIntercomCompanyToQueue($options);
        } catch (IntercomConfigDisabledException | IntercomEmptyOptionsException $e) {
            $this->logger->info('Intercom integration error' . "\n\n" . $e->getMessage(), [
                'exception' => $e,
                'options'   => $options,
            ]);
        }
        $this->logger->info('Set intercom company to queue', $options);
    }

    /**
     * @param array $options
     */
    public function updateContactByQueue(array $options): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->intercomTypeResolverService->setIntercomContactToQueue($options);
        } catch (IntercomConfigDisabledException | IntercomEmptyOptionsException $e) {
            $this->logger->info('Intercom integration error' . "\n\n" . $e->getMessage(), [
                'exception' => $e,
                'options'   => $options,
            ]);
        }
        $this->logger->info('Set intercom contact to queue', $options);
    }

    /**
     * @param array $contactOptions
     * @param array $companyOptions
     */
    private function createEntitiesByQueue(array $contactOptions, array $companyOptions): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $contactOptions[IntercomFieldsMapper::FIELD_NEED_LINK_DB] = true;
        $this->intercomContactService->setCompanyOptionsToContactOptions($contactOptions, $companyOptions);

        try {
            $this->intercomTypeResolverService->setIntercomContactToQueue($contactOptions);
        } catch (IntercomConfigDisabledException | IntercomEmptyOptionsException $e) {
            $this->logger->info('Intercom integration error' . "\n\n" . $e->getMessage(), [
                'exception'      => $e,
                'contactOptions' => $contactOptions,
                'companyOptions' => $companyOptions,
            ]);
        }
    }

    /**
     * @param array $data
     */
    public function processQueue(array $data): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->intercomTypeResolverService->updateIntercomByTypeResolver($data);
        } catch (ModelNotFoundException | IntercomApiException | IntercomBadTypeException | IntercomConfigDisabledException | IntercomEmptyOptionsException $e) {
            $this->logger->info('Intercom integration error' . "\n\n" . $e->getMessage(), [
                'exception' => $e,
                'options'   => $data,
            ]);
        }
    }

    /**
     * @param UserInterface|null $user
     */
    public function updateIntercomContactByUser(
        UserInterface $user
    ): void {
        if (! $this->isEnabled()) {
            return;
        }

        $this->updateContactByQueue($this->intercomContactService->getContactOptionsByUser($user));
    }

    public function updateContactsOnChangeSalonActivity(CSalon $salon): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $users = $this->userService->getApprovedUsersBySalon($salon);
        if (! $users) {
            return;
        }

        foreach ($users as $user) {
            $options = $this->intercomContactService->getContactOptionsByUser($user);
            $this->updateContactByQueue($options);
        }
    }

    /**
     * @param CSalon $salon
     * @param UserInterface $user
     * @param UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function createIntercomRelatedEntities(
        CSalon $salon,
        UserInterface $user,
        UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto
    ): void {
        if (! $this->isEnabled()) {
            return;
        }

        if ($userRegisterTargetMetricsDto->getUtmSource()) {
            $user->setUtmSource($userRegisterTargetMetricsDto->getUtmSource());
        }
        if ($userRegisterTargetMetricsDto->getUtmMedium()) {
            $user->setUtmMedium($userRegisterTargetMetricsDto->getUtmMedium());
        }
        if ($userRegisterTargetMetricsDto->getUtmCampaign()) {
            $user->setUtmCampaign($userRegisterTargetMetricsDto->getUtmCampaign());
        }
        if ($userRegisterTargetMetricsDto->getUtmTerm()) {
            $user->setUtmTerm($userRegisterTargetMetricsDto->getUtmTerm());
        }
        if ($userRegisterTargetMetricsDto->getUtmContent()) {
            $user->setUtmContent($userRegisterTargetMetricsDto->getUtmContent());
        }

        $contactOptions = $this->intercomContactService->getContactOptionsByUserAndCompany($user, $salon);
        $companyOptions = $this->intercomCompanyService->getCompanyOptionsOnCreating($salon);
        $this->createEntitiesByQueue($contactOptions, $companyOptions);
    }

    /**
     * @param string $eventName
     */
    public function logEventName(string $eventName): void
    {
        if ($eventName === '' || ! $this->isEnabled()) {
            return;
        }
        $this->logger->info('Intercom event' . "\n\n" . $eventName);
    }

    public function updateIntercomRelatedOptions(array $contactOptions, array $companyOptions): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->createEntitiesByQueue($contactOptions, $companyOptions);
    }

    public function updateIntercomCompanyEntity(array $companyOptions): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $companyOptions[IntercomFieldsMapper::FIELD_NEED_LINK_DB] = true;
        $this->updateCompanyByQueue($companyOptions);
    }
}
