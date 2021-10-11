<?php

namespace More\Amo\Filters;

use CBindedObjectFilter;
use CIntParam;
use CStringParam;
use More\Amo\Data\AmoTask;

class AmoTasksFilter extends CBindedObjectFilter
{
    public function __construct(array $data, $prefix = '', array $parent_filters = [])
    {
        parent::__construct([
            'task_type'           => new CIntParam(),
            'element_type'        => new CIntParam(),
            'element_id'          => new CIntParam(),
            'responsible_user_id' => new CIntParam(),
            'created_after'       => new CStringParam(),
            'yc_task_type'        => new CIntParam(),
        ], $data, $prefix, $parent_filters);
    }

    /**
     * Метод должен возвращать список условий, налагаемых фильтром на запрос
     * @return array
     */
    public function getMyWhereArr(): array
    {
        $where = [];
        $tablename = $this->getTableShort();

        if ($this->F['task_type']) {
            $taskType = $this->F['task_type'];
            $where[] = "$tablename.task_type = $taskType";
        }

        if ($this->F['element_type']) {
            $elementType = $this->F['element_type'];
            $where[] = "$tablename.element_type = $elementType";
        }

        if ($this->F['element_id']) {
            $elementId = $this->F['element_id'];
            $where[] = "$tablename.element_id = $elementId";
        }

        if ($this->F['responsible_user_id']) {
            $responsibleUserId = $this->F['responsible_user_id'];
            $where[] = "$tablename.responsible_user_id = $responsibleUserId";
        }

        if ($this->F['created_after']) {
            $dateStart = $this->F['created_after'];
            $where[] = "$tablename.created_at >= '$dateStart'";
        }

        if ($this->F['yc_task_type']) {
            $taskType = $this->F['yc_task_type'];

            $where[] = "$tablename.yc_task_type = $taskType";
        }

        return $where;
    }

    /**
     * Метод должен возвращать префикс главной таблицы, используемый в JOIN'ах с таблицей
     * @return string
     */
    public function getTableSuffix(): string
    {
        return 'amotsk';
    }

    /**
     * Метод должен строить объект на основе данных запроса на выборку
     * @return string
     */
    public static function getModelClass(): string
    {
        return AmoTask::class;
    }
}
