<?php

namespace Core\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidRuPhone implements ValidationRule
{
    public function validPhone(mixed $v) {
        $regex = '/^((\(\s?)?(\d{4}|\d{2}\-\d{2})(\s?\))?[\- ]?(\d{3}[\- ]?\d{3}|(\d{2}[\- ]){2}?\d{2}|\d{4}[\-]?\d{2})|((\(\s?)?\d{3}(\s?\))?[\- ]?(\d{7}|\d{3}[\- ](\d{2}[\- ]\d{2}|\d{4}))))(\s\(?(\доб.\s)?\d{1,5}\)?)?$/';

        return preg_match($regex, $v) ? true : false;
    }

    // Should return true or false depending on whether the attribute value is valid or not.
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $message = 'Номер телефона указан в не правильном формате!';

        if (!$this->validPhone($value)) {
            $fail($message, null);
        }
        try {
        } catch (\Exception $e) {
            $fail($message, null);
        }
    }

    public static function create(): self
    {
        return new self();
    }
}
