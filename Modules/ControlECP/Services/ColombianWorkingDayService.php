<?php

namespace Modules\ControlECP\Services;

use Carbon\CarbonImmutable;

class ColombianWorkingDayService
{
    private static array $holidayCache = [];

    public function isWorkingDay(string $date): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$year, $month, $dayOfMonth] = array_map('intval', explode('-', $date));
        if (! checkdate($month, $dayOfMonth, $year)) {
            return false;
        }

        $day = CarbonImmutable::createFromFormat('!Y-m-d', $date);
        if (! $day || $day->format('Y-m-d') !== $date || $day->isSaturday() || $day->isSunday()) {
            return false;
        }

        return ! in_array($date, $this->holidaysForYears((int) $day->year, (int) $day->year), true);
    }

    public function holidaysForYears(int $fromYear, int $toYear): array
    {
        $fromYear = max(1, $fromYear);
        $toYear = min(9999, $toYear);
        $holidays = [];

        for ($year = $fromYear; $year <= $toYear; $year++) {
            if (! isset(self::$holidayCache[$year])) {
                self::$holidayCache[$year] = $this->holidaysForYear($year);
            }

            array_push($holidays, ...self::$holidayCache[$year]);
        }

        return $holidays;
    }

    private function holidaysForYear(int $year): array
    {
        $dates = [
            "$year-01-01",
            "$year-05-01",
            "$year-07-20",
            "$year-08-07",
            "$year-12-08",
            "$year-12-25",
        ];

        foreach ([
            "$year-01-06",
            "$year-03-19",
            "$year-06-29",
            "$year-08-15",
            "$year-10-12",
            "$year-11-01",
            "$year-11-11",
        ] as $date) {
            $dates[] = $this->moveToMonday($date);
        }

        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $easterMonth = intdiv($h + $l - 7 * $m + 114, 31);
        $easterDay = (($h + $l - 7 * $m + 114) % 31) + 1;
        $easterSunday = CarbonImmutable::create($year, $easterMonth, $easterDay);
        $dates[] = $easterSunday->subDays(3)->format('Y-m-d');
        $dates[] = $easterSunday->subDays(2)->format('Y-m-d');

        foreach ([39, 60, 68] as $daysAfterEaster) {
            $dates[] = $this->moveToMonday($easterSunday->addDays($daysAfterEaster)->format('Y-m-d'));
        }

        sort($dates);

        return array_values(array_unique($dates));
    }

    private function moveToMonday(string $date): string
    {
        $day = CarbonImmutable::createFromFormat('!Y-m-d', $date);

        return $day->isMonday() ? $date : $day->next(CarbonImmutable::MONDAY)->format('Y-m-d');
    }
}
