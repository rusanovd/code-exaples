<?php

namespace More\Amo\Data;

use CCachableIdObject;
use DateTimeInterface;
use More\Amo\Services\AmoTaskService;

class AmoTask extends CCachableIdObject
{
    protected static $table_name = 'amocrm_tasks';

    protected static $table_fields = [
        'task_id'             => null,
        'task_type'           => 0,
        'element_type'        => 0,
        'element_id'          => 0,
        'responsible_user_id' => 0,
        'created_at'          => null,
        'is_completed'        => null,
        'complete_till_at'    => null,
        'created_by'          => null,
        'account_id'          => null,
        'group_id'            => null,
        'text'                => '',
        'yc_task_type'        => null,
    ];

    public function getTaskId(): ?int
    {
        return $this->getAsNullableInt('task_id');
    }

    public function setTaskId(?int $taskId): AmoTask
    {
        $this->set('task_id', $taskId);

        return $this;
    }

    public function getTaskType(): int
    {
        return $this->getAsInt('task_type');
    }

    public function setTaskType(int $taskType): AmoTask
    {
        $this->set('task_type', $taskType);

        return $this;
    }

    public function getElementType(): int
    {
        return $this->getAsInt('element_type');
    }

    public function setElementType(int $elementType): AmoTask
    {
        $this->set('element_type', $elementType);

        return $this;
    }

    public function getElementId(): int
    {
        return $this->getAsInt('element_id');
    }

    public function setElementId(int $elementId): AmoTask
    {
        $this->set('element_id', $elementId);

        return $this;
    }

    public function getResponsibleUserId(): int
    {
        return $this->getAsInt('responsible_user_id');
    }

    public function setResponsibleUserId(int $responsibleUserId): AmoTask
    {
        $this->set('responsible_user_id', $responsibleUserId);

        return $this;
    }

    public function getText(): string
    {
        return $this->getAsString('text');
    }

    public function setText(string $text): AmoTask
    {
        $this->set('text', $text);

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->getAsDateTime('created_at');
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): AmoTask
    {
        $this->set('created_at', $createdAt);

        return $this;
    }

    public function getIsCompleted(): ?bool
    {
        return $this->getAsNullableBoolean('is_completed');
    }

    public function setIsCompleted(?bool $isCompleted): AmoTask
    {
        $this->set('is_completed', $isCompleted);

        return $this;
    }

    public function getCompleteTillAt(): ?DateTimeInterface
    {
        return $this->getAsDateTime('complete_till_at');
    }

    public function setCompleteTillAt(?DateTimeInterface $completeTillAt): AmoTask
    {
        $this->set('complete_till_at', $completeTillAt);

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->getAsNullableInt('created_by');
    }

    public function setCreatedBy(?int $createdBy): AmoTask
    {
        $this->set('created_by', $createdBy);

        return $this;
    }

    public function getAccountId(): ?int
    {
        return $this->getAsNullableInt('account_id');
    }

    public function setAccountId(?int $accountId): AmoTask
    {
        $this->set('account_id', $accountId);

        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->getAsNullableInt('group_id');
    }

    public function setGroupId(?int $groupId): AmoTask
    {
        $this->set('group_id', $groupId);

        return $this;
    }

    public function isSipuniCallBackTrigger(): bool
    {
        return $this->getYcTaskType() === AmoTaskService::YC_TASK_TYPE_SIPUNI_CALL_BACK;
    }

    public function getYcTaskType(): int
    {
        return $this->getAsNullableInt('yc_task_type') ?? 0;
    }

    public function setYcTaskType(int $ycIsCallBackTrigger): AmoTask
    {
        $this->set('yc_task_type', $ycIsCallBackTrigger);

        return $this;
    }
}
