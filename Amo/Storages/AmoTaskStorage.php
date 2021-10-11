<?php

namespace More\Amo\Storages;

use Carbon\Carbon;
use Infrastructure\DateTime\DateTimeFormat;
use More\Amo\Filters\AmoTasksFilter;
use More\Amo\Services\AmoTaskService;
use More\Integration\Sipuni\Data\SipuniCallEvent;
use More\Storage\BasicStorage;

class AmoTaskStorage extends BasicStorage
{
    /**
     * @param int $taskType
     * @param int $elementId
     * @param int $period
     * @return SipuniCallEvent[]
     */
    public function getCallBackTasksWithinPeriod(int $taskType, int $elementId, int $period): array
    {
        if (!$elementId) {
            return [];
        }

        return (new AmoTasksFilter([
            'task_type'     => $taskType,
            'element_type'  => AmoTaskService::ELEMENT_TYPE_LEAD,
            'element_id'    => $elementId,
            'created_after' => Carbon::now()->subDays($period)->startOfDay()->format(DateTimeFormat::DATE_TIME_BD),
            'yc_task_type'  => AmoTaskService::YC_TASK_TYPE_SIPUNI_CALL_BACK,
        ]))->getList();
    }
}
