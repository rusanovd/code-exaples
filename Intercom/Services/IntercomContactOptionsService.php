<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CSalon;
use CUsersSalonsLink;
use More\Amo\Services\AmoService;
use More\References\Country\DataProviders\CountryDataProvider;
use More\Salon\DataProviders\SalonsDataProvider;
use More\SalonSettings\Data\SalonInternalSettings;
use More\SalonSettings\Services\SalonInternalSettingsService;
use More\User\Interfaces\UserInterface;
use More\User\Services\DataProviders\UserDataProvider;
use More\User\Services\UsersSalonsLinkService;

class IntercomContactOptionsService
{
    private AmoService $amoService;
    private UsersSalonsLinkService $usersSalonsLinkService;
    private SalonsDataProvider $salonsDataProvider;
    private UserDataProvider $userDataProvider;
    private CountryDataProvider $countryDataProvider;
    private SalonInternalSettingsService $salonInternalSettingsService;

    public function __construct(
        AmoService $amoService,
        UsersSalonsLinkService $usersSalonsLinkService,
        SalonsDataProvider $salonsDataProvider,
        UserDataProvider $userDataProvider,
        CountryDataProvider $countryDataProvider,
        SalonInternalSettingsService $salonInternalSettingsService
    ) {
        $this->amoService = $amoService;
        $this->usersSalonsLinkService = $usersSalonsLinkService;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->userDataProvider = $userDataProvider;
        $this->countryDataProvider = $countryDataProvider;
        $this->salonInternalSettingsService = $salonInternalSettingsService;
    }

    /**
     * @param int $managerId
     * @return string
     */
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

