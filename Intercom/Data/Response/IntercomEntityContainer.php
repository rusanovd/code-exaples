<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Data\Response;

use More\Integration\Intercom\Services\IntercomFieldsMapper;

class IntercomEntityContainer
{
    private array $entityArray;

    /**
     * AmoEntity constructor.
     * @param array $entityArray
     */
    public function __construct(array $entityArray)
    {
        $this->entityArray = $entityArray;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) ($this->entityArray[IntercomFieldsMapper::FIELD_ID] ?? '');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string) ($this->entityArray[IntercomFieldsMapper::FIELD_TYPE] ?? '');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) ($this->entityArray[IntercomFieldsMapper::FIELD_NAME] ?? '');
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return (string) ($this->entityArray[IntercomFieldsMapper::FIELD_APP_ID] ?? '');
    }

    /**
     * @return int
     */
    public function getCreatedTime(): int
    {
        return (int) ($this->entityArray[IntercomFieldsMapper::FIELD_CREATED_AT] ?? 0);
    }

    /**
     * @return int
     */
    public function getRemoteCreatedTime(): int
    {
        return (int) ($this->entityArray[IntercomFieldsMapper::FIELD_REMOTE_CREATED_AT] ?? 0);
    }

    /**
     * @return int
     */
    public function getUpdatedTime(): int
    {
        return (int) ($this->entityArray[IntercomFieldsMapper::FIELD_UPDATED_AT] ?? 0);
    }

    /**
     * @return int
     */
    public function getContactId(): int
    {
        return (int) ($this->entityArray[IntercomFieldsMapper::FIELD_USER_ID] ?? 0);
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return (int) ($this->entityArray[IntercomFieldsMapper::FIELD_COMPANY_ID] ?? 0);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttributeByKey(string $key)
    {
        return $this->entityArray[$key] ?? null;
    }

    /**
     * @param string $attributeName
     * @return array
     */
    public function getAttributeMultipleByName(string $attributeName): array
    {
        $attributeData = $this->getAttributeByKey($attributeName);

        if (! is_array($attributeData)) {
            return [];
        }

        return (array) ($attributeData[$attributeName] ?? []);
    }
}
