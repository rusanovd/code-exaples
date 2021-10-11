<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CSalon;
use More\Exception\ModelNotFoundException;
use More\Integration\Intercom\Data\IntercomCompany;
use More\Integration\Intercom\Data\IntercomContact;
use More\Integration\Intercom\Log\IntercomLoggerFactory;
use More\Salon\DataProviders\SalonsDataProvider;
use More\User\Interfaces\UserInterface;
use More\User\Services\UserService;
use Psr\Log\LoggerInterface;

class IntercomDbLinkService
{
    private UserService $userService;
    private SalonsDataProvider $salonsDataProvider;
    private LoggerInterface $logger;

    /**
     * IntercomDbLinkService constructor.
     * @param UserService $userService
     * @param SalonsDataProvider $salonsDataProvider
     * @param IntercomLoggerFactory $intercomLoggerFactory
     */
    public function __construct(UserService $userService, SalonsDataProvider $salonsDataProvider, IntercomLoggerFactory $intercomLoggerFactory)
    {
        $this->userService = $userService;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->logger = $intercomLoggerFactory->getIntercomLogger();
    }

    /**
     * @param IntercomContact $intercomContact
     * @param int $salonId
     * @return string
     */
    private function getCompanyIdFromContact(IntercomContact $intercomContact, int $salonId): string
    {
        $companies = $intercomContact->getCompanies();
        $intercomId = '';
        foreach ($companies as $company) {
            if (! isset($company[IntercomFieldsMapper::FIELD_COMPANY_ID])) {
                continue;
            }
            if ((int) $company[IntercomFieldsMapper::FIELD_COMPANY_ID] === $salonId) {
                $intercomId = (string) ($company[IntercomFieldsMapper::FIELD_ID] ?? '');
                break;
            }
        }

        return $intercomId;
    }

    /**
     * @param CSalon $salon
     * @param string $intercomId
     * @return void
     */
    private function saveCompanyId(CSalon $salon, string $intercomId): void
    {
        if ($intercomId === '' || $salon->getIntercomId() === $intercomId) {
            return;
        }

        $salon->setIntercomId($intercomId)->save([IntercomFieldsMapper::FIELD_INTERCOM_ID]);

        $this->logger->info('Salon updated intercomId', [
            'salonId'    => $salon->getId(),
            'intercomId' => $intercomId,
        ]);
    }

    /**
     * @param UserInterface $user
     * @param string $intercomId
     */
    private function saveUserId(UserInterface $user, string $intercomId): void
    {
        if ($intercomId === '' || $user->getIntercomId() === $intercomId) {
            return;
        }

        $this->userService->updateUserIntercomId($user, $intercomId);

        $this->logger->info('User updated intercomId', [
            'userId'     => $user->getId(),
            'intercomId' => $intercomId,
        ]);
    }

    /**
     * @param IntercomContact $intercomContact
     * @param int $salonId
     * @throws ModelNotFoundException
     */
    private function saveUserCompanyId(IntercomContact $intercomContact, int $salonId): void
    {
        if ($salonId === 0) {
            return;
        }

        $salon = $this->salonsDataProvider->getSalonById($salonId);
        $intercomId = $this->getCompanyIdFromContact($intercomContact, $salon->getId());
        $this->saveCompanyId($salon, $intercomId);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function processLinkDbFlag(array &$data): bool
    {
        $result = false;
        if (isset($data[IntercomFieldsMapper::FIELD_NEED_LINK_DB])) {
            $result = (bool) $data[IntercomFieldsMapper::FIELD_NEED_LINK_DB];
            unset($data[IntercomFieldsMapper::FIELD_NEED_LINK_DB]);
        }

        return $result;
    }

    /**
     * @param array $data
     * @return int
     */
    public function processUserCompanyIdFlag(array &$data): int
    {
        $companyId = 0;
        if (isset($data[IntercomFieldsMapper::FIELD_USER_SALON_ID])) {
            $companyId = (int) $data[IntercomFieldsMapper::FIELD_USER_SALON_ID];
            unset($data[IntercomFieldsMapper::FIELD_USER_SALON_ID]);
        }

        return $companyId;
    }

    /**
     * @param IntercomCompany $intercomCompany
     * @throws ModelNotFoundException
     */
    public function linkToDbIntercomCompany(IntercomCompany $intercomCompany): void
    {
        if (empty($intercomCompany->getId()) || empty($intercomCompany->getSalonId())) {
            return;
        }

        $salon = $this->salonsDataProvider->getSalonById($intercomCompany->getSalonId());
        $this->saveCompanyId($salon, $intercomCompany->getId());
    }

    /**
     * @param IntercomContact $intercomContact
     * @param int $salonId
     * @throws ModelNotFoundException
     */
    public function linkToDbIntercomContact(IntercomContact $intercomContact, int $salonId): void
    {
        if (empty($intercomContact->getId()) || empty($intercomContact->getUserId())) {
            return;
        }

        $user = $this->userService->findUserById($intercomContact->getUserId());

        if ($user === null) {
            return;
        }

        $this->saveUserId($user, $intercomContact->getId());
        $this->saveUserCompanyId($intercomContact, $salonId);
    }
}
