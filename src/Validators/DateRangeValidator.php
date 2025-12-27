<?php

namespace Core\Validators;

use Illuminate\Validation\Validator;
use Carbon\Carbon;

class DateRangeValidator
{
    /**
     * Валидация даты и времени (Y-m-d H:i:s)
     *
     * @param Validator $validator
     * @param ?string $dateStart
     * @param ?string $dateEnd
     *
     * @return void
     */
    public static function validate(Validator $validator, ?string $dateStart, ?string $dateEnd): void
    {
        self::validateInternal($validator, $dateStart, $dateEnd, 'Y-m-d H:i:s');
    }

    /**
     * Валидация только дат (Y-m-d)
     *
     * @param Validator $validator
     * @param ?string $dateStart
     * @param ?string $dateEnd
     *
     * @return void
     */
    public static function validateOnlyDate(Validator $validator, ?string $dateStart, ?string $dateEnd): void
    {
        self::validateInternal($validator, $dateStart, $dateEnd, 'Y-m-d');
    }

    /**
     * Общий внутренний метод валидации
     *
     * @param Validator $validator
     * @param ?string $dateStart
     * @param ?string $dateEnd
     * @param string $format
     *
     * @return void
     */
    private static function validateInternal(Validator $validator, ?string $dateStart, ?string $dateEnd, string $format): void
    {
        try {
            $start = $dateStart ? Carbon::createFromFormat($format, $dateStart) : null;
            $end   = $dateEnd   ? Carbon::createFromFormat($format, $dateEnd)   : null;
        } catch (\Exception $e) {
            $validator->errors()->add('dateStart', 'Неверный формат даты.');
            return;
        }

        // Проверка: дата начала < дата окончания
        if ($start && $end && $start->gte($end)) {
            $validator->errors()->add('dateStart', 'Дата начала должна быть меньше даты окончания.');
        }

        // Проверка: дата начала > текущее время
        if ($start && $start->lte(Carbon::now())) {
            $validator->errors()->add('dateStart', 'Дата начала должна быть больше текущего времени.');
        }

        // Проверка: дата око > текущее время
        if ($end && $end->lte(Carbon::now())) {
            $validator->errors()->add('dateStart', 'Дата окончания должна быть больше текущего времени.');
        }
    }
}
