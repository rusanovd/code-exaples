<?php

declare(strict_types=1);

namespace More\Amo\Factories;

use CSalon;
use Infrastructure\Models\ChangeItem;
use More\Amo\Data\AmoLead;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Data\Dto\AmoLeadStartReactivationDto;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Services\AmoLeadOptionsService;
use More\Exception\HasUserMessageException;
use More\Exception\ModelNotFoundException;
use More\Exception\ModelNotFoundUserException;
use More\Integration\SmsProvider\Data\SalonSmsProviderSetting;
use More\Salon\DataProviders\SalonsDataProvider;
use More\SalonTariff\Data\SalonTariffLink;
use More\SalonTariff\Service\LicenseService;
use More\User\Dto\UserRegisterTargetMetricsDto;
use More\User\Services\DataProviders\UserDataProvider;
use Psr\Cache\InvalidArgumentException;

class AmoLeadFieldsDtoFactory
{
    private const DEFAULT_EXTERNAL_TEAM_AMO_USER_ID = 2314756;

    private AmoLeadOptionsService $amoLeadOptionsService;
    private UserDataProvider $userDataProvider;
    private SalonsDataProvider $salonsDataProvider;
    private LicenseService $licenseService;

    public function __construct(
        AmoLeadOptionsService $amoLeadOptionsService,
        UserDataProvider $userDataProvider,
        SalonsDataProvider $salonsDataProvider,
        LicenseService $licenseService
    ) {
        $this->amoLeadOptionsService = $amoLeadOptionsService;
        $this->userDataProvider = $userDataProvider;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->licenseService = $licenseService;
    }

    /**
     * @param CSalon $salon
     * @throws AmoBadParamsException
     */
    private function checkSalon(CSalon $salon): void
    {
        if (! $salon->getAmoId()) {
            throw new AmoBadParamsException('Bad salon amo id');
        }
    }

    /**
     * @param CSalon $salon
     * @throws AmoBadParamsException
     */
    private function checkSalonLocation(CSalon $salon): void
    {
        if (! $salon->getCityId() && ! $salon->getCountryId()) {
            throw new AmoBadParamsException('salon has no city and country defined');
        }
    }

    /**
     * @param CSalon $salon
     * @throws AmoBadParamsException
     */
    private function checkSalonBusiness(CSalon $salon): void
    {
        if (! $salon->getBusinessTypeId() && ! $salon->getBusinessGroupId()) {
            throw new AmoBadParamsException('salon has no business type and group defined');
        }
    }

    private function resolveResponsibleAmoUserId(CSalon $salon): int
    {
        if (! $salon->getManagerId()) {
            return 0;
        }

        try {
            $manager = $this->userDataProvider->getUserById($salon->getManagerId());
        } catch (ModelNotFoundException $e) {
            return 0;
        }

        if ($manager->getAmoUserId()) {
            return $manager->getAmoUserId();
        }

        if (! $manager->getGroupId()) {
            return 0;
        }

        if (! $manager->getTeamId()) {
            return self::DEFAULT_EXTERNAL_TEAM_AMO_USER_ID;
        }

        $team = $manager->getTeam();

        if ($team->isOuter()) {
            return self::DEFAULT_EXTERNAL_TEAM_AMO_USER_ID;
        }

        return 0;
    }

