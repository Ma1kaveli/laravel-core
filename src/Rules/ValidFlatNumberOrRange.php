<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidFlatNumberOrRange implements ValidationRule
{

    // Should return true or false depending on whether the attribute value is valid or not.
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Проверка для одиночного числа
        if (is_int($value)) {
            if ($value <= 0) {
                $fail("Число $value в элементе $attribute должно быть больше 0.", null);
            }
            return;
        }

        // Проверка для диапазона
        if (is_array($value)) {
            // Проверка количества элементов
            if (count($value) !== 2) {
                $fail("Диапазон в $attribute должен содержать ровно 2 числа.", null);
                return;
            }

            // Проверка типа элементов
            if (!is_int($value[0]) || !is_int($value[1])) {
                $fail("Оба элемента в $attribute должны быть целыми числами.", null);
                return;
            }

            // Проверка положительных значений
            if ($value[0] <= 0 || $value[1] <= 0) {
                $fail("Все числа в диапазоне $attribute должны быть больше 0.", null);
                return;
            }

            // Проверка порядка чисел
            if ($value[0] >= $value[1]) {
                $fail("Первое число в диапазоне $attribute должно быть меньше второго.", null);
            }

            return;
        }

        // Неподдерживаемый тип
        $fail("Элемент $attribute должен быть числом или массивом из двух чисел.", null);
    }

    public static function create(): self
    {
        return new self();
    }
}
