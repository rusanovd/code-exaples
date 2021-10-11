<?php

namespace More\Amo\Services;

use Carbon\Carbon;
use CSalon;
use More\Amo\Data\AmoLead;
use More\Amo\Data\AmoTask;
use More\Amo\Data\AmoUser;
use More\Amo\Exceptions\AmoException;
use More\Amo\Factories\AmoTaskFactory;
use More\Amo\Storages\AmoTaskStorage;
use Psr\Log\LoggerInterface;

class AmoTaskService
{
    public const TASK_TYPE_NOT_ACTIVE = 1321617; // НЕ АКТИВЕН
    public const TASK_TYPE_LICENSE_FINISHED = 1321614; // Срок лицензии (5 дней)
    public const TASK_TYPE_LICENSE_FINISHED_SOON = 1384866; // Скоро Конец лицензии (39 дней)

    public const ELEMENT_TYPE_LEAD = 2;

    private const MAX_TIME_TO_ASSIGN_TASK_TODAY = '17:00:00'; // максимальное время, до которого задача ставится на сегодня
    private const TASK_TYPE_LOAD_REDUCE = 1598895; // Снизить нагрузку

    public const YC_TASK_TYPE_SIPUNI_CALL_BACK = 4706; // Перезвон Sipuni, см JIRA:YC-4706

    private AmoClient $amoClient;
    private AmoTaskStorage $amoTaskStorage;
    private AmoTaskFactory $amoTaskFactory;
    private AmoLoggerService $amoLoggerService;
    private AmoDateFormatter $amoDateFormatter;

    /**
     * AmoTaskService constructor.
     * @param AmoClient $amoClient
     * @param AmoTaskStorage $amoTaskStorage
     * @param AmoTaskFactory $amoTaskFactory
     * @param AmoLoggerService $amoLoggerService
     * @param AmoDateFormatter $amoDateFormatter
     */
    public function __construct(
        AmoClient $amoClient,
        AmoTaskStorage $amoTaskStorage,
        AmoTaskFactory $amoTaskFactory,
        AmoLoggerService $amoLoggerService,
        AmoDateFormatter $amoDateFormatter
    ) {
        $this->amoClient = $amoClient;
        $this->amoTaskStorage = $amoTaskStorage;
        $this->amoTaskFactory = $amoTaskFactory;
        $this->amoLoggerService = $amoLoggerService;
        $this->amoDateFormatter = $amoDateFormatter;
    }

    /**
     * @param CSalon $salon
     * @param int $period
     * @return bool
     */
    public function hasCallBackTaskForSalon(CSalon $salon, int $period): bool
    {
        $tasks = $this->amoTaskStorage->getCallBackTasksWithinPeriod(
            self::TASK_TYPE_LOAD_REDUCE,
            $salon->getAmoId(),
            $period
        );

        return count($tasks) > 0;
    }

    /**
     * @param CSalon $salon
     * @param string $text
     * @return AmoTask|null
     */
    public function createCallBackTaskForSalon(CSalon $salon, string $text): ?AmoTask
    {
        try {
            $amoTask = $this->amoTaskFactory->createForSalon(
                $salon,
                self::TASK_TYPE_LOAD_REDUCE,
                $text
            );

            $amoTask->setYcTaskType(self::YC_TASK_TYPE_SIPUNI_CALL_BACK);

            $shouldPutTaskForToday = Carbon::now()->lessThan(Carbon::now()->setTimeFromTimeString(self::MAX_TIME_TO_ASSIGN_TASK_TODAY));

            $completeTill = $shouldPutTaskForToday ? Carbon::today() : Carbon::tomorrow();

            $amoTask->setCompleteTillAt($completeTill->endOfDay());

            return $this->createTaskApi($amoTask);
        } catch (AmoException $e) {
            $this->amoLoggerService->logExceptionError($e, ['salonId' => $salon->getId()]);
        }

        return null;
    }

    /**
     * @param CSalon $salon
     * @param int $taskType
     * @param string $text
     * @param LoggerInterface|null $logger
     */
    public function createTaskForSalon(CSalon $salon, int $taskType, string $text, ?LoggerInterface $logger = null): void
    {
        try {
            $amoTask = $this->amoTaskFactory->createForSalon($salon, $taskType, $text);

            if ($logger !== null) {
                $logger->info('Task creation attempt', [
                    'salonId'  => $salon->getId(),
                    'taskType' => $amoTask->getTaskType(),
                    'taskText' => $amoTask->getText(),
                ]);
            }

            $amoTask = $this->createTaskApi($amoTask);

            if ($logger !== null) {
                $logger->info('Task created by api', [
                    'taskId'   => $amoTask->getTaskId(),
                    'leadId'   => $amoTask->getElementId(),
                    'salonId'  => $salon->getId(),
                ]);
            }
        } catch (AmoException $e) {
            if ($logger !== null) {
                $logger->error('Amo task has not been created', [
                    'salonId'   => $salon->getId(),
                    'exception' => $e,
                ]);
            }
        }
    }

    /**
     * Создать задачу по реактивации лида
     *
     * @param AmoLead $amoLead
     * @param string $text Текст задачи
     * @throws AmoException
     */
    public function createTaskForAmoLeadReactivation(AmoLead $amoLead, string $text): void
    {
        $this->createTaskApi($this->amoTaskFactory->createForLeadReactivation($amoLead, $text));
    }

    /**
     * @param AmoTask $amoTask
     * @return AmoTask
     * @throws AmoException
     */
    public function createTaskApi(AmoTask $amoTask): AmoTask
    {
        $amoTaskId = $this->amoClient->addTask($amoTask);

        $amoTask->setTaskId($amoTaskId);
        $amoTask->fullSave();

        return $amoTask;
    }

    /**
     * Найти в AmoCRM пользователя по ID
     *
     * @param int $amoUserId ID пользователя AmoCRM
     * @return AmoUser|null
     */
    public function findAmoUserById(int $amoUserId): ?AmoUser
    {
        return $this->amoClient->findAmoUserById($amoUserId);
    }

    /**
     * Найти в AmoCRM сделку (лида) по ID
     *
     * @param int $amoLeadId ID сделки (лида)
     * @return AmoLead|null
     */
    public function findAmoLeadById(int $amoLeadId): ?AmoLead
    {
        return $this->amoClient->findAmoLeadById($amoLeadId);
    }

    /**
     * Вывести дату в переданном формате и временной зоне AmoCRM
     *
     * @param \DateTimeImmutable $date
     * @param string $format
     * @return string
     */
    public function formatDateAndTimezone(\DateTimeImmutable $date, string $format): string
    {
        return $this->amoDateFormatter->formatDateAndTimezone($date, $format);
    }
}
