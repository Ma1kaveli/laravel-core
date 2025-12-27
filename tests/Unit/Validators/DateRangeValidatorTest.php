<?php

namespace Tests\Unit\Validators;

use Core\Validators\DateRangeValidator;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DateRangeValidatorTest extends TestCase
{
    /**
     * Создаёт пустой валидатор.
     *
     * В DateRangeValidator ошибки добавляются вручную,
     * поэтому стандартные правила Laravel не нужны.
     */
    protected function makeValidator(): \Illuminate\Validation\Validator
    {
        return Validator::make([], []);
    }

    /**
     * Проверяет корректный диапазон дат и времени.
     *
     * Условие: дата начала < дата окончания, обе даты в будущем.
     * Ожидается: ошибок нет.
     */
    public function test_valid_datetime_range_passes(): void
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $validator = $this->makeValidator();
        $start = Carbon::now()->addHour()->format('Y-m-d H:i:s');
        $end   = Carbon::now()->addHours(2)->format('Y-m-d H:i:s');

        DateRangeValidator::validate($validator, $start, $end);

        $this->assertEmpty($validator->errors()->all());
    }

    /**
     * Проверяет добавление ошибки при некорректном формате даты.
     *
     * Условие: дата начала в неверном формате.
     * Ожидается: валидатор содержит ошибку.
     */
    public function test_invalid_format_demo(): void
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $validator = $this->makeValidator();
        DateRangeValidator::validate($validator, 'invalid-date', '2025-01-01 13:00:00');

        $this->assertNotEmpty($validator->errors()->all());
    }

    /**
     * Проверяет ошибку, если дата начала >= даты окончания.
     *
     * Условие: дата начала позже даты окончания.
     * Ожидается: валидатор содержит ошибку.
     */
    public function test_start_gte_end_demo(): void
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $validator = $this->makeValidator();
        $start = Carbon::now()->addHours(2)->format('Y-m-d H:i:s');
        $end   = Carbon::now()->addHour()->format('Y-m-d H:i:s');

        DateRangeValidator::validate($validator, $start, $end);

        $this->assertNotEmpty($validator->errors()->all());
    }

    /**
     * Проверяет ошибку, если дата начала в прошлом.
     *
     * Условие: дата начала меньше текущего времени.
     * Ожидается: валидатор содержит ошибку.
     */
    public function test_start_in_past_demo(): void
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $validator = $this->makeValidator();
        $start = Carbon::now()->subHour()->format('Y-m-d H:i:s');
        $end   = Carbon::now()->addHour()->format('Y-m-d H:i:s');

        DateRangeValidator::validate($validator, $start, $end);

        $this->assertNotEmpty($validator->errors()->all());
    }

    /**
     * Проверяет ошибку, если дата окончания в прошлом.
     *
     * Условие: дата окончания меньше текущего времени.
     * Ожидается: валидатор содержит ошибку.
     */
    public function test_end_in_past_demo(): void
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $validator = $this->makeValidator();
        $start = Carbon::now()->addHour()->format('Y-m-d H:i:s');
        $end   = Carbon::now()->subHour()->format('Y-m-d H:i:s');

        DateRangeValidator::validate($validator, $start, $end);

        $this->assertNotEmpty($validator->errors()->all());
    }

    /**
     * Проверяет корректную работу режима "только дата".
     *
     * Условие: даты в формате Y-m-d, start < end.
     * Ожидается: ошибок нет.
     */
    public function test_only_date_demo(): void
    {
        Carbon::setTestNow('2025-01-01');

        $validator = $this->makeValidator();
        $start = Carbon::now()->addDay()->format('Y-m-d');
        $end   = Carbon::now()->addDays(2)->format('Y-m-d');

        DateRangeValidator::validateOnlyDate($validator, $start, $end);

        $this->assertEmpty($validator->errors()->all());
    }

    /**
     * Проверяет, что режим "только дата" ловит ошибки формата.
     *
     * Условие: дата начала в неверном формате.
     * Ожидается: валидатор содержит ошибку.
     */
    public function test_only_date_invalid_format_demo(): void
    {
        Carbon::setTestNow('2025-01-01');

        $validator = $this->makeValidator();
        DateRangeValidator::validateOnlyDate($validator, '2024-99-99', '2025-01-02');

        $this->assertNotEmpty($validator->errors()->all());
    }
}
