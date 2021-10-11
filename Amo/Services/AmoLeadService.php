<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Data\Dto\AmoLeadStartReactivationDto;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Factories\AmoLeadFieldsDtoFactory;
use More\Exception\HasUserMessageException;
use More\Integration\SmsProvider\Data\SalonSmsProviderSetting;
use More\SalonTariff\Data\SalonTariffLink;
use More\User\Dto\UserRegisterTargetMetricsDto;
use Psr\Cache\InvalidArgumentException;

class AmoLeadService
{
    private AmoLeadFieldsDtoFactory $amoLeadFieldsDtoFactory;

    public function __construct(
        AmoLeadFieldsDtoFactory $amoLeadFieldsDtoFactory
    ) {
        $this->amoLeadFieldsDtoFactory = $amoLeadFieldsDtoFactory;
    }

    /**
     * @param CSalon $salon
     * @param UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto
     * @return AmoEntityFieldsDto
     * @throws InvalidArgumentException
     * @throws HasUserMessageException
     */
    public function getAmoLeadCreatingDto(CSalon $salon, UserRegisterTargetMetricsDto $userRegisterTargetMetricsDto): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnCreating($salon, $userRegisterTargetMetricsDto);
    }

    /**
     * @param CSalon $salon
     * @param AmoLeadStartReactivationDto $amoLeadStartReactivationDto
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getAmoLeadStartReactivationDto(Csalon $salon, AmoLeadStartReactivationDto $amoLeadStartReactivationDto): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnStartReactivation($salon, $amoLeadStartReactivationDto);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getAmoLeadBusinessChangedDto(CSalon $salon): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnBusinessChanged($salon);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getAmoLeadSubscriptionChangedDto(CSalon $salon): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnSubscriptionChanged($salon);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     * @throws InvalidArgumentException
     * @throws HasUserMessageException
     */
    public function getAmoLeadLocationChangedDto(CSalon $salon): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnLocationChanged($salon);
    }

    public function getAmoLeadSalonActivityChangedDto(CSalon $salon): ?AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnSalonActivityChanged($salon);
    }

    public function getAmoLeadLicenseActivityChangedDto(SalonTariffLink $license): ?AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnLicenseActivityChanged($license);
    }

    /**
     * @param int $licenseId
     * @return AmoEntityFieldsDto|null
     */
    public function getAmoLeadLicenseSettingsDto(int $licenseId): ?AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnChangeLicenseSettings($licenseId);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getAmoLeadTariffSettingsDto(CSalon $salon): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnOnChangeTariffSettings($salon);
    }

    /**
     * @param CSalon $salon
     * @param array $changeItems
     * @return AmoEntityFieldsDto|null
     * @throws AmoBadParamsException
     * @throws InvalidArgumentException
     */
    public function getAmoLeadBaseSettingsDto(CSalon $salon, array $changeItems): ?AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnOnChangeSalonBaseSettings($salon, $changeItems);
    }

    /**
     * @param CSalon $salon
     * @param int $leadId
     * @return AmoEntityFieldsDto
     */
    public function getAmoLeadLinkSettingsDto(CSalon $salon, int $leadId): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnLinkExistingLead($salon, $leadId);
    }

    /**
     * @param CSalon $salon
     * @param SalonSmsProviderSetting $salonSmsProviderSetting
     * @return AmoEntityFieldsDto
     */
    public function getAmoLeadSmsProviderSettingsDto(CSalon $salon, SalonSmsProviderSetting $salonSmsProviderSetting): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnChangeSalonSmsProviderSettings($salon, $salonSmsProviderSetting);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     */
    public function getAmoLeadContactInfoDto(CSalon $salon): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoOnOnChangeSalonContactInfo($salon);
    }

    /**
     * @param CSalon $salon
     * @return AmoEntityFieldsDto
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function getAmoLeadForScriptDto(CSalon $salon): AmoEntityFieldsDto
    {
        return $this->amoLeadFieldsDtoFactory->getDtoForScript($salon);
    }
}
