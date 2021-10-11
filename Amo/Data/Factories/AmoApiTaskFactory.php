<?php

namespace More\Amo\Data\Factories;

use AmoCRM\Client as AmoApiClient;
use AmoCRM\Models\Task as APIAmoTask;
use Infrastructure\DateTime\DateTimeFormat;
use More\Amo\Data\AmoTask;

class AmoApiTaskFactory
{
    private AmoApiClient $amoApiClient;

    /**
     * AmoTaskFactory constructor.
     * @param AmoApiClient $amoApiClient
     */
    public function __construct(AmoApiClient $amoApiClient)
    {
        $this->amoApiClient = $amoApiClient;
    }

    /**
     * @param AmoTask $amoTask
     * @return APIAmoTask
     */
    public function createFromYcAmoTask(AmoTask $amoTask): APIAmoTask
    {
        $task = $this->amoApiClient->task;

        $task['task_type'] = $amoTask->getTaskType();
        $task['element_type'] = $amoTask->getElementType();
        $task['element_id'] = $amoTask->getElementId();
        $task['responsible_user_id'] = $amoTask->getResponsibleUserId();
        $task['text'] = $amoTask->getText();
        $task['created_at'] = $amoTask->getCreatedAt();

        if (null !== $amoTask->getCompleteTillAt()) {
            $task['complete_till'] = $amoTask->getCompleteTillAt()->format(DateTimeFormat::DATE_TIME_BD);
        }

        return $task;
    }
}
