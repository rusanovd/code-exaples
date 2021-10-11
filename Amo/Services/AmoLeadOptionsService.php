<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use Infrastructure\Models\ChangeItem;
use More\Amo\Data\AmoLead;
use More\Amo\Data\Dto\AmoLeadStartReactivationDto;
use More\City\Services\DataProviders\CityDataProvider;
use More\Exception\HasUserMessageException;
use More\Integration\SmsProvider\Data\SalonSmsProviderSetting;
use More\Integration\SmsProvider\Loaders\SmsProviderLoader;
use More\Integration\SmsProvider\Services\SmsProviderService;
use More\Partner\Services\PartnerService;
use More\References\Country\DataProviders\CountryDataProvider;
use More\Registration\Data\Onboarding\OnboardingProgress;
use More\Registration\DataProviders\Onboarding\OnboardingProgressDataProvider;
use More\Registration\Service\Onboarding\OnboardingService;
use More\SalonSettings\Services\SalonInternalSettingsService;
use More\SalonSettings\Services\SalonSettingsService;
use More\SalonTariff\Data\Option;
use More\SalonTariff\Data\SalonTariffLink;
use More\SalonTariff\DataProviders\SalonTariffDataProvider;
use More\SalonTariff\Service\LicenseService;
use More\SalonTariff\Service\OptionsService;
use More\User\Dto\UserRegisterTargetMetricsDto;
use Psr\Cache\InvalidArgumentException;

class AmoLeadOptionsService
{
    private AmoConfig $amoConfig;
    private AmoFieldsMapper $amoFieldsMapper;
    private AmoDateFormatter $amoDateFormatter;
    private CityDataProvider $cityDataProvider;
    private CountryDataProvider $countryDataProvider;
    private SalonTariffDataProvider $salonTariffDataProvider;
    private OptionsService $salonTariffOptionsService;
    private SalonSettingsService $salonSettingsService;
    private SalonInternalSettingsService $salonInternalSettingsService;
    private LicenseService $licenseService;
    private PartnerService $partnerService;
    private OnboardingProgressDataProvider $onboardingProgressDataProvider;
    private SmsProviderLoader $smsProviderLoader;
    private SmsProviderService $smsProviderService;

    public function __construct(
        AmoConfig $amoConfig,
        AmoFieldsMapper $amoFieldsMapper,
        AmoDateFormatter $amoDateFormatter,
        CityDataProvider $cityDataProvider,
        CountryDataProvider $countryDataProvider,
        SalonInternalSettingsService $salonInternalSettingsService,
        LicenseService $licenseService,
        SalonTariffDataProvider $salonTariffDataProvider,
        OptionsService $salonTariffOptionsService,
        SalonSettingsService $salonSettingsService,
        PartnerService $partnerService,
        OnboardingProgressDataProvider $onboardingProgressDataProvider,
        SmsProviderLoader $smsProviderLoader,
        SmsProviderService $smsProviderService
    ) {
        $this->amoConfig = $amoConfig;
        $this->amoFieldsMapper = $amoFieldsMapper;
        $this->amoDateFormatter = $amoDateFormatter;
        $this->cityDataProvider = $cityDataProvider;
        $this->salonTariffDataProvider = $salonTariffDataProvider;
        $this->salonTariffOptionsService = $salonTariffOptionsService;
        $this->salonSettingsService = $salonSettingsService;
        $this->countryDataProvider = $countryDataProvider;
        $this->salonInternalSettingsService = $salonInternalSettingsService;
        $this->licenseService = $licenseService;
        $this->partnerService = $partnerService;
        $this->onboardingProgressDataProvider = $onboardingProgressDataProvider;
        $this->smsProviderLoader = $smsProviderLoader;
        $this->smsProviderService = $smsProviderService;
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws HasUserMessageException
     */
    private function getOptionsCity(Csalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_CITY_TITLE => $this->cityDataProvider->getCityById($salon->getCityId())->getTitle(),
        ];
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws InvalidArgumentException
     */
    private function getOptionsCountry(Csalon $salon): array
    {
        $country = $this->countryDataProvider->getById($salon->getCountryId());

        return [
            AmoLead::LEAD_FIELD_ID_COUNTRY_TITLE => $country->getTitle(),
            AmoLead::LEAD_FIELD_ID_COUNTRY_GROUP => $country->getGroupType(),
        ];
    }

    private function getOptionsTimezone(Csalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_TIMEZONE => $this->amoFieldsMapper->getAmoTimezone($salon->getTimezoneReal()),
        ];
    }

    public function getOptionsSubscription(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_SUBSCRIBED_AT => $this->amoDateFormatter->formatDateString($salon->getSubscribedAt()),
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
        return $this->getOptionsCity($salon) +
            $this->getOptionsCountry($salon) +
            $this->getOptionsTimezone($salon);
    }

