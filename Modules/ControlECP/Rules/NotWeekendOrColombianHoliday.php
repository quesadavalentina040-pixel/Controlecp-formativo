<?php

namespace Modules\ControlECP\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\ControlECP\Services\ColombianWorkingDayService;

class NotWeekendOrColombianHoliday implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $fail('La fecha debe tener el formato válido AAAA-MM-DD.');

            return;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));
        if (! checkdate($month, $day, $year)) {
            $fail('La fecha debe tener el formato válido AAAA-MM-DD.');

            return;
        }

        $calendar = app(ColombianWorkingDayService::class);
        if (! $calendar->isWorkingDay($value)) {
            $fail('La fecha debe ser un día hábil: lunes a viernes y no festivo nacional de Colombia.');
        }
    }
}
