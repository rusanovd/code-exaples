<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CSalon;
use Infrastructure\DateTime\DateTimeFormat;
use More\Amo\Services\AmoService;
use More\BusinessGroup\Service\BusinessGroupService;
use More\BusinessGroup\Service\BusinessTypeService;
use More\City\Services\DataProviders\CityDataProvider;
use More\Exception\HasUserMessageException;
use More\References\Country\DataProviders\CountryDataProvider;
use More\Salon\DataProviders\SalonsDataProvider;
use More\SalonSettings\Data\SalonInternalSettings;
use More\SalonSettings\Services\SalonInternalSettingsService;
use More\SalonSettings\Services\SalonLicenseSettingsService;
use More\SalonSettings\Services\SalonSettingsService;
use More\SalonTariff\Data\Option;
use More\SalonTariff\Data\SalonTariffLink;
use More\SalonTariff\DataProviders\SalonPayParamsProvider;
use More\SalonTariff\DataProviders\SalonTariffDataProvider;
use More\SalonTariff\Service\OptionsService;
use More\User\Services\DataProviders\UserDataProvider;
use Psr\Cache\InvalidArgumentException;

class IntercomCompanyOptionsService
{
    private OptionsService $salonTariffOptionsService;
    private BusinessTypeService $businessTypeService;
    private BusinessGroupService $businessGroupService;
    private SalonSettingsService $salonSettingsService;
    private SalonLicenseSettingsService $salonLicenseSettingsService;
    private CityDataProvider $cityDataProvider;
    private CountryDataProvider $countryDataProvider;
    private UserDataProvider $userDataProvider;
    private SalonsDataProvider $salonsDataProvider;
    private SalonPayParamsProvider $salonPayParamsProvider;
    private SalonTariffDataProvider $salonTariffDataProvider;
    private SalonInternalSettingsService $salonInternalSettingsService;
    private IntercomFieldsService $intercomFieldsService;
    private AmoService $amoService;

    public function __construct(
        OptionsService $salonTariffOptionsService,
        BusinessTypeService $businessTypeService,
        BusinessGroupService $businessGroupService,
        SalonSettingsService $salonSettingsService,
        SalonLicenseSettingsService $salonLicenseSettingsService,
        CityDataProvider $cityDataProvider,
        CountryDataProvider $countryDataProvider,
        UserDataProvider $userDataProvider,
        SalonsDataProvider $salonsDataProvider,
        SalonPayParamsProvider $salonPayParamsProvider,
        SalonTariffDataProvider $salonTariffDataProvider,
        SalonInternalSettingsService $salonInternalSettingsService,
        IntercomFieldsService $intercomFieldsService,
        AmoService $amoService
    ) {
        $this->salonTariffOptionsService = $salonTariffOptionsService;
        $this->businessTypeService = $businessTypeService;
        $this->businessGroupService = $businessGroupService;
        $this->salonSettingsService = $salonSettingsService;
        $this->salonLicenseSettingsService = $salonLicenseSettingsService;
        $this->cityDataProvider = $cityDataProvider;
        $this->countryDataProvider = $countryDataProvider;
        $this->userDataProvider = $userDataProvider;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->salonPayParamsProvider = $salonPayParamsProvider;
        $this->salonTariffDataProvider = $salonTariffDataProvider;
        $this->salonInternalSettingsService = $salonInternalSettingsService;
        $this->intercomFieldsService = $intercomFieldsService;
        $this->amoService = $amoService;
    }

    private function getManagerName(int $managerId): string
    {
        if (! $managerId) {
            return '';
        }

        $manager = $this->userDataProvider->findUserById($managerId);

        if ($manager === null) {
            return '';
        }

        return $manager->getFirstName();
    }

    public function getOptionsIndustry(Csalon $salon): array
    {
        $businessTitle = $this->businessTypeService->getBusinessTitleById($salon->getBusinessTypeId());

        return [
            IntercomFieldsMapper::FIELD_INDUSTRY => $businessTitle,
        ];
    }

    public function getOptionsIndustryGroup(Csalon $salon): array
    {
        $businessGroupTitle = $this->businessGroupService->getBusinessGroupTitleById($salon->getBusinessGroupId());

        return [
            IntercomFieldsMapper::FIELD_INDUSTRY_GROUP => $businessGroupTitle,
        ];
    }

