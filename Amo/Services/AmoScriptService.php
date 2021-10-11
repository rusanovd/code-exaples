<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Exceptions\AmoConfigDisabledException;
use More\Salon\DataProviders\SalonsDataProvider;
use More\User\Interfaces\UserInterface;
use More\User\Services\DataProviders\UserDataProvider;
use Psr\Cache\InvalidArgumentException;
use Throwable;

class AmoScriptService
{
    private const LIMIT_ENTITIES = 10000;

    private AmoRequestDelayService $amoRequestDelayService;
    private AmoLoggerService $amoLoggerService;
    private AmoQueueService $amoQueueService;
    private AmoLeadService $amoLeadService;
    private AmoContactService $amoContactService;
    private SalonsDataProvider $salonsDataProvider;
    private UserDataProvider $userDataProvider;

    public function __construct(
        AmoRequestDelayService $amoRequestDelayService,
        AmoLoggerService $amoLoggerService,
        AmoQueueService $amoQueueService,
        AmoLeadService $amoLeadService,
        AmoContactService $amoContactService,
        SalonsDataProvider $salonsDataProvider,
        UserDataProvider $userDataProvider
    ) {
        $this->amoRequestDelayService = $amoRequestDelayService;
        $this->amoLoggerService = $amoLoggerService;
        $this->amoQueueService = $amoQueueService;
        $this->amoLeadService = $amoLeadService;
        $this->amoContactService = $amoContactService;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->userDataProvider = $userDataProvider;
    }

    /**
     * @throws AmoConfigDisabledException
     */
    public function checkIsEnabled(): void
    {
        $this->amoQueueService->checkAmoEnabled();
    }

    /**
     * @param array $salonIds
     * @throws AmoConfigDisabledException
     * @throws InvalidArgumentException
     */
    public function processCompaniesAndUsersBySalonIds(array $salonIds): void
    {
        $this->checkIsEnabled();
        foreach ($salonIds as $salonId) {
            $salon = $this->salonsDataProvider->findSalonById((int) $salonId);
            if ($salon === null) {
                continue;
            }

            $users = $salon->getSalonUsers();

            if ($users) {
                foreach ($users as $user) {
                    $this->updateContactByUser($user);
                }
            } else {
                $this->updateCompanyBySalon($salon);
            }

            $this->amoLoggerService->logScript('salon and users updated.', [
                'salonId'  => $salon->getId(),
                'amoId'    => $salon->getAmoId(),
                'usersCnt' => count($users),
            ]);
        }
    }

    /**
     * @param int $limit
     * @param int $offset
     * @throws AmoBadParamsException
     * @throws AmoConfigDisabledException
     * @throws InvalidArgumentException
     */
    public function processCompaniesAndUsers(int $limit, int $offset): void
    {
        $this->checkIsEnabled();

        if (! $limit) {
            $limit = self::LIMIT_ENTITIES;
        }

        $salonIds = $this->salonsDataProvider->getSalonIdsByLimitOffset($limit, $offset);

        if (! $salonIds) {
            throw new AmoBadParamsException('No salon ids found.');
        }

        $this->processCompaniesAndUsersBySalonIds($salonIds);
    }

    /**
     * @param array $salonIds
     * @throws AmoConfigDisabledException
     * @throws InvalidArgumentException
     */
    public function processCompaniesBySalonIds(array $salonIds): void
    {
        $this->checkIsEnabled();
        foreach ($salonIds as $salonId) {
            $salon = $this->salonsDataProvider->findSalonById((int) $salonId);
            if ($salon === null) {
                continue;
            }
            $this->updateCompanyBySalon($salon);
        }
    }

    /**
     * @param int $limit
     * @param int $offset
     * @throws AmoBadParamsException
     * @throws AmoConfigDisabledException
     * @throws InvalidArgumentException
     */
    public function processCompanies(int $limit, int $offset): void
    {
        $this->checkIsEnabled();

        if (! $limit) {
            $limit = self::LIMIT_ENTITIES;
        }

        $salonIds = $this->salonsDataProvider->getSalonIdsByLimitOffset($limit, $offset);

        if (! $salonIds) {
            throw new AmoBadParamsException('No salon Ids found.');
        }

        $this->processCompaniesBySalonIds($salonIds);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @throws AmoBadParamsException
     * @throws AmoConfigDisabledException
     */
    public function processContacts(int $limit, int $offset): void
    {
        $this->checkIsEnabled();

        if (! $limit) {
            $limit = self::LIMIT_ENTITIES;
        }

        $userIds = $this->userDataProvider->getUserIdsByLimitOffset($limit, $offset);

        if (! $userIds) {
            throw new AmoBadParamsException('No users Ids found.');
        }

        $this->processContactsByUserIds($userIds);
    }

    /**
     * @param array $userIds
     * @throws AmoConfigDisabledException
     */
    public function processContactsByUserIds(array $userIds): void
    {
        $this->checkIsEnabled();

        foreach ($userIds as $userId) {
            $user = $this->userDataProvider->findUserById((int) $userId);
            if ($user === null) {
                continue;
            }
            $this->updateContactByUser($user);
        }
    }

    private function updateContactByUser(UserInterface $user): void
    {
        if (! $user->getAmoContactId()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoContactService->getAmoContactWithAccessDto($user);
        } catch (Throwable $e) {
            $this->amoLoggerService->logScriptException($e, ['userId' => $user->getId()]);
            $amoEntityFieldsDto = null;
        }

        if ($amoEntityFieldsDto === null) {
            return;
        }

        $this->amoQueueService->setAmoContactToQueue($amoEntityFieldsDto);
        $this->amoLoggerService->logScript('User updated', ['userId' => $user->getId()]);
        $this->amoRequestDelayService->markRequest();
    }

    /**
     * @param CSalon $salon
     * @throws InvalidArgumentException
     */
    private function updateCompanyBySalon(CSalon $salon): void
    {
        if (! $salon->getAmoId()) {
            return;
        }

        try {
            $amoEntityFieldsDto = $this->amoLeadService->getAmoLeadForScriptDto($salon);
        } catch (Throwable $e) {
            $this->amoLoggerService->logScriptException($e, ['salonId' => $salon->getId()]);

            return;
        }
        $this->amoQueueService->setAmoLeadToQueue($amoEntityFieldsDto);
        $this->amoLoggerService->logScript('Salon updated', ['salonId' => $salon->getId()]);
        $this->amoRequestDelayService->markRequest();
    }
}
