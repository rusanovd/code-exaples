<?php

declare(strict_types=1);

namespace More\Amo\Services;

use AmoCRM\Exception;
use Infrastructure\Queue\RabbitManagerInterface;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Exceptions\AmoConfigDisabledException;
use More\Command\Daemon\Amo\AmoSyncContactsCommand;
use More\Command\Daemon\Amo\AmoSyncLeadsCommand;

class AmoQueueService
{
    private AmoClient $amoClient;
    private RabbitManagerInterface $rabbitManager;
    private AmoLoggerService $amoLoggerService;

    /**
     * AmoQueueService constructor.
     * @param AmoClient $amoClient
     * @param RabbitManagerInterface $rabbitManager
     * @param AmoLoggerService $amoLoggerService
     */
    public function __construct(
        AmoClient $amoClient,
        RabbitManagerInterface $rabbitManager,
        AmoLoggerService $amoLoggerService
    ) {
        $this->amoClient = $amoClient;
        $this->rabbitManager = $rabbitManager;
        $this->amoLoggerService = $amoLoggerService;
    }

    /**
     * @return bool
     */
    public function isAmoEnabled(): bool
    {
        return $this->amoClient->isAmoEnabled();
    }

    /**
     * @throws AmoConfigDisabledException
     */
    public function checkAmoEnabled(): void
    {
        $this->amoClient->checkAmoEnabled();
    }

    /**
     * @param array $data
     */
    public function processContactFromQueue(array $data): void
    {
        $amoEntityFieldsDto = $this->getAmoEntityFieldsDtoFromArray($data);

        if ($amoEntityFieldsDto === null) {
            return;
        }

        try {
            $result = $this->amoClient->updateAmoContactByDto($amoEntityFieldsDto);
        } catch (AmoConfigDisabledException | AmoBadParamsException | Exception $e) {
            $this->amoLoggerService->logExceptionError($e, $data);

            return;
        }

        $this->amoLoggerService->logApiResult($amoEntityFieldsDto, AmoFieldsMapper::ENTITY_TYPE_CONTACT, $result);
    }

    /**
     * @param array $data
     */
    public function processLeadFromQueue(array $data): void
    {
        $amoEntityFieldsDto = $this->getAmoEntityFieldsDtoFromArray($data);

        if ($amoEntityFieldsDto === null) {
            return;
        }

        try {
            $result = $this->amoClient->updateAmoLeadByDto($amoEntityFieldsDto);
        } catch (AmoConfigDisabledException | AmoBadParamsException | Exception $e) {
            $this->amoLoggerService->logExceptionError($e, $data);

            return;
        }

        $this->amoLoggerService->logApiResult($amoEntityFieldsDto, AmoFieldsMapper::ENTITY_TYPE_LEAD, $result);
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     */
    public function setAmoContactToQueue(AmoEntityFieldsDto $amoEntityFieldsDto): void
    {
        try {
            $this->amoClient->checkAmoEnabled();
        } catch (AmoConfigDisabledException $e) {
            $this->amoLoggerService->logExceptionError($e, $amoEntityFieldsDto->toArray());

            return;
        }

        if (! $data = $this->getDataForQueue($amoEntityFieldsDto)) {
            return;
        }

        $this->rabbitManager->send(AmoSyncContactsCommand::QUEUE, $data);
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     */
    public function setAmoLeadToQueue(AmoEntityFieldsDto $amoEntityFieldsDto): void
    {
        try {
            $this->amoClient->checkAmoEnabled();
        } catch (AmoConfigDisabledException $e) {
            $this->amoLoggerService->logExceptionError($e, $amoEntityFieldsDto->toArray());

            return;
        }

        if (! $data = $this->getDataForQueue($amoEntityFieldsDto)) {
            return;
        }

        $this->rabbitManager->send(AmoSyncLeadsCommand::QUEUE, $data);
    }

    /**
     * @param array $data
     * @return AmoEntityFieldsDto|null
     */
    private function getAmoEntityFieldsDtoFromArray(array $data): ?AmoEntityFieldsDto
    {
        if (empty($data)) {
            return null;
        }

        $amoEntityFieldsDto = AmoEntityFieldsDto::fromArray($data);

        try {
            $amoEntityFieldsDto->checkDtoFieldsParam();
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, $data);

            return null;
        }

        return $amoEntityFieldsDto;
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return array
     */
    private function getDataForQueue(AmoEntityFieldsDto $amoEntityFieldsDto): array
    {
        try {
            $amoEntityFieldsDto->checkDtoFieldsParam();
        } catch (AmoBadParamsException $e) {
            $this->amoLoggerService->logExceptionError($e, $amoEntityFieldsDto->toArray());

            return [];
        }

        return $amoEntityFieldsDto->toArray();
    }
}
