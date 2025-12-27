<?php

namespace Tests\Unit\Rules;

use Core\Rules\ValidPassword;

use Tests\TestCase;

class ValidPasswordTest extends TestCase
{
    /**
     * Проверка, что корректный пароль проходит валидацию
     */
    public function test_it_passes_for_valid_password(): void
    {
        $rule = new ValidPassword();

        $failCalled = false;
        $rule->validate('password', 'Abcd1234', function ($message) use (&$failCalled) {
            $failCalled = true;
        });

        $this->assertFalse($failCalled, 'Fail callback не должен быть вызван для валидного пароля');
    }

    /**
     * Пароль меньше 8 символов вызывает fail
     */
    public function test_it_fails_for_too_short_password(): void
    {
        $rule = new ValidPassword();

        $failMessage = null;
        $rule->validate('password', 'Abc12', function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame(
            'Пароль должен содержать минимум 8 символов, включая заглавные и строчные буквы, а также цифры.',
            $failMessage
        );
    }

    /**
     * Пароль без заглавной буквы вызывает fail
     */
    public function test_it_fails_for_password_without_uppercase(): void
    {
        $rule = new ValidPassword();

        $failMessage = null;
        $rule->validate('password', 'abcd1234', function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame(
            'Пароль должен содержать минимум 8 символов, включая заглавные и строчные буквы, а также цифры.',
            $failMessage
        );
    }

    /**
     * Пароль без строчной буквы вызывает fail
     */
    public function test_it_fails_for_password_without_lowercase(): void
    {
        $rule = new ValidPassword();

        $failMessage = null;
        $rule->validate('password', 'ABCD1234', function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame(
            'Пароль должен содержать минимум 8 символов, включая заглавные и строчные буквы, а также цифры.',
            $failMessage
        );
    }

    /**
     * Пароль без цифры вызывает fail
     */
    public function test_it_fails_for_password_without_digit(): void
    {
        $rule = new ValidPassword();

        $failMessage = null;
        $rule->validate('password', 'Abcdefgh', function ($message) use (&$failMessage) {
            $failMessage = $message;
        });

        $this->assertSame(
            'Пароль должен содержать минимум 8 символов, включая заглавные и строчные буквы, а также цифры.',
            $failMessage
        );
    }

    /**
     * Проверка статического метода create()
     */
    public function test_it_can_be_created_via_static_method(): void
    {
        $rule = ValidPassword::create();

        $this->assertInstanceOf(ValidPassword::class, $rule);
    }

    /**
     * Тестируем метод validPassword() напрямую
     */
    public function test_valid_password_method(): void
    {
        $rule = new ValidPassword();

        $this->assertTrue($rule->validPassword('Abcd1234'));
        $this->assertFalse($rule->validPassword('abcd1234'));
        $this->assertFalse($rule->validPassword('ABCD1234'));
        $this->assertFalse($rule->validPassword('Abcdefgh'));
        $this->assertFalse($rule->validPassword('Ab1'));
    }
}
