<?php

namespace More\Amo\Data;

use More\Amo\Data\AmoResponse\AmoEntityContainer;
use More\Amo\Services\AmoFieldsMapper;

class AmoContact
{
    public const CONTACT_FIELD_ID_PHONE = 122535;
    public const CONTACT_FIELD_ID_EMAIL = 122537;
    public const CONTACT_FIELD_ID_USER_ID = 485058;
    public const CONTACT_FIELD_ID_COUNTRY_TITLE = 484562;
    public const CONTACT_FIELD_ID_POSITION = 122533;
    public const CONTACT_FIELD_ENUM_EMAIL = 'WORK';
    public const CONTACT_FIELD_ENUM_PHONE = 'WORK';

    public const CONTACT_FIELD_ACCESS_CLIENTS_EXCEL = 484992;
    public const CONTACT_FIELD_ACCESS_CLIENTS_DELETE = 484994;
    public const CONTACT_FIELD_ACCESS_USERS_EDIT = 484996;
    public const CONTACT_FIELD_ACCESS_BILLING = 493040;

    private int $id;
    private array $phones;
    private string $email;
    private int $userId;
    private array $leadIds;
    private int $created;

    public static function createFromAmoEntityContainer(AmoEntityContainer $amoEntityContainer): AmoContact
    {
        return (new self())
            ->setId($amoEntityContainer->getId())
            ->setEmail((string) $amoEntityContainer->getCustomFieldValue(self::CONTACT_FIELD_ID_EMAIL, true))
            ->setPhones($amoEntityContainer->getCustomFieldValues(self::CONTACT_FIELD_ID_PHONE))
            ->setUserId((int) $amoEntityContainer->getCustomFieldValue(self::CONTACT_FIELD_ID_USER_ID, true))
            ->setLeadIds((array) $amoEntityContainer->getFieldByKey(AmoFieldsMapper::FIELD_LINKED_LEAD_IDS))
            ->setCreatedTimeStamp((int) $amoEntityContainer->getFieldByKey('date_create'));
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    protected function setId(int $id): AmoContact
    {
        $this->id = $id;

        return $this;
    }

    public function getPhones(): array
    {
        return $this->phones;
    }

    public function setPhones(array $phones): AmoContact
    {
        $this->phones = $phones;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): AmoContact
    {
        $this->email = $email;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): AmoContact
    {
        $this->userId = $userId;

        return $this;
    }

    public function getLeadIds(): array
    {
        return $this->leadIds;
    }

    public function setLeadIds(array $leadIds): AmoContact
    {
        $this->leadIds = $leadIds;

        return $this;
    }

    public function hasLeadId(int $leadId): bool
    {
        return $leadId && in_array($leadId, $this->leadIds, true);
    }

    public function addLeadId(int $leadId): AmoContact
    {
        if ($leadId > 0 && ! in_array($leadId, $this->leadIds, true)) {
            $this->setLeadIds(array_merge($this->leadIds, [$leadId]));
        }

        return $this;
    }

    public function removeLeadId(int $leadId): AmoContact
    {
        if (! $leadId) {
            return $this;
        }

        if (($key = array_search($leadId, $this->leadIds, true)) === false) {
            return $this;
        }

        unset($this->leadIds[$key]);

        return $this;
    }

    public function setCreatedTimeStamp(int $timeStamp): AmoContact
    {
        $this->created = $timeStamp;

        return $this;
    }

    public function getCreatedTimeStamp(): int
    {
        return $this->created;
    }
}
