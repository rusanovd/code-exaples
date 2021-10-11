<?php

namespace More\Amo\Data\AmoResponse;

class AmoEntityContainer
{
    public const KEY_VALUE = 'value';
    public const KEY_ENUM = 'enum';

    /**
     * @var array
     */
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
     * @return int
     */
    public function getId(): int
    {
        return (int) ($this->entityArray['id'] ?? 0);
    }

    public function getName(): string
    {
        return (string) ($this->entityArray['name'] ?? '');
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFieldByKey(string $key)
    {
        return $this->entityArray[$key] ?? null;
    }

    /**
     * @return int
     */
    public function getResponsibleUserId(): int
    {
        return (int) ($this->entityArray['responsible_user_id'] ?? 0);
    }

    /**
     * @return int
     */
    public function getModifiedUserId(): int
    {
        return (int) ($this->entityArray['modified_user_id'] ?? 0);
    }

    /**
     * @param int $customFieldId
     * @param bool $onlyFirst
     * @param string $key
     * @return mixed
     */
    public function getCustomFieldValue(int $customFieldId, bool $onlyFirst = false, $key = self::KEY_VALUE)
    {
        $customFieldArray = array_first(
            $this->entityArray['custom_fields'] ?? [],
            static function ($customField) use ($customFieldId) {
                $currentCustomFieldId = (int) ($customField['id'] ?? 0);

                return $customFieldId === $currentCustomFieldId;
            }
        );

        $value = null;
        if ($customFieldArray) {
            $value = $customFieldArray['values'] ?? null;

            if ($onlyFirst) {
                $value = $value[0][$key] ?? null;
            }
        }

        return $value;
    }

    /**
     * @param int $customFieldId
     * @param string $key
     * @return array
     */
    public function getCustomFieldValues(int $customFieldId, $key = self::KEY_VALUE): array
    {
        $values = [];
        if (! $rows = $this->getCustomFieldValue($customFieldId, false, $key)) {
            return $values;
        }

        foreach ($rows as $row) {
            if (! empty($row[$key])) {
                $values[] = $row[$key];
            }
        }

        return $values;
    }
}
