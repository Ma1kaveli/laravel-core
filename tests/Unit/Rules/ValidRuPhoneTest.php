<?php

namespace Tests\Unit\Rules;

use Core\Rules\ValidRuPhone;
use Tests\TestCase;

class ValidRuPhoneTest extends TestCase
{
    /**
     * Проверка, что корректный номер проходит валидацию
     */
    public function test_it_passes_for_valid_phone(): void
    {
        $rule = new ValidRuPhone();

        $validPhones = [
            '1234 567 890',
            '(1234) 567-890',
            '123 1234567',
            '(123) 123-45-67',
            '123-1234567',
            '123-123-45-67',
        ];

        foreach ($validPhones as $phone) {
            $failCalled = false;
            $rule->validate('phone', $phone, function () use (&$failCalled) {
                $failCalled = true;
            });

            $this->assertFalse($failCalled, "Fail callback не должен вызываться для номера $phone");
        }
    }

    /**
     * Неверные номера вызывают fail
     */
    public function test_it_fails_for_invalid_phone(): void
    {
        $rule = new ValidRuPhone();

        $invalidPhones = [
            '',                  // пустая строка
            'abc',               // текст
            '123',               // слишком коротко
            '123456789012345',   // слишком длинно
            '+7 123 456',        // неправильный формат
            '123-abc-4567',      // буквы в номере
            '12-34 56-78',
            '(12-34) 56 78',
            '1234 567 890 доб.123',
            '(123) 1234567 доб.12',
        ];

        foreach ($invalidPhones as $phone) {
            $failMessage = null;
            $rule->validate('phone', $phone, function ($message) use (&$failMessage) {
                $failMessage = $message;
            });

            $this->assertSame('Номер телефона указан в не правильном формате!', $failMessage, "Fail callback должен вызываться для номера $phone");
        }
    }

    /**
     * Проверка статического метода create()
     */
    public function test_it_can_be_created_via_static_method(): void
    {
        $rule = ValidRuPhone::create();

        $this->assertInstanceOf(ValidRuPhone::class, $rule);
    }

    /**
     * Проверка метода validPhone() напрямую
     */
    public function test_valid_phone_method(): void
    {
        $rule = new ValidRuPhone();

        $this->assertTrue($rule->validPhone('1234 567 890'));
        $this->assertTrue($rule->validPhone('(123) 123-45-67'));
        $this->assertFalse($rule->validPhone('abc'));
        $this->assertFalse($rule->validPhone('123'));
    }
}