    public function getOptionsAmoLead(Csalon $salon): array
    {
        if (! $salon->getAmoId()) {
            $salon = $this->salonsDataProvider->findSalonById($salon->getId());
        }

        return [
            IntercomFieldsMapper::FIELD_AMO_LEAD_LINK => $this->amoService->getAmoLeadUrl($salon->getAmoId()),
        ];
    }

    public function getOptionsSalonSettings(Csalon $salon): array
    {
        $salonSettings = $this->salonSettingsService->getBySalon($salon);

        return [
            IntercomFieldsMapper::FIELD_IS_INDIVIDUAL => $salonSettings->isIndividual(),
        ];
    }

    public function getOptionsLicenseSettings(Csalon $salon): array
    {
        $licenseSettings = $this->salonLicenseSettingsService->getLicenseSettingsForSalon($salon);

        return [
            IntercomFieldsMapper::FIELD_TARIFF_DISCOUNT => $licenseSettings->getTariffDiscount(),
        ];
    }

    public function getOptionsPaySettings(Csalon $salon): array
    {
        $salonPlan = $this->salonPayParamsProvider->getBySalonId($salon->getId());

        return [
            IntercomFieldsMapper::FIELD_PLAN     => _price($salonPlan->getTotalPlan(), $salon->getId()),
            IntercomFieldsMapper::FIELD_PLAN_ABS => $salonPlan->getTotalPlan(),
            IntercomFieldsMapper::FIELD_PLAN_RUB => $salonPlan->getTotalPlanRub($salon),
        ];
    }

    public function getOptionsPlanMainOptions(Csalon $salon): array
    {
        $salonPlan = $this->salonPayParamsProvider->getBySalonId($salon->getId());

        return [
            IntercomFieldsMapper::FIELD_PLAN => _price($salonPlan->getTotalPlan(), $salon->getId()),
        ];
    }

    public function getOptionsTariffSettings(SalonTariffLink $license): array
    {
        $salonTariff = $this->salonTariffDataProvider->findTariffById($license->getTariffId());

        return [
            IntercomFieldsMapper::FIELD_PLAN_NAME         => $salonTariff ? $salonTariff->getName() : '',
            IntercomFieldsMapper::FIELD_PLAN_SIZE         => $license->getStaffLimit(),
            IntercomFieldsMapper::FIELD_FREE_TRIAL        => $license->isTrial(),
        ];
    }

    public function getOptionsTariffActivity(SalonTariffLink $license): array
    {
        return [
            IntercomFieldsMapper::FIELD_DEACTIVATION_DATE => $this->intercomFieldsService->getTimeStamp(
                $license->getDeactivationDate(DateTimeFormat::DATE_TIME_BD)
            ),
        ];
    }

    public function getOptionsUtm(Csalon $salon): array
    {
        return [
            IntercomFieldsMapper::FIELD_UTM_MEDIUM   => $salon->getUtmMedium(),
            IntercomFieldsMapper::FIELD_UTM_SOURCE   => $salon->getUtmSource(),
            IntercomFieldsMapper::FIELD_UTM_TERM     => $salon->getUtmTerm(),
            IntercomFieldsMapper::FIELD_UTM_CONTENT  => $salon->getUtmContent(),
            IntercomFieldsMapper::FIELD_UTM_CAMPAIGN => $salon->getUtmCampaign(),
        ];
    }

