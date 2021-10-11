<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CSalon;
use More\Integration\Intercom\Exceptions\IntercomConfigDisabledException;
use More\Integration\Intercom\Exceptions\IntercomException;
use More\Integration\Intercom\Log\IntercomLoggerFactory;
use More\Salon\DataProviders\SalonsDataProvider;
use Psr\Log\LoggerInterface;

class IntercomScriptService
{
    private const LIMIT_SALONS_PER_COMMAND = 10000;

    private IntercomService $intercomService;
    private IntercomContactService $intercomContactService;
    private IntercomCompanyService $intercomCompanyService;
    private IntercomRequestDelayService $intercomRequestDelayService;
    private SalonsDataProvider $salonsDataProvider;
    private LoggerInterface $logger;

    public function __construct(
        IntercomService $intercomService,
        IntercomContactService $intercomContactService,
        IntercomCompanyService $intercomCompanyService,
        IntercomRequestDelayService $intercomRequestDelayService,
        SalonsDataProvider $salonsDataProvider,
        IntercomLoggerFactory $intercomLoggerFactory
    ) {
        $this->intercomService = $intercomService;
        $this->intercomContactService = $intercomContactService;
        $this->intercomCompanyService = $intercomCompanyService;
        $this->intercomRequestDelayService = $intercomRequestDelayService;
        $this->salonsDataProvider = $salonsDataProvider;
        $this->logger = $intercomLoggerFactory->getIntercomScriptLogger();
    }

    /**
     * @throws IntercomConfigDisabledException
     */
    public function checkIsEnabled(): void
    {
        $this->intercomService->checkIsEnabled();
    }

    /**
     * @param int[] $salonIds
     */
    public function processCompanyAndUsersBySalonIds(array $salonIds): void
    {
        foreach ($salonIds as $salonId) {
            if (! $salon = $this->salonsDataProvider->findSalonById((int) $salonId)) {
                continue;
            }
            $this->processCompanyAndUsersBySalon($salon);
        }
    }

    /**
     * @param int[] $salonIds
     * @param bool $flag
     */
    public function processCompaniesScriptFlag(array $salonIds, bool $flag): void
    {
        $this->logger->info('IntercomScript' . "\n" . 'Start setting flag (' . $flag . ') for ' . count($salonIds) . ' salons ');
        foreach ($salonIds as $salonId) {
            if (! $salon = $this->salonsDataProvider->findSalonById((int) $salonId)) {
                $this->logger->info('IntercomScript' . "\n" . 'salon ' . $salonId . 'not found id db.');
                continue;
            }

            $options = $this->intercomCompanyService->getCompanyScriptFlagOptions($salon, $flag);
            $this->intercomService->updateCompanyByQueue($options);
            $this->logger->info('IntercomScript' . "\n" . 'salon ' . $salonId . 'put to queue', $options);
            $this->intercomRequestDelayService->markRequest();
        }
        $this->logger->info('IntercomScript' . "\n" . 'End setting flag.');
    }

    private function processCompanyAndUsersBySalon(CSalon $salon): void
    {
        $companyOptions = $this->intercomCompanyService->getCompanyOptionsOnCreating($salon);
        $users = $salon->getSalonUsers();

        if ($users) {
            foreach ($users as $user) {
                $this->intercomService->updateIntercomRelatedOptions(
                    $this->intercomContactService->getContactOptionsByUserAndCompany($user, $salon),
                    $companyOptions
                );
                $this->intercomRequestDelayService->markRequest();
            }
        } else {
            $this->intercomService->updateIntercomCompanyEntity($companyOptions);
            $this->intercomRequestDelayService->markRequest();
        }

        $this->logger->info('IntercomScript' . "\n" . 'salon updated.', [
            'salonId'  => $salon->getId(),
            'usersCnt' => count($users),
        ]);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @throws IntercomConfigDisabledException
     * @throws IntercomException
     */
    public function processCompanyAndUsers(int $limit, int $offset): void
    {
        $this->checkIsEnabled();

        if (! $limit) {
            $limit = self::LIMIT_SALONS_PER_COMMAND;
        }

        if (! $salonIds = $this->salonsDataProvider->getSalonIdsByLimitOffset($limit, $offset)) {
            throw new IntercomException('No salon Ids found.');
        }

        $this->processCompanyAndUsersBySalonIds($salonIds);
    }
}
