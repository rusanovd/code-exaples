<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CMaster;
use CSalon;
use More\Exception\HasUserMessageException;
use More\Salon\DataProviders\SalonsDataProvider;
use More\SalonTariff\Data\SalonTariffLink;
use More\SalonTariff\Service\LicenseService;
use Psr\Cache\InvalidArgumentException;

class IntercomCompanyService
{
    private IntercomCompanyOptionsService $intercomCompanyOptionsService;
    private LicenseService $licenseService;
    private IntercomFieldsService $intercomFieldsService;
    private SalonsDataProvider $salonsDataProvider;

    public function __construct(
        IntercomCompanyOptionsService $intercomCompanyOptionsService,
        LicenseService $licenseService,
        IntercomFieldsService $intercomFieldsService,
        SalonsDataProvider $salonsDataProvider
    ) {
        $this->intercomCompanyOptionsService = $intercomCompanyOptionsService;
        $this->licenseService = $licenseService;
        $this->intercomFieldsService = $intercomFieldsService;
        $this->salonsDataProvider = $salonsDataProvider;
    }

    private function getCompanyFirstPhone(Csalon $salon): string
    {
        return count($salon->getContactPhones()) ? $salon->getContactPhones()[0] : '';
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getCompanyOptionsOnChangeMainSettings(Csalon $salon): array
    {
        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
            [
                IntercomFieldsMapper::FIELD_WEBSITE => $salon->getSite(),
            ]
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsLocation($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
            [
                IntercomFieldsMapper::FIELD_BRANCH_TIMEZONE => $salon->getTimezoneOriginal(),
                IntercomFieldsMapper::FIELD_PHONE           => $this->getCompanyFirstPhone($salon),
            ]
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyOptionsOnChangeTariffSettings(Csalon $salon): array
    {
        $license = $this->licenseService->findById($salon->getSalonTariffLinkId());

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsPlanMainOptions($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsManager($salon),
            $this->intercomCompanyOptionsService->getOptionsPaid($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonSettings($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonActivity($salon),
            $this->intercomCompanyOptionsService->getOptionsLicenseSettings($salon),
            $license ? $this->intercomCompanyOptionsService->getOptionsTariffSettings($license) : [],
            $license ? $this->intercomCompanyOptionsService->getOptionsTariffActivity($license) : [],
            $this->intercomCompanyOptionsService->getOptionsConsulting($salon),
            $this->intercomCompanyOptionsService->getOptionsAmoLead($salon),
            [
                IntercomFieldsMapper::FIELD_PROMO     => $salon->getPromo(),
                IntercomFieldsMapper::FIELD_VIP       => $salon->isVip(),
                IntercomFieldsMapper::FIELD_DELETED   => $salon->isDeleted(),
            ]
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyOptionsOnChangeManager(Csalon $salon): array
    {
        return $this->intercomFieldsService->createWithCustomOptions(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsManager($salon)
        );
    }

    public function getCompanyOptionsOnChangeConsultingStatus(Csalon $salon): array
    {
        return $this->intercomFieldsService->createWithCustomOptions(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsConsulting($salon)
        );
    }

    public function getCompanyOptionsOnChangeLicenseSettings(int $licenseId): array
    {
        $license = $this->licenseService->findById($licenseId);
        if ($license === null) {
            return [];
        }

        $salon = $this->salonsDataProvider->findSalonById($license->getSalonId());
        if ($salon === null) {
            return [];
        }

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
            $this->intercomCompanyOptionsService->getOptionsPlanMainOptions($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsManager($salon),
            $this->intercomCompanyOptionsService->getOptionsPaid($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonActivity($salon),
            $this->intercomCompanyOptionsService->getOptionsTariffSettings($license),
            $this->intercomCompanyOptionsService->getOptionsTariffActivity($license),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyOptionsOnLicensePaid(SalonTariffLink $license): array
    {
        $salon = $this->salonsDataProvider->findSalonById($license->getSalonId());
        if ($salon === null) {
            return [];
        }

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
            $this->intercomCompanyOptionsService->getOptionsPlanMainOptions($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsManager($salon),
            $this->intercomCompanyOptionsService->getOptionsPaid($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonActivity($salon),
            $this->intercomCompanyOptionsService->getOptionsTariffSettings($license),
            $this->intercomCompanyOptionsService->getOptionsTariffActivity($license),
            $this->intercomCompanyOptionsService->getOptionsBalance($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyOptionsOnChangeLicenseActivity(SalonTariffLink $license): array
    {
        $salon = $this->salonsDataProvider->findSalonById($license->getSalonId());
        if ($salon === null) {
            return [];
        }

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsPlanMainOptions($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsManager($salon),
            $this->intercomCompanyOptionsService->getOptionsPaid($salon),
            $this->intercomCompanyOptionsService->getOptionsTariffSettings($license),
            $this->intercomCompanyOptionsService->getOptionsTariffActivity($license),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyOptionsOnChangeSalonActivity(CSalon $salon): array
    {
        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsPlanMainOptions($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsManager($salon),
            $this->intercomCompanyOptionsService->getOptionsPaid($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonActivity($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyOptionsOnChangeInfoSettings(Csalon $salon): array
    {
        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            [
                IntercomFieldsMapper::FIELD_WEBSITE => $salon->getSite(),
            ]
        );

        $this->intercomFieldsService->setCustomOptions($options, [
            IntercomFieldsMapper::FIELD_PHONE => $this->getCompanyFirstPhone($salon),
        ]);

        return $options;
    }

    public function getCompanyOptionsOnStaffChange(CMaster $master): array
    {
        $salon = $this->salonsDataProvider->findSalonById($master->getSalonId());
        if ($salon === null) {
            return [];
        }

        $options = $this->intercomCompanyOptionsService->getOptionsIdentification($salon);
        $this->intercomFieldsService->setCustomOptions($options, [
            IntercomFieldsMapper::FIELD_MASTERS => $salon->getCountMasters(true, false),
        ]);

        return $options;
    }

    public function getCompanyOptionsOnBusinessChanged(CSalon $salon): array
    {
        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
        );

        $customOptions = $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon);

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws HasUserMessageException
     */
    public function getCompanyOptionsOnWizardFirstStep(Csalon $salon): array
    {
        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsCity($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    /**
     * @param CSalon $salon
     * @return array
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getCompanyOptionsOnCreating(Csalon $salon): array
    {
        $license = $this->licenseService->findById($salon->getSalonTariffLinkId());
        $planOptions = $this->intercomCompanyOptionsService->getOptionsPaySettings($salon);

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
            [
                IntercomFieldsMapper::FIELD_CREATED_AT        => $this->intercomFieldsService->getTimeStamp($salon->getCreationDate()),
                IntercomFieldsMapper::FIELD_MONTHLY_SPEND     => $salon->getAvgMonthlySpendsRub(),
                IntercomFieldsMapper::FIELD_PLAN              => $planOptions[IntercomFieldsMapper::FIELD_PLAN],
                IntercomFieldsMapper::FIELD_WEBSITE           => $salon->getSite(),
            ]
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsManager($salon),
            $this->intercomCompanyOptionsService->getOptionsPaid($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonActivity($salon),
            $this->intercomCompanyOptionsService->getOptionsUtm($salon),
            $this->intercomCompanyOptionsService->getOptionsLocation($salon),
            $this->intercomCompanyOptionsService->getOptionsSalonSettings($salon),
            $this->intercomCompanyOptionsService->getOptionsLicenseSettings($salon),
            $license ? $this->intercomCompanyOptionsService->getOptionsTariffSettings($license) : [],
            $license ? $this->intercomCompanyOptionsService->getOptionsTariffActivity($license) : [],
            $this->intercomCompanyOptionsService->getOptionsBalance($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
            $this->intercomCompanyOptionsService->getOptionsAmoLead($salon),
            $this->intercomCompanyOptionsService->getOptionsConsulting($salon),
            [
                IntercomFieldsMapper::FIELD_EMAIL              => $salon->getEmail(),
                IntercomFieldsMapper::FIELD_PROMO              => $salon->getPromo(),
                IntercomFieldsMapper::FIELD_DELETED            => $salon->isDeleted(),
                IntercomFieldsMapper::FIELD_PLAN_ABS           => $planOptions[IntercomFieldsMapper::FIELD_PLAN_ABS],
                IntercomFieldsMapper::FIELD_PLAN_RUB           => $planOptions[IntercomFieldsMapper::FIELD_PLAN_RUB],
                IntercomFieldsMapper::FIELD_BALANCE_ABS        => $salon->getBalance(),
                IntercomFieldsMapper::FIELD_BALANCE_RUB        => $salon->getBalanceRub(),
                IntercomFieldsMapper::FIELD_AVG_SPEND          => $salon->getAvgSpends(),
                IntercomFieldsMapper::FIELD_ZENDESK_ID         => $salon->getZendeskId(),
                IntercomFieldsMapper::FIELD_PHONE              => $this->getCompanyFirstPhone($salon),
                IntercomFieldsMapper::FIELD_MASTERS            => $salon->getCountMasters(true, false),
                IntercomFieldsMapper::FIELD_LAST_RECORD_DATE   => $this->intercomFieldsService->getTimeStamp($salon->getLastRecordCreateDate()),
                IntercomFieldsMapper::FIELD_VIP                => $salon->isVip(),
                IntercomFieldsMapper::FIELD_BRANCH_TIMEZONE    => $salon->getTimezoneOriginal(),
            ]
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    public function getCompanyScriptFlagOptions(Csalon $salon, bool $flag): array
    {
        return $this->intercomFieldsService->createWithCustomOptions(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            [
                IntercomFieldsMapper::FIELD_SCRIPT_FLAG => $flag,
            ]
        );
    }
}
