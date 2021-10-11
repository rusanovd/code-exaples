<?php

declare(strict_types=1);

namespace More\Amo\Services;

class AmoPhoneFormatter
{
    public const PATERN_NOT_INT = '/[\D]+/';
    public const ALL_CODES = [371, 380, 370, 971, 372, 375, 38, 8, 7, 1, 0];
    public const PHONE_MIN_LENGTH = 5;
    public const CODE_SEVEN = '7';
    public const PHONE_START_FROM_NINE = '9';

    /**
     * @param string $str
     * @return string
     */
    public function clearNotNumeric(string $str): string
    {
        return (string) preg_replace(self::PATERN_NOT_INT, '', $str);
    }

    /**
     * @param string $phone
     * @param string $code
     * @return string
     */
    public function clearPhoneByCode(string $phone, string $code): string
    {
        if (strpos($phone, $code) === 0) {
            $phone = preg_replace('/^' . $code . '/', '', $phone, 1);
        }

        return $phone;
    }

    /**
     * @param string $phone
     * @return bool
     */
    public function checkPhoneLength(string $phone): bool
    {
        return strlen($phone) >= self::PHONE_MIN_LENGTH;
    }

    /**
     * @param string $phone
     * @param string $code
     * @return string
     */
    public function getPhoneWithoutCode(string $phone, string $code): string
    {
        if (! $phone = $this->clearNotNumeric($phone)) {
            return $phone;
        }

        $codes = [];
        if (empty($code)) {
            $possibleCodes = self::ALL_CODES;
            rsort($possibleCodes); // по кол-ву цифр для правильной работы регулярок
            $codes = $possibleCodes;
        } elseif ($code = $this->clearNotNumeric($code)) {
            $codes[] = $code;
        }

        foreach ($codes as $code) {
            $phoneWithoutCode = $this->clearPhoneByCode($phone, (string) $code);
            if ($phoneWithoutCode !== $phone) {
                $phone = $phoneWithoutCode;
                break;
            }
        }

        return $phone;
    }
}
