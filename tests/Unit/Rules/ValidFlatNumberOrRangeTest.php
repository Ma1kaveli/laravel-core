<?php

namespace Tests\Unit\Rules;

use Core\Rules\ValidFlatNumberOrRange;

use Tests\TestCase;

class ValidFlatNumberOrRangeTest extends TestCase
{
    /**
     * Проверка, что валидное одиночное число проходит проверку
     */
    public function test_it_passes_for_single_positive_number(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failCalled = false;
        $rule->validate('flat', 5, function ($message) use (&$failCalled) {
            $failCalled = true;
        });

        $this->assertFalse($failCalled, 'Fail callback не должен быть вызван для положительного числа');
    }

    /**
     * Проверка, что отрицательное число вызывает fail
     */
    public function test_it_fails_for_single_negative_number(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failMessage = null;
        $rule->validate('flat', -3, function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame('Число -3 в элементе flat должно быть больше 0.', $failMessage);
    }

    /**
     * Проверка, что диапазон из двух положительных чисел в правильном порядке проходит
     */
    public function test_it_passes_for_valid_range(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failCalled = false;
        $rule->validate('flat', [1, 5], function ($message) use (&$failCalled) {
            $failCalled = true;
        });

        $this->assertFalse($failCalled, 'Fail callback не должен быть вызван для корректного диапазона');
    }

    /**
     * Диапазон с неправильным количеством элементов должен вызвать fail
     */
    public function test_it_fails_for_range_with_wrong_number_of_elements(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failMessage = null;
        $rule->validate('flat', [1, 2, 3], function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame('Диапазон в flat должен содержать ровно 2 числа.', $failMessage);
    }

    /**
     * Диапазон с нецелыми числами вызывает fail
     */
    public function test_it_fails_for_range_with_non_integer_values(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failMessage = null;
        $rule->validate('flat', [1, 'a'], function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame('Оба элемента в flat должны быть целыми числами.', $failMessage);
    }

    /**
     * Диапазон с отрицательными числами вызывает fail
     */
    public function test_it_fails_for_range_with_negative_numbers(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failMessage = null;
        $rule->validate('flat', [0, 2], function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame('Все числа в диапазоне flat должны быть больше 0.', $failMessage);
    }

    /**
     * Диапазон с первым числом больше или равным второму вызывает fail
     */
    public function test_it_fails_when_first_number_greater_or_equal_second(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failMessage = null;
        $rule->validate('flat', [5, 2], function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame('Первое число в диапазоне flat должно быть меньше второго.', $failMessage);
    }

    /**
     * Неподдерживаемый тип (например, строка) вызывает fail
     */
    public function test_it_fails_for_unsupported_type(): void
    {
        $rule = new ValidFlatNumberOrRange();

        $failMessage = null;
        $rule->validate('flat', 'abc', function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame('Элемент flat должен быть числом или массивом из двух чисел.', $failMessage);
    }

    /**
     * Проверка статического метода create()
     */
    public function test_it_can_be_created_via_static_method(): void
    {
        $rule = ValidFlatNumberOrRange::create();

        $this->assertInstanceOf(ValidFlatNumberOrRange::class, $rule);
    }
}
