<?php

namespace More\Amo\Factories;

use CSalon;
use More\Amo\Data\AmoLead;
use More\Amo\Data\AmoTask;
use More\Amo\Exceptions\AmoException;
use More\Amo\Services\AmoDateFormatter;
use More\Amo\Services\AmoTaskService;
use More\User\Services\DataProviders\UserDataProvider;

class AmoTaskFactory
{
    private const TASK_TYPE_LEAD_REACTIVATION = 1557447; // Возвращение лида

    private UserDataProvider $userDataProvider;
    private AmoDateFormatter $amoDateFormatter;

    /**
     * AmoTaskFactory constructor.
     * @param UserDataProvider $userDataProvider
     * @param AmoDateFormatter $amoDateFormatter
     */
    public function __construct(UserDataProvider $userDataProvider, AmoDateFormatter $amoDateFormatter)
    {
        $this->userDataProvider = $userDataProvider;
        $this->amoDateFormatter = $amoDateFormatter;
    }

    /**
     * @param CSalon $salon
     * @param int $taskType
     * @param string $text
     * @return AmoTask
     * @throws AmoException
     */
    public function createForSalon(CSalon $salon, int $taskType, string $text): AmoTask
    {
        $salonManager = $this->userDataProvider->getUserById($salon->getManagerId());

        if (! $salonManager) {
            throw AmoException::createWithUserMessage(_t("У филиала {$salon->getId()} нет менеджера"));
        }

        $amoManagerId = $salonManager->getAmoUserId();

        if (! $amoManagerId) {
            throw AmoException::createWithUserMessage(_t("Менеджер (salon #{$salon->getId()}) не имеет amo user id"));
        }

        $amoLeadId = $salon->getAmoId();

        if (! $amoLeadId) {
            throw AmoException::createWithUserMessage(_t("Филиал {$salon->getId()} не имеет amo id"));
        }

        return (new AmoTask())
            ->setTaskType($taskType)
            ->setElementType(AmoTaskService::ELEMENT_TYPE_LEAD)
            ->setElementId($amoLeadId)
            ->setResponsibleUserId($amoManagerId)
            ->setText($text)
            ->setCreatedAt(carbon());
    }

    /**
     * @param AmoLead $amoLead
     * @param string $text
     * @return AmoTask
     */
    public function createForLeadReactivation(AmoLead $amoLead, string $text): AmoTask
    {
        return (new AmoTask())
            ->setTaskType(self::TASK_TYPE_LEAD_REACTIVATION)
            ->setElementType(AmoTaskService::ELEMENT_TYPE_LEAD)
            ->setElementId($amoLead->getId())
            ->setResponsibleUserId($amoLead->getResponsibleUserId())
            ->setText($text)
            ->setCreatedAt(carbon())
            ->setCompleteTillAt($this->amoDateFormatter->calculateTaskCompleteTillEndOfThisWeekdayDate(carbon()));
    }
}