    public function getOptionsSalonActivity(CSalon $salon, ?SalonTariffLink $license = null): array
    {
        if ($license === null) {
            $license = $this->licenseService->findById($salon->getSalonTariffLinkId());
        }

        if ($license &&
            $license->isTrial() &&
            $license->isActive() &&
            $salon->isActive() &&
            $salon->getSubscribedAt() === CSalon::NULL_DATE
        ) {
            $activityId = $this->amoFieldsMapper->getAmoActivityId(2);
        } else {
            $activityId = $this->amoFieldsMapper->getAmoActivityId($salon->getActive());
        }

        $fields = [
            AmoLead::LEAD_FIELD_ID_ACTIVITY => $activityId,
        ];

        if ($lastActivationDate = $this->amoDateFormatter->formatDateString($salon->getLastActivationDate())) {
            $fields[AmoLead::LEAD_FIELD_ID_LAST_ACTIVATION_DATE] = $lastActivationDate;
        }
        if ($lastDeactivationDate = $this->amoDateFormatter->formatDateString($salon->getLastDisactivationDate())) {
            $fields[AmoLead::LEAD_FIELD_ID_LAST_DEACTIVATION_DATE] = $lastDeactivationDate;
        }

        return $fields;
    }

    public function getOptionsLicenseActivity(SalonTariffLink $license): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_DEACTIVATION_DATE => $license->getDeactivationDate(AmoDateFormatter::AMO_API_DATE_FORMAT),
        ];
    }

    public function getOptionsLicenseActivityBySalon(CSalon $salon): array
    {
        $license = $this->licenseService->findById($salon->getSalonTariffLinkId());
        if ($license === null) {
            return [];
        }

        return $this->getOptionsLicenseActivity($license);
    }

    public function getOptionsBusiness(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_BUSINESS_TYPE_FIELD_ID        => $this->amoFieldsMapper->getAmoBusinessTypeId($salon->getBusinessTypeId()),
            AmoLead::LEAD_BUSINESS_GROUP_FIELD_ID       => $this->amoFieldsMapper->getAmoBusinessGroupId($salon->getBusinessGroupId()),
            AmoLead::LEAD_BUSINESS_DESCRIPTION_FIELD_ID => $salon->getShortDescription(),
        ];
    }

    public function getOptionsTargetMetrics(UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_UTM_SOURCE    => $userRegisterTargetMetricsDto->getUtmSource(),
            AmoLead::LEAD_FIELD_ID_UTM_MEDIUM    => $userRegisterTargetMetricsDto->getUtmMedium(),
            AmoLead::LEAD_FIELD_ID_UTM_CAMPAIGN  => $userRegisterTargetMetricsDto->getUtmCampaign(),
            AmoLead::LEAD_FIELD_ID_UTM_CONTENT   => $userRegisterTargetMetricsDto->getUtmContent(),
            AmoLead::LEAD_FIELD_ID_UTM_TERM      => $userRegisterTargetMetricsDto->getUtmTerm(),
            AmoLead::LEAD_FIELD_ID_ROISTAT_VISIT => $userRegisterTargetMetricsDto->getRoistatVisit(),
        ];
    }

    public function getOptionsSalonUtm(Csalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_UTM_SOURCE   => $salon->getUtmSource(),
            AmoLead::LEAD_FIELD_ID_UTM_MEDIUM   => $salon->getUtmMedium(),
            AmoLead::LEAD_FIELD_ID_UTM_CAMPAIGN => $salon->getUtmCampaign(),
            AmoLead::LEAD_FIELD_ID_UTM_CONTENT  => $salon->getUtmContent(),
            AmoLead::LEAD_FIELD_ID_UTM_TERM     => $salon->getUtmTerm(),
        ];
    }

    public function getOptionsInternalSettings(CSalon $salon): array
    {
        $settings = $this->salonInternalSettingsService->findBySalon($salon);

        return [
            AmoLead::LEAD_FIELD_ID_INTEGRATOR_MANAGER_ID       => $settings ? $this->amoFieldsMapper->getAmoIntegratorManagerId($settings->getIntegratorManagerId()) : 0,
            AmoLead::LEAD_FIELD_ID_CONSULTING_MANAGER_ID       => $settings ? $this->amoFieldsMapper->getAmoConsultingManagerId($settings->getConsultingManagerId()) : 0,
            AmoLead::LEAD_FIELD_ID_CONSULTING_STATUS_ID        => $settings ? $this->amoFieldsMapper->getAmoConsultingStatusId($settings->getConsultingStatus()) : 0,
            AmoLead::LEAD_FIELD_ID_CONSULTING_START            => $settings ? $this->amoDateFormatter->formatDate($settings->getConsultingInProgressDateTimeImmutable()) : '',
            AmoLead::LEAD_FIELD_ID_CONSULTING_END              => $settings ? $this->amoDateFormatter->formatDate($settings->getConsultingCompleteDateTimeImmutable()) : '',
            AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_STATUS_ID  => $settings ? $this->amoFieldsMapper->getAmoExtraConsultingStatusId($settings->getExtraConsultingStatus()) : 0,
            AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_START      => $settings ? $this->amoDateFormatter->formatDate($settings->getExtraConsultingInProgressDateTimeImmutable()) : '',
            AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_END        => $settings ? $this->amoDateFormatter->formatDate($settings->getExtraConsultingCompleteDateTimeImmutable()) : '',
            AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_MANAGER_ID => $settings ? $this->amoFieldsMapper->getAmoExtraConsultingManagerId($settings->getExtraConsultingManagerId()) : 0,
        ];
    }

    public function getOptionsSalonRegisteredSource(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_ADMIN_APP                => $this->partnerService->isAdminAppOnlyByPartnerId($salon->getPartnerId()),
            AmoLead::LEAD_FIELD_ID_IS_REGISTERED_VIA_EVOTOR => $salon->getPartnerId() === $this->amoConfig->getEvotorPartnerId(),
        ];
    }

    public function getOptionsPromo(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_PROMO_ID   => $salon->getPartnerId(),
            AmoLead::LEAD_FIELD_ID_PROMO_CODE => $salon->getPromo(),
        ];
    }

    public function getOptionsSalonContacts(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_WEBSITE => $salon->getSite(),
        ];
    }

    public function getOptionsSalonDeleted(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_SALON_IS_DELETED => $salon->isDeleted(),
        ];
    }

    public function getOptionsSalonLink(CSalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_SALON_ID      => $salon->getId(),
            AmoLead::LEAD_FIELD_ID_LINK_TO_SALON => salonLink($salon->getId()),
        ];
    }

    public function getOptionsSmsProvider(SalonSmsProviderSetting $salonSmsProviderSetting): array
    {
        $smsProviderName = $this->smsProviderLoader->getSmsProviderById($salonSmsProviderSetting->getProviderId())->getName();

        return [
            AmoLead::LEAD_FIELD_ID_SMS_AGGREGATOR_NAME => $smsProviderName,
        ];
    }

    public function getOptionsSmsProviderBySalon(CSalon $salon): array
    {
        $smsProviderSetting = $this->smsProviderService->findSalonSmsProviderSettingBySalonId($salon->getId());
        if ($smsProviderSetting === null) {
            return [];
        }

        return $this->getOptionsSmsProvider($smsProviderSetting);
    }

    public function getOptionsPlanSettings(Csalon $salon, ?SalonTariffLink $license = null): array
    {
        if ($license === null) {
            $license = $this->licenseService->findById($salon->getSalonTariffLinkId());
            if ($license === null) {
                return [];
            }
        }

        $salonTariff = $this->salonTariffDataProvider->findTariffById($license->getTariffId());
        $options = $this->salonTariffOptionsService->getOptionsArrayByCurrentOptions($salon);

        return [
                AmoLead::LEAD_FIELD_ID_STAFF_COUNT_2      => $license->getStaffLimit(),
                AmoLead::LEAD_FIELD_ID_TARIFF_DISCOUNT    => $license->getDiscount(),
                AmoLead::LEAD_FIELD_ID_TARIFF_IS_FREEZE   => $license->isFreeze(),
                AmoLead::LEAD_FIELD_ID_IS_INDIVIDUAL      => $this->salonSettingsService->getBySalon($salon)->isIndividual(),
                AmoLead::LEAD_FIELD_ID_IS_VIP             => $salon->isVip(),
                AmoLead::LEAD_FIELD_ID_PLAN_NAME          => $salonTariff ? $salonTariff->getName() : '',
                AmoLead::LEAD_FIELD_ID_NOTIFICATIONS_PAID => in_array(Option::OPTION_NOTIFICATION_MODULE, $options, true),
                AmoLead::LEAD_FIELD_ID_KKM_PAID           => in_array(Option::OPTION_KKM, $options, true) || in_array(Option::OPTION_KKM_SERVER, $options, true),
            ];
    }

    public function getOptionsOnboardingProgress(OnboardingProgress $onboardingProgress): array
    {
        return $this->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_FIELD_ID_PLACE_COUNT                   => $this->amoFieldsMapper->getAmoBusinessPlaceCountId($onboardingProgress->getPlaceCountId()),
            AmoLead::LEAD_FIELD_ID_STAFF_COUNT_1                 => $this->amoFieldsMapper->getAmoBusinessStuffCountId($onboardingProgress->getStaffCountId()),
            AmoLead::LEAD_FIELD_ID_IS_INDIVIDUAL                 => OnboardingService::isIndividualMaster($onboardingProgress->getPlaceCountId()),
            AmoLead::LEAD_FIELD_ID_PURPOSE                       => $this->amoFieldsMapper->getAmoBusinessPurposesIds($onboardingProgress->getPurposesIds()),
            AmoLead::LEAD_FIELD_ID_REFERRAL_SOURCE               => $this->amoFieldsMapper->getAmoReferralSourceId($onboardingProgress->getReferralSourceId()),
            AmoLead::LEAD_FIELD_ID_TIME_TO_CALL                  => $onboardingProgress->getTimeToCallTimestamp(),
            AmoLead::LEAD_FIELD_ID_IS_PROVIDE_SERVICES_BY_MYSELF => $onboardingProgress->isProvideServicesByMyself(),
        ];
    }

    public function getOptionsOnboardingFirstStep(OnboardingProgress $onboardingProgress): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_LAST_COMPLETE_STEP => $onboardingProgress->getLastCompletedStep(),
        ];
    }

    public function getOptionsOnboardingProgressBySalon(CSalon $salon, $isOnlyStep = false): array
    {
        $onboardingProgress = $this->onboardingProgressDataProvider->findOnboardingProgressBySalonId($salon->getId());

        if ($onboardingProgress === null) {
            return [];
        }

        if ($isOnlyStep) {
            return $this->getOptionsOnboardingFirstStep($onboardingProgress);
        }

        return $this->getOptionsOnboardingProgress($onboardingProgress);
    }

    public function getOptionsPlanModeration(Csalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_MODERATION_STATUS => $this->amoFieldsMapper->getModerationStatus($salon->getModerationStatus()),
            AmoLead::LEAD_FIELD_ID_TARIFF_COMMENT    => $salon->getPlan()->getComment(),
        ];
    }

    public function getOptionsSalonRecords(Csalon $salon): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_LAST_RECORD_CREATE_DATE => $this->amoDateFormatter->formatDateString($salon->getLastRecordCreateDate()),
        ];
    }

    public function getOptionsReactivation(AmoLeadStartReactivationDto $amoLeadStartReactivationDto): array
    {
        return [
            AmoLead::LEAD_FIELD_ID_SUBSCRIBED_AT                => $this->amoDateFormatter->formatDate($amoLeadStartReactivationDto->getSubscribedAtDate()),
            AmoLead::LEAD_FIELD_ID_LAST_ACTIVATION_DATE         => $this->amoDateFormatter->formatDate($amoLeadStartReactivationDto->getLastActivationDate()),
            AmoLead::LEAD_FIELD_ID_LAST_DEACTIVATION_DATE       => $this->amoDateFormatter->formatDate($amoLeadStartReactivationDto->getLastDisactivationDate()),
            AmoLead::LEAD_FIELD_ID_LAST_RECORD_CREATE_DATE      => $this->amoDateFormatter->formatDate($amoLeadStartReactivationDto->getLastRecordCreateDate()),
            AmoLead::LEAD_FIELD_ID_LAST_START_REACTIVATION_DATE => $this->amoDateFormatter->formatDateAndTimezone($amoLeadStartReactivationDto->getLastStartReactivationDate()),
        ];
    }

    /**
     * @param ChangeItem[] $changeItems
     * @return array
     * @throws InvalidArgumentException
     */
    public function getOptionsByChanges(array $changeItems): array
    {
        $fields = [];
        foreach ($changeItems as $changeItem) {
            if ($changeItem->getField() === AmoLead::KEY_CHANGE_COUNTRY) {
                $country = $this->countryDataProvider->getById((int) $changeItem->getTo());
                if ($country->getId()) {
                    $fields[AmoLead::LEAD_FIELD_ID_COUNTRY_TITLE] = $country->getTitle();
                    $fields[AmoLead::LEAD_FIELD_ID_COUNTRY_GROUP] = $country->getGroupType();
                }
            }

            if ($changeItem->getField() === AmoLead::KEY_CHANGE_CITY) {
                try {
                    $cityTitle = $this->cityDataProvider->getCityById((int) $changeItem->getTo())->getTitle();
                } catch (HasUserMessageException $e) {
                    $cityTitle = '';
                }
                if ($cityTitle) {
                    $fields[AmoLead::LEAD_FIELD_ID_CITY_TITLE] = $cityTitle;
                }
            }

            if ($changeItem->getField() === AmoLead::KEY_CHANGE_TIMEZONE) {
                $fields[AmoLead::LEAD_FIELD_ID_TIMEZONE] = $this->amoFieldsMapper->getAmoTimezone((int) $changeItem->getTo());
            }

            if ($changeItem->getField() === AmoLead::KEY_CHANGE_SITE) {
                $fields[AmoLead::LEAD_FIELD_ID_WEBSITE] = $changeItem->getTo();
            }
        }

        return $fields;
    }
}
