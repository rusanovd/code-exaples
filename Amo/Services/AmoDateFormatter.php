<?php

declare(strict_types=1);

namespace More\Amo\Services;

use Infrastructure\DateTime\DateTimeFormat;

final class AmoDateFormatter
{
    public const AMO_API_DATE_FORMAT = 'Y-m-d H:i:s';
    private const AMO_TIMEZONE = 'Europe/Moscow';

    // -1 sec - требование AmoCRM, чтобы срок задачи считался до конца дня. Если указать 00:00:00, задача будет отнесена на следующий день до 00:00
    private const AMO_TASK_COMPLETE_TILL_END_OF_TODAY = 'tomorrow -1 sec';
    private const AMO_TASK_COMPLETE_TILL_END_OF_NEXT_WEEKDAY = 'next weekday +1 day -1 sec';

    private const DAY_NUMBER_SATURDAY = 6; // Суббота - 6й день недели

    private const HOUR_END_OF_DAY = 19; // Конец рабочего дня 19:00

    /**
     * Вычислить срок выполнения задачи до конца рабочего дня (с учетом выходных и окончания рабочего дня)
     *
     * Если текущая дата - будний день до 19:00 (во временной зоне AmoCRM), то установить срок до конца этого дня
     * Иначе - найти ближайший будний день и установить срок до его конца
     *
     * @param \DateTimeImmutable $now Текущая дата-время (любая временная зона)
     * @return \DateTimeImmutable Итоговая дата в той же временной зоне, что и переданная
     */
    public function calculateTaskCompleteTillEndOfThisWeekdayDate(\DateTimeImmutable $now): \DateTimeImmutable
    {
        $amoNow = $now->setTimezone(new \DateTimeZone(self::AMO_TIMEZONE));

        $isWorkingHours = $amoNow->format('N') < self::DAY_NUMBER_SATURDAY
            && $amoNow->format('G') < self::HOUR_END_OF_DAY;

        $amoEndOfThisWeekday = $isWorkingHours
            ? $amoNow->modify(self::AMO_TASK_COMPLETE_TILL_END_OF_TODAY)
            : $amoNow->modify(self::AMO_TASK_COMPLETE_TILL_END_OF_NEXT_WEEKDAY);

        return $amoEndOfThisWeekday->setTimezone($now->getTimezone());
    }

    /**
     * Форматировать дату для записи атрибута в AmoCRM во временной зоне AmoCRM
     *
     * @param \DateTimeImmutable|null $date
     * @param string $format Формат даты, по умолчанию - формат, принимаемый AmoCRM
     * @return string
     */
    public function formatDateAndTimezone(?\DateTimeImmutable $date, string $format = self::AMO_API_DATE_FORMAT): string
    {
        return $date
            ? $this->formatDate($date->setTimezone(new \DateTimeZone(self::AMO_TIMEZONE)), $format)
            : '';
    }

    /**
     * Форматировать дату для записи атрибута в AmoCRM
     *
     * @param \DateTimeImmutable|null $date
     * @param string $format Формат даты, по умолчанию - формат, принимаемый AmoCRM
     * @return string
     */
    public function formatDate(?\DateTimeImmutable $date, string $format = self::AMO_API_DATE_FORMAT): string
    {
        if ($date === null) {
            return '';
        }

        $dateString = $date->format($format);

        if ($dateString === \CSalon::NULL_DATE) {
            return '';
        }

        return $dateString;
    }

    public function formatDateString(string $dateString, string $format = self::AMO_API_DATE_FORMAT): string
    {
        if ($dateString === \CSalon::NULL_DATE) {
            return '';
        }

        $dateTime = \DateTimeImmutable::createFromFormat(DateTimeFormat::DATE_TIME_BD, $dateString);

        if (! $dateTime) {
            return '';
        }

        return $this->formatDate($dateTime, $format);
    }
}
