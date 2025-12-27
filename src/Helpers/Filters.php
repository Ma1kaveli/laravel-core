<?php

namespace Core\Helpers;

class Filters {
    /**
     * Функция для трансформации логического значения
     *  Если поле пустое, то так и вернет null.
     *  Иначе будет проверяться, что значение равно 'true'
     *
     * @param mixed $field
     *
     * @return bool|null
     */
    public static function transformBoolean(mixed $field): bool|null {
        if ($field === null) {
            return null;
        }

        if (is_bool($field)) {
            return $field;
        }

        if (is_string($field)) {
            return $field === 'true';
        }

        return false;
    }
}
