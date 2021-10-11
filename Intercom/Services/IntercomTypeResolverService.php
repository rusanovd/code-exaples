<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use Infrastructure\Queue\RabbitManagerInterface;
use More\Command\Daemon\Intercom\IntercomSyncCommand;
use More\Exception\ModelNotFoundException;
use More\Integration\Intercom\Data\Response\IntercomEntityFactory;
use More\Integration\Intercom\Exceptions\IntercomApiException;
use More\Integration\Intercom\Exceptions\IntercomBadTypeException;
use More\Integration\Intercom\Exceptions\IntercomConfigDisabledException;
use More\Integration\Intercom\Exceptions\IntercomEmptyOptionsException;
use More\Integration\Intercom\Log\IntercomLoggerFactory;
use Psr\Log\LoggerInterface;

class IntercomTypeResolverService
{
    private IntercomApiClient $intercomApiClient;
    private IntercomDbLinkService $intercomDbLinkService;
    private IntercomEntityFactory $intercomEntityFactory;
    private RabbitManagerInterface $rabbitManager;
    private LoggerInterface $logger;

    /**
     * IntercomTypeResolverService constructor.
     * @param IntercomApiClient $intercomApiClient
     * @param IntercomDbLinkService $intercomDbLinkService
     * @param IntercomEntityFactory $intercomEntityFactory
     * @param RabbitManagerInterface $rabbitManager
     * @param IntercomLoggerFactory $intercomLoggerFactory
     * @throws \Exception
     */
    public function __construct(
        IntercomApiClient $intercomApiClient,
        IntercomDbLinkService $intercomDbLinkService,
        IntercomEntityFactory $intercomEntityFactory,
        RabbitManagerInterface $rabbitManager,
        IntercomLoggerFactory $intercomLoggerFactory
    ) {
        $this->intercomApiClient = $intercomApiClient;
        $this->intercomDbLinkService = $intercomDbLinkService;
        $this->intercomEntityFactory = $intercomEntityFactory;
        $this->rabbitManager = $rabbitManager;
        $this->logger = $intercomLoggerFactory->getIntercomLogger();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->intercomApiClient->isEnabled();
    }

    /**
     * @throws IntercomConfigDisabledException
     */
    public function checkIsEnabled(): void
    {
        $this->intercomApiClient->checkIsEnabled();
    }

    /**
     * @param array $data
     * @throws IntercomBadTypeException
     */
    private function checkIntercomType(array $data): void
    {
        if (empty($data[IntercomFieldsMapper::FIELD_TYPE])) {
            throw new IntercomBadTypeException;
        }

        if (! in_array($data[IntercomFieldsMapper::FIELD_TYPE], [IntercomFieldsMapper::FIELD_COMPANY, IntercomFieldsMapper::FIELD_CONTACT], true)) {
            throw new IntercomBadTypeException;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    private function processIntercomTypeFlag(array &$data): string
    {
        if (! isset($data[IntercomFieldsMapper::FIELD_TYPE])) {
            return '';
        }

        $type = (string) $data[IntercomFieldsMapper::FIELD_TYPE];

        unset($data[IntercomFieldsMapper::FIELD_TYPE]);

        return $type;
    }

    /**
     * @param array $data
     * @param string $type
     */
    public function setIntercomType(array &$data, string $type): void
    {
        $data[IntercomFieldsMapper::FIELD_TYPE] = $type;
    }

    /**
     * @param array $data
     * @throws IntercomApiException
     * @throws IntercomBadTypeException
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     * @throws ModelNotFoundException
     */
    public function updateIntercomByTypeResolver(array $data): void
    {
        $this->checkIntercomType($data);

        $type = $this->processIntercomTypeFlag($data);

        $this->logger->info('Get intercom ' . $type . ' from queue', $data);

        if ($type === IntercomFieldsMapper::FIELD_COMPANY) {
            $this->updateIntercomByTypeCompany($data);
        } elseif ($type === IntercomFieldsMapper::FIELD_CONTACT) {
            $this->updateIntercomByTypeContact($data);
        }
    }

    /**
     * @param array $data
     * @throws IntercomConfigDisabledException
     * @throws ModelNotFoundException
     * @throws IntercomApiException
     * @throws IntercomEmptyOptionsException
     */
    private function updateIntercomByTypeCompany(array $data): void
    {
        $isNeedLinkDb = $this->intercomDbLinkService->processLinkDbFlag($data);

        $response = $this->intercomApiClient->updateCompany($data);

        if ($response !== null && $isNeedLinkDb) {
            $intercomCompany = $this->intercomEntityFactory->getCompany($response);
            $this->intercomDbLinkService->linkToDbIntercomCompany($intercomCompany);
        }
    }

    /**
     * @param array $data
     * @throws IntercomApiException
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     * @throws ModelNotFoundException
     */
    private function updateIntercomByTypeContact(array $data): void
    {
        $salonId = $this->intercomDbLinkService->processUserCompanyIdFlag($data);
        $isNeedLinkDb = $this->intercomDbLinkService->processLinkDbFlag($data);

        $response = $this->intercomApiClient->updateContact($data);

        if ($response !== null && $isNeedLinkDb) {
            $intercomContact = $this->intercomEntityFactory->getContact($response);
            $this->intercomDbLinkService->linkToDbIntercomContact($intercomContact, $salonId);
        }
    }

    /**
     * @param array $options
     * @param string $type
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     */
    private function setIntercomDataToQueue(array $options, string $type): void
    {
        $this->intercomApiClient->checkParams($options);
        $this->setIntercomType($options, $type);
        $this->rabbitManager->send(IntercomSyncCommand::QUEUE, $options);
    }

    /**
     * @param array $options
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     */
    public function setIntercomCompanyToQueue(array $options): void
    {
        $this->setIntercomDataToQueue($options, IntercomFieldsMapper::FIELD_COMPANY);
    }

    /**
     * @param array $options
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     */
    public function setIntercomContactToQueue(array $options): void
    {
        $this->setIntercomDataToQueue($options, IntercomFieldsMapper::FIELD_CONTACT);
    }
}
