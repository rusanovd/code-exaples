<?php

declare(strict_types=1);

namespace More\Amo\Data\Dto;

use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Services\AmoFieldsMapper;

class AmoEntityFieldsDto
{
    public const EMPTY_LEAD_IDS = [-1];

    private int $id;
    private array $fields;
    private string $name;
    private int $managerId;
    private array $leadIds;

    public function __construct(
        int $id,
        array $fields,
        string $name = '',
        int $managerId = 0,
        array $leadIds = self::EMPTY_LEAD_IDS
    ) {
        $this->id = $id;
        $this->fields = $fields;
        $this->name = $name;
        $this->managerId = $managerId;
        $this->leadIds = $leadIds;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AmoEntityFieldsDto
    {
        $this->name = $name;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    private function isDtoFieldsEmpty(): bool
    {
        return empty($this->fields) && empty($this->name) && ! $this->managerId && ! $this->isLeadsChanged();
    }

    /**
     * @throws AmoBadParamsException
     */
    public function checkDtoFieldsParam(): void
    {
        if ($this->isDtoFieldsEmpty()) {
            throw new AmoBadParamsException('Empty amo fields and name parameters');
        }
    }

    /**
     * @throws AmoBadParamsException
     */
    public function checkDtoCreateParams(): void
    {
        if ($this->isDtoFieldsEmpty()) {
            throw new AmoBadParamsException('Empty amo fields and name parameters');
        }
    }

    public function toArray(): array
    {
        return [
            AmoFieldsMapper::FIELD_ID               => $this->id,
            AmoFieldsMapper::FIELD_CONTAINER_FIELDS => $this->fields,
            AmoFieldsMapper::FIELD_NAME             => $this->name,
            AmoFieldsMapper::FIELD_MANAGER_ID       => $this->managerId,
            AmoFieldsMapper::FIELD_LINKED_LEAD_IDS  => $this->leadIds,
        ];
    }

    public static function fromArray(array $data): AmoEntityFieldsDto
    {
        return new self(
            (int) ($data[AmoFieldsMapper::FIELD_ID] ?? 0),
            (array) ($data[AmoFieldsMapper::FIELD_CONTAINER_FIELDS] ?? []),
            (string) ($data[AmoFieldsMapper::FIELD_NAME] ?? ''),
            (int) ($data[AmoFieldsMapper::FIELD_MANAGER_ID] ?? 0),
            (array) $data[AmoFieldsMapper::FIELD_LINKED_LEAD_IDS],
        );
    }

    public function getManagerId(): int
    {
        return $this->managerId;
    }

    public function getLeadIds(): array
    {
        return $this->leadIds;
    }

    public function isLeadsChanged(): bool
    {
        return $this->leadIds !== self::EMPTY_LEAD_IDS;
    }
}