    /**
     * @param CSalon $salon
     * @param UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto
     * @return AmoEntityFieldsDto
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getDtoOnCreating(CSalon $salon, UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto): AmoEntityFieldsDto
    {
        $fields =
            $this->amoLeadOptionsService->getOptionsSalonLink($salon) +
            $this->amoLeadOptionsService->getOptionsPlanSettings($salon) +
            $this->amoLeadOptionsService->getOptionsLocation($salon) +
            $this->amoLeadOptionsService->getOptionsBusiness($salon) +
            $this->amoLeadOptionsService->getOptionsSalonActivity($salon) +
            $this->amoLeadOptionsService->getOptionsLicenseActivityBySalon($salon) +
            $this->amoLeadOptionsService->getOptionsSubscription($salon) +
            $this->amoLeadOptionsService->getOptionsTargetMetrics($userRegisterTargetMetricsDto) +
            $this->amoLeadOptionsService->getOptionsPromo($salon) +
            $this->amoLeadOptionsService->getOptionsSalonRegisteredSource($salon) +
            $this->amoLeadOptionsService->getOptionsOnboardingProgressBySalon($salon, true)
        ;

        return new AmoEntityFieldsDto(0, $fields, $salon->getTitle(), $this->resolveResponsibleAmoUserId($salon));
    }

    /**
     * @param CSalon $salon
     * @param AmoLeadStartReactivationDto $amoLeadStartReactivationDto
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getDtoOnStartReactivation(Csalon $salon, AmoLeadStartReactivationDto $amoLeadStartReactivationDto): AmoEntityFieldsDto
    {
        $this->checkSalon($salon);

        $fields = $this->amoLeadOptionsService->getOptionsReactivation($amoLeadStartReactivationDto);

        return new AmoEntityFieldsDto($salon->getAmoId(), $fields);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getDtoOnBusinessChanged(Csalon $salon): AmoEntityFieldsDto
    {
        $this->checkSalon($salon);
        $this->checkSalonBusiness($salon);

        return new AmoEntityFieldsDto($salon->getAmoId(), $this->amoLeadOptionsService->getOptionsBusiness($salon));
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getDtoOnSubscriptionChanged(Csalon $salon): AmoEntityFieldsDto
    {
        $this->checkSalon($salon);

        return new AmoEntityFieldsDto($salon->getAmoId(), $this->amoLeadOptionsService->getOptionsSubscription($salon));
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getDtoOnLocationChanged(Csalon $salon): AmoEntityFieldsDto
    {
        $this->checkSalon($salon);
        $this->checkSalonLocation($salon);

        return new AmoEntityFieldsDto($salon->getAmoId(), $this->amoLeadOptionsService->getOptionsLocation($salon));
    }

    public function getDtoOnChangeLicenseSettings(int $licenseId): ?AmoEntityFieldsDto
    {
        try {
            $license = $this->licenseService->getById($licenseId);
        } catch (ModelNotFoundException $e) {
            return null;
        }

        try {
            $salon = $this->salonsDataProvider->getSalonById($license->getSalonId());
        } catch (ModelNotFoundUserException $e) {
            return null;
        }

        if (! $salon->getAmoId() || ! $salon->getSalonTariffLinkId() || ! $salon->switchedToTariff()) {
            return null;
        }

        $fields =
            $this->amoLeadOptionsService->getOptionsPlanSettings($salon, $license) +
            $this->amoLeadOptionsService->getOptionsSalonActivity($salon, $license) +
            $this->amoLeadOptionsService->getOptionsLicenseActivity($license)
        ;

        return new AmoEntityFieldsDto($salon->getAmoId(), $fields, '', $this->resolveResponsibleAmoUserId($salon));
    }

    public function getDtoOnLicenseActivityChanged(SalonTariffLink $license): ?AmoEntityFieldsDto
    {
        $salon = $this->salonsDataProvider->findSalonById($license->getSalonId());

        if ($salon === null || ! $salon->getAmoId() || ! $salon->getSalonTariffLinkId()) {
            return null;
        }

        $fields =
            $this->amoLeadOptionsService->getOptionsPlanSettings($salon, $license) +
            $this->amoLeadOptionsService->getOptionsLicenseActivity($license)
        ;

        return new AmoEntityFieldsDto($salon->getAmoId(), $fields, '', $this->resolveResponsibleAmoUserId($salon));
    }

    public function getDtoOnSalonActivityChanged(CSalon $salon): ?AmoEntityFieldsDto
    {
        if (! $salon->getAmoId() || ! $salon->getSalonTariffLinkId()) {
            return null;
        }

        $fields =
            $this->amoLeadOptionsService->getOptionsPlanSettings($salon) +
            $this->amoLeadOptionsService->getOptionsSalonActivity($salon)
        ;

        return new AmoEntityFieldsDto($salon->getAmoId(), $fields, '', $this->resolveResponsibleAmoUserId($salon));
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getDtoOnOnChangeTariffSettings(Csalon $salon): AmoEntityFieldsDto
    {
        $this->checkSalon($salon);

        $fields = $this->amoLeadOptionsService->getOptionsPlanSettings($salon) +
            $this->amoLeadOptionsService->getOptionsSalonActivity($salon) +
            $this->amoLeadOptionsService->getOptionsLicenseActivityBySalon($salon) +
            $this->amoLeadOptionsService->getOptionsInternalSettings($salon) +
            $this->amoLeadOptionsService->getOptionsPlanModeration($salon) +
            $this->amoLeadOptionsService->getOptionsSalonDeleted($salon)
        ;

        return new AmoEntityFieldsDto($salon->getAmoId(), $fields, '', $this->resolveResponsibleAmoUserId($salon));
    }

    /**
     * @param CSalon $salon
     * @param ChangeItem[] $changeItems
     * @return AmoEntityFieldsDto|null
     * @throws AmoBadParamsException
     * @throws InvalidArgumentException
     */
    public function getDtoOnOnChangeSalonBaseSettings(CSalon $salon, array $changeItems): ?AmoEntityFieldsDto
    {
        $this->checkSalon($salon);

        $fields = $this->amoLeadOptionsService->getOptionsByChanges($changeItems);
        $title = '';
        foreach ($changeItems as $changeItem) {
            if ($changeItem->getField() === AmoLead::KEY_CHANGE_TITLE) {
                $title = $changeItem->getTo() ?? $salon->getTitle();
            }
        }

        if (empty($fields) && empty($title)) {
            return null;
        }

        return new AmoEntityFieldsDto($salon->getAmoId(), $fields, $title);
    }

