<?php

namespace App\Helpers;

class AccountHelper
{
    /**
     * Mask account number to show only last 4 digits
     * Example: 1234567890 becomes ++++7890
     *
     * @param string|null $accountNumber
     * @return string
     */
    public static function maskAccountNumber($accountNumber)
    {
        if (empty($accountNumber)) {
            return '';
        }

        $accountNumber = (string) $accountNumber;
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return $accountNumber;
        }

        $lastFour = substr($accountNumber, -4);
        return '++++' . $lastFour;
    }

    /**
     * Mask account number with custom mask character
     *
     * @param string|null $accountNumber
     * @param string $maskChar
     * @param int $visibleDigits
     * @return string
     */
    public static function maskAccountNumberCustom($accountNumber, $maskChar = '+', $visibleDigits = 4)
    {
        if (empty($accountNumber)) {
            return '';
        }

        $accountNumber = (string) $accountNumber;
        $length = strlen($accountNumber);

        if ($length <= $visibleDigits) {
            return $accountNumber;
        }

        $lastDigits = substr($accountNumber, -$visibleDigits);
        $maskLength = 4; // Always show 4 mask characters
        return str_repeat($maskChar, $maskLength) . $lastDigits;
    }
}
