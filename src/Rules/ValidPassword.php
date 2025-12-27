<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidPassword implements ValidationRule
{
    public function validPassword(mixed $v) {
        return strlen($v) >= 8 &&
            preg_match('/[a-z]/', $v) &&
            preg_match('/[A-Z]/', $v) &&
            preg_match('/\d/', $v);
    }

    // Should return true or false depending on whether the attribute value is valid or not.
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $message = 'Пароль должен содержать минимум 8 символов, включая заглавные и строчные буквы, а также цифры.';

        if (!$this->validPassword($value)) {
            $fail($message, null);
        }
    }

    public static function create(): self
    {
        return new self();
    }
}