    public function getOptionsIdentification(Csalon $salon): array
    {
        return [
            IntercomFieldsMapper::FIELD_COMPANY_ID => $salon->getId(),
            IntercomFieldsMapper::FIELD_NAME       => $salon->getTitle(),
        ];
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws HasUserMessageException
     */
    public function getOptionsCity(Csalon $salon): array
    {
        $city = $salon->getCityId() ? $this->cityDataProvider->getCityById($salon->getCityId()) : null;

        return [
            IntercomFieldsMapper::FIELD_CITY_ID       => $city ? $city->getId() : 0,
            IntercomFieldsMapper::FIELD_CITY_TITLE    => $city ? $city->getTitle() : '',
        ];
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws InvalidArgumentException
     */
    public function getOptionsCountry(Csalon $salon): array
    {
        $country = $salon->getCountryId() ? $this->countryDataProvider->getById($salon->getCountryId()) : null;

        return [
            IntercomFieldsMapper::FIELD_COUNTRY_ID    => $country ? $country->getId() : 0,
            IntercomFieldsMapper::FIELD_COUNTRY_TITLE => $country ? $country->getTitle() : '',
            IntercomFieldsMapper::FIELD_COUNTRY_TYPE  => $country ? $country->getGroupType() : 0,
        ];
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getOptionsLocation(Csalon $salon): array
    {
        return array_merge($this->getOptionsCity($salon), $this->getOptionsCountry($salon));
    }

    public function getOptionsManager(Csalon $salon): array
    {
        return [
            IntercomFieldsMapper::FIELD_MANAGER_ID   => $salon->getManagerId(),
            IntercomFieldsMapper::FIELD_MANAGER_NAME => $this->getManagerName($salon->getManagerId()),
        ];
    }

    public function getOptionsPaid(Csalon $salon): array
    {
        $options = $this->salonTariffOptionsService->getOptionsArrayByCurrentOptions($salon);

        return [
            IntercomFieldsMapper::FIELD_NOTIFICATIONS_PAID => in_array(Option::OPTION_NOTIFICATION_MODULE, $options, true),
            IntercomFieldsMapper::FIELD_KKM_PAID           => in_array(Option::OPTION_KKM, $options, true) || in_array(Option::OPTION_KKM_SERVER, $options, true),
        ];
    }

    public function getOptionsSalonActivity(Csalon $salon): array
    {
        return [
            IntercomFieldsMapper::FIELD_LAST_ACTIVATION_DATE   => $this->intercomFieldsService->getTimeStamp($salon->getLastActivationDate()),
            IntercomFieldsMapper::FIELD_LAST_DEACTIVATION_DATE => $this->intercomFieldsService->getTimeStamp($salon->getLastDisactivationDate()),
            IntercomFieldsMapper::FIELD_ACTIVE                 => $salon->isActive(),
        ];
    }

    public function getOptionsConsulting(Csalon $salon): array
    {
        $settings = $this->salonInternalSettingsService->findBySalon($salon);
        $statusName = $settings ? SalonInternalSettings::getConsultingStatusTitleByConsultingStatus($settings->getConsultingStatus()) : '';
        $managerName = $settings ? $this->getManagerName($settings->getConsultingManagerId()) : '';
        $extraConsultingStatusName = $settings ? SalonInternalSettings::getConsultingStatusTitleByConsultingStatus($settings->getExtraConsultingStatus()) : '';
        $extraConsultingInProgressDateTime = $settings ? $this->intercomFieldsService->getTimeStamp($settings->getExtraConsultingInProgressDateTimeString()) : '';
        $extraConsultingCompleteDateTime = $settings ? $this->intercomFieldsService->getTimeStamp($settings->getExtraConsultingCompleteDateTimeString()) : '';

        return [
            IntercomFieldsMapper::FIELD_CONSULTING_STATUS_NAME                => $statusName,
            IntercomFieldsMapper::FIELD_CONSULTING_MANAGER_NAME               => $managerName,
            IntercomFieldsMapper::FIELD_EXTRA_CONSULTING_STATUS_NAME          => $extraConsultingStatusName,
            IntercomFieldsMapper::FIELD_EXTRA_CONSULTING_IN_PROGRESS_DATETIME => $extraConsultingInProgressDateTime,
            IntercomFieldsMapper::FIELD_EXTRA_CONSULTING_COMPLETE_DATETIME    => $extraConsultingCompleteDateTime,
        ];
    }

    public function getOptionsBalance(Csalon $salon): array
    {
        return [
            IntercomFieldsMapper::FIELD_LAST_PAID_DATE => $this->intercomFieldsService->getTimeStamp($salon->getLastPayDate()),
            IntercomFieldsMapper::FIELD_SUBSCRIBED_AT  => $this->intercomFieldsService->getTimeStamp($salon->getSubscribedAt()),
        ];
    }
}
