<?php

namespace Tests\Unit\Helpers;

use Core\Helpers\Phone;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    /** @test */
    public function it_removes_all_non_digit_symbols()
    {
        $result = Phone::stringNumberWithoutSymbols('+7 (999) 123-45-67');

        $this->assertSame('79991234567', $result);
    }

    /** @test */
    public function it_returns_empty_string_for_null()
    {
        $result = Phone::stringNumberWithoutSymbols(null);

        $this->assertSame('', $result);
    }

    /** @test */
    public function it_returns_empty_string_for_empty_string()
    {
        $result = Phone::stringNumberWithoutSymbols('');

        $this->assertSame('', $result);
    }

    /** @test */
    public function it_keeps_digits_unchanged()
    {
        $result = Phone::stringNumberWithoutSymbols('1234567890');

        $this->assertSame('1234567890', $result);
    }

    /** @test */
    public function it_removes_letters_and_symbols()
    {
        $result = Phone::stringNumberWithoutSymbols('abc123def!@#456');

        $this->assertSame('123456', $result);
    }

    /** @test */
    public function it_handles_international_phone_formats()
    {
        $result = Phone::stringNumberWithoutSymbols('+1-800-FLOWERS');

        $this->assertSame('1800', $result);
    }
}
