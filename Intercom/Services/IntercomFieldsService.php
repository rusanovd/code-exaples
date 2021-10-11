<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use Infrastructure\DateTime\DateTimeFormat;
use More\Utils\DateUtils;

class IntercomFieldsService
{
    private const NULL_DATE = '0000-00-00 00:00:00';

    /**
     * @param mixed|null $dateString
     * @return int
     */
    public function getTimeStamp($dateString = null): int
    {
        if (empty($dateString) ||
            (string) $dateString === self::NULL_DATE ||
            ! DateUtils::isDate($dateString, DateTimeFormat::DATE_TIME_BD)
        ) {
            return 0;
        }

        $date = \DateTimeImmutable::createFromFormat(
            DateTimeFormat::DATE_TIME_BD,
            $dateString,
            new \DateTimeZone(IntercomConfig::INTERCOM_TIMEZONE)
        );

        if ($date === null) {
            return 0;
        }

        return $date->getTimestamp();
    }

    /**
     * @param array $data
     * @param array $options
     */
    public function setCustomOptions(array &$data, array $options): void
    {
        $this->setOptionsByKey(IntercomFieldsMapper::FIELD_CUSTOM_ATTRIBUTES, $data, $options);
    }

    public function createWithCustomOptions(array $options, array $customOptions): array
    {
        $options[IntercomFieldsMapper::FIELD_CUSTOM_ATTRIBUTES] = $customOptions;

        return $options;
    }

    /**
     * @param string $key
     * @param array $data
     * @param array $options
     */
    private function setOptionsByKey(string $key, array &$data, array $options): void
    {
        if (empty($options)) {
            return;
        }

        if (isset($data[$key])) {
            $data[$key] = array_merge($data[$key], $options);
        } else {
            $data[$key] = $options;
        }
    }
}