    public function getDtoOnLinkExistingLead(CSalon $salon, int $amoLeadId): AmoEntityFieldsDto
    {
        $leadFields =
            $this->amoLeadOptionsService->getOptionsSalonActivity($salon) +
            $this->amoLeadOptionsService->getOptionsLicenseActivityBySalon($salon) +
            $this->amoLeadOptionsService->getOptionsSalonLink($salon);

        return new AmoEntityFieldsDto($amoLeadId, $leadFields, '');
    }

    public function getDtoOnChangeSalonSmsProviderSettings(CSalon $salon, SalonSmsProviderSetting $salonSmsProviderSetting): AmoEntityFieldsDto
    {
        $leadFields = $this->amoLeadOptionsService->getOptionsSmsProvider($salonSmsProviderSetting);

        return new AmoEntityFieldsDto($salon->getAmoId(), $leadFields, '');
    }

    public function getDtoOnOnChangeSalonContactInfo(Csalon $salon): AmoEntityFieldsDto
    {
        $leadFields = $this->amoLeadOptionsService->getOptionsSalonContacts($salon);

        return new AmoEntityFieldsDto($salon->getAmoId(), $leadFields, '');
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getDtoForScript(CSalon $salon): AmoEntityFieldsDto
    {
        $fields =
            $this->amoLeadOptionsService->getOptionsSalonLink($salon) +
            $this->amoLeadOptionsService->getOptionsPlanSettings($salon) +
            $this->amoLeadOptionsService->getOptionsPlanModeration($salon) +
            $this->amoLeadOptionsService->getOptionsLocation($salon) +
            $this->amoLeadOptionsService->getOptionsBusiness($salon) +
            $this->amoLeadOptionsService->getOptionsSalonContacts($salon) +
            $this->amoLeadOptionsService->getOptionsSalonDeleted($salon) +
            $this->amoLeadOptionsService->getOptionsSalonActivity($salon) +
            $this->amoLeadOptionsService->getOptionsLicenseActivityBySalon($salon) +
            $this->amoLeadOptionsService->getOptionsSubscription($salon) +
            $this->amoLeadOptionsService->getOptionsSalonRecords($salon) +
            $this->amoLeadOptionsService->getOptionsPromo($salon) +
            $this->amoLeadOptionsService->getOptionsSalonRegisteredSource($salon) +
            $this->amoLeadOptionsService->getOptionsInternalSettings($salon) +
            $this->amoLeadOptionsService->getOptionsOnboardingProgressBySalon($salon) +
            $this->amoLeadOptionsService->getOptionsSmsProviderBySalon($salon) +
            $this->amoLeadOptionsService->getOptionsSalonUtm($salon)
        ;

        return new AmoEntityFieldsDto(
            $salon->getAmoId(),
            $fields,
            $salon->getTitle(),
            $this->resolveResponsibleAmoUserId($salon)
        );
    }
}
