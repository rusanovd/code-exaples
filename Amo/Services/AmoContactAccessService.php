<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use More\Amo\Data\AmoContact;
use More\Salon\DataProviders\SalonsDataProvider;
use More\User\Interfaces\UserInterface;
use More\User\Services\UsersSalonsLinkService;

class AmoContactAccessService
{
    private UsersSalonsLinkService $usersSalonsLinkService;
    private SalonsDataProvider $salonsDataProvider;
    private AmoLoggerService $amoLoggerService;

    public function __construct(
        UsersSalonsLinkService $usersSalonsLinkService,
        SalonsDataProvider $salonsDataProvider,
        AmoLoggerService $amoLoggerService
    ) {
        $this->usersSalonsLinkService = $usersSalonsLinkService;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->amoLoggerService = $amoLoggerService;
    }

    public function hasMasterOnlyAccess(UserInterface $user, Csalon $salon): bool
    {
        $userSalonsLink = $this->usersSalonsLinkService->findBySalonAndUser($salon, $user);

        if (! $userSalonsLink) {
            return false;
        }

        $isMasterOnlyAccess = $this->usersSalonsLinkService->isMasterDefaultAccess($userSalonsLink);
        $extendedAccess = $this->usersSalonsLinkService->getMasterExtendedAccess($userSalonsLink);

        $this->amoLoggerService->logAccess($user->getId(), $salon->getId(), $isMasterOnlyAccess, $extendedAccess);

        return $isMasterOnlyAccess;
    }

    /**
     * Проверяем есть ли нужные права ХОТЯ БЫ В ОДНОМ ФИЛИАЛЕ
     *
     * @param UserInterface $user
     * @return int[]
     */
    public function getAmoAccessFields(UserInterface $user): array
    {
        if (! $userSalonsLinks = $this->usersSalonsLinkService->getApprovedByUserId($user->getId())) {
            return [];
        }
        $salons = $this->salonsDataProvider->getSalonsByUserSalonsLinks($userSalonsLinks);

        $fields = [
            AmoContact::CONTACT_FIELD_ACCESS_CLIENTS_DELETE => 0,
            AmoContact::CONTACT_FIELD_ACCESS_USERS_EDIT     => 0,
            AmoContact::CONTACT_FIELD_ACCESS_CLIENTS_EXCEL  => 0,
            AmoContact::CONTACT_FIELD_ACCESS_BILLING        => 0,
        ];

        foreach ($userSalonsLinks as $userSalonsLink) {
            if (! isset($salons[$userSalonsLink->getSalonId()])) {
                continue;
            }

            if (! $fields[AmoContact::CONTACT_FIELD_ACCESS_CLIENTS_DELETE]) {
                $fields[AmoContact::CONTACT_FIELD_ACCESS_CLIENTS_DELETE] = $userSalonsLink->getClientsDeleteAccess();
            }
            if (! $fields[AmoContact::CONTACT_FIELD_ACCESS_USERS_EDIT]) {
                $fields[AmoContact::CONTACT_FIELD_ACCESS_USERS_EDIT] = $userSalonsLink->getEditUsersAccess();
            }
            if (! $fields[AmoContact::CONTACT_FIELD_ACCESS_CLIENTS_EXCEL]) {
                $fields[AmoContact::CONTACT_FIELD_ACCESS_CLIENTS_EXCEL] = $userSalonsLink->getClientsExcelAccess();
            }
            if (! $fields[AmoContact::CONTACT_FIELD_ACCESS_BILLING]) {
                $fields[AmoContact::CONTACT_FIELD_ACCESS_BILLING] = $userSalonsLink->getBillingAccess();
            }

            if (array_sum($fields) === count($fields)) {
                break;
            }
        }

        return $fields;
    }
}