    /**
     * @param UserInterface $user
     * @return array
     */
    public function getOptionsIdentification(UserInterface $user): array
    {
        return [
            IntercomFieldsMapper::FIELD_USER_ID => $user->getId(),
            IntercomFieldsMapper::FIELD_EMAIL   => $user->getEmail(),
        ];
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function getOptionsLetters(UserInterface $user): array
    {
        return [
            IntercomFieldsMapper::FIELD_NEWS_LETTERS      => $user->getNewsLetters(),
            IntercomFieldsMapper::FIELD_MARKETING_LETTERS => $user->getMarketingLetters(),
            IntercomFieldsMapper::FIELD_INFO_LETTERS      => $user->getInfoLetters(),
        ];
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function getOptionsUtm(UserInterface $user): array
    {
        return [
            IntercomFieldsMapper::FIELD_UTM_MEDIUM   => $user->getUtmMedium(),
            IntercomFieldsMapper::FIELD_UTM_SOURCE   => $user->getUtmSource(),
            IntercomFieldsMapper::FIELD_UTM_TERM     => $user->getUtmTerm(),
            IntercomFieldsMapper::FIELD_UTM_CONTENT  => $user->getUtmContent(),
            IntercomFieldsMapper::FIELD_UTM_CAMPAIGN => $user->getUtmCampaign(),
        ];
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function getOptionsAmo(UserInterface $user): array
    {
        if (! $user->getAmoContactId()) {
            $user = $this->userDataProvider->findUserById($user->getId());
        }

        return [
            IntercomFieldsMapper::FIELD_AMO_CONTACT_LINK => $this->amoService->getAmoContactUrl($user->getAmoContactId()),
        ];
    }

    /**
     * @param CUsersSalonsLink[] $userSalonsLinks
     * @param Csalon[] $salons
     * @return int
     */
    private function getSalonsCntByUserSalonsLinks(array $userSalonsLinks, array $salons): int
    {
        $cnt = 0;
        foreach ($userSalonsLinks as $userSalonsLink) {
            if (! isset($salons[$userSalonsLink->getSalonId()])) {
                continue;
            }
            $cnt++;
        }

        return $cnt;
    }

    /**
     * @param CUsersSalonsLink[] $userSalonsLinks
     * @param Csalon[] $salons
     * @return bool
     */
    private function isUserHasActiveSalon(array $userSalonsLinks, array $salons): bool
    {
        foreach ($userSalonsLinks as $userSalonsLink) {
            if (isset($salons[$userSalonsLink->getSalonId()]) && $salons[$userSalonsLink->getSalonId()]->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CUsersSalonsLink[] $userSalonsLinks
     * @param Csalon[] $salons
     * @return int
     */
    private function getBillingAccessByUserSalonsLinks(array $userSalonsLinks, array $salons): int
    {
        $billingAccess = 0;
        foreach ($userSalonsLinks as $userSalonsLink) {
            if (! isset($salons[$userSalonsLink->getSalonId()])) {
                continue;
            }

            if ($billingAccess = $userSalonsLink->getBillingAccess()) {
                break;
            }
        }

        return $billingAccess;
    }

    /**
     * @param UserInterface $user
     * @return array|int[]
     */
    public function getOptionsAccess(UserInterface $user): array
    {
        $userSalonsLinks = $this->usersSalonsLinkService->getApprovedByUserId($user->getId());
        $salons = $this->salonsDataProvider->getSalonsByUserSalonsLinks($userSalonsLinks);
        $billingAccess = $this->getBillingAccessByUserSalonsLinks($userSalonsLinks, $salons);

        return [
            IntercomFieldsMapper::FIELD_CONTACT_HAS_BILLING_ACCESS => $billingAccess,
        ];
    }

    /**
     * @param UserInterface $user
     * @return array|int[]
     */
    public function getOptionsSalonLinks(UserInterface $user): array
    {
        $userSalonsLinks = $this->usersSalonsLinkService->getApprovedByUserId($user->getId());
        $salons = $this->salonsDataProvider->getSalonsByUserSalonsLinks($userSalonsLinks);
        $billingAccess = $this->getBillingAccessByUserSalonsLinks($userSalonsLinks, $salons);
        $salonsCnt = $this->getSalonsCntByUserSalonsLinks($userSalonsLinks, $salons);
        $isUserHasActiveSalon = $this->isUserHasActiveSalon($userSalonsLinks, $salons);

        return [
            IntercomFieldsMapper::FIELD_CONTACT_HAS_BILLING_ACCESS => $billingAccess,
            IntercomFieldsMapper::FIELD_FILIALS                    => $salonsCnt,
            IntercomFieldsMapper::FIELD_HAS_ACTIVE_SALON           => $isUserHasActiveSalon,
        ];
    }

    /**
     * @param CSalon $salon
     * @return array
     */
    public function getOptionsConsulting(Csalon $salon): array
    {
        $settings = $this->salonInternalSettingsService->findBySalon($salon);
        $statusId = $settings ? $settings->getConsultingStatus() : 0;
        $statusName = $settings ? SalonInternalSettings::getConsultingStatusTitleByConsultingStatus($settings->getConsultingStatus()) : '';
        $managerId = $settings ? $settings->getConsultingManagerId() : 0;
        $managerName = $settings ? $this->getManagerName($settings->getConsultingManagerId()) : '';

        return [
            IntercomFieldsMapper::FIELD_SALON_CONSULTING_ID        => $managerId,
            IntercomFieldsMapper::FIELD_SALON_CONSULTING_NAME      => $managerName,
            IntercomFieldsMapper::FIELD_SALON_CONSULTING_STATUS_ID => $statusId,
            IntercomFieldsMapper::FIELD_SALON_CONSULTING_STATUS    => $statusName,
        ];
    }

    /**
     * @param CSalon $salon
     * @return array
     */
    public function getOptionsManager(Csalon $salon): array
    {
        return [
            IntercomFieldsMapper::FIELD_SALON_MANAGER_ID   => $salon->getManagerId(),
            IntercomFieldsMapper::FIELD_SALON_MANAGER_NAME => $this->getManagerName($salon->getManagerId()),
        ];
    }

    public function getOptionsLocation(Csalon $salon): array
    {
        try {
            $groupType = $this->countryDataProvider->getById($salon->getCountryId())->getGroupType();
        } catch (\Throwable $e) {
            $groupType = 0;
        }

        return [
            IntercomFieldsMapper::FIELD_COUNTRY_GROUP_ID => $groupType,
        ];
    }
}
