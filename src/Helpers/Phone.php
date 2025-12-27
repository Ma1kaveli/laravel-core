<?php

namespace Core\Helpers;

class Phone {
    /**
     * Удаление всего кроме цифр
     *
     * @param string|null $number
     *
     * @return string
     */
    public static function stringNumberWithoutSymbols(string|null $number): string
    {
        return $number !== null
            ? preg_replace('/[^0-9]/', '', $number)
            : '';
    }
}
