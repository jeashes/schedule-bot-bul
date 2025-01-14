<?php

namespace App\Helpers;

use App\Enums\Workspace\ScheduleEnum;
use Illuminate\Support\Carbon;

class WeekDayDates
{
    private Carbon $currentDay;

    public function __construct(Carbon $date)
    {
        $this->currentDay = $date;
    }

    public function getMonWenFriDates(): array
    {
        return [
            'monday' => $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            'wednesday' => $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            'friday' => $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String()
        ];
    }

    public function getTueThuSatDates(): array
    {
        return [
            'tuesday' => $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            'thursday' => $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            'saturday' => $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String()
        ];
    }

    public function getSatSunDates(): array
    {
        return [
            'saturday' => $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String(),
            'sunday' => $this->currentDay->weekday(Carbon::SATURDAY)->addDay()->toIso8601String()
        ];
    }

    public function getAllWeekDaysDates(): array
    {
        return [
            'monday' => $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            'tuesday' => $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            'wednesday' => $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            'thursday' => $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            'friday' => $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String()
        ];
    }

    public function getEveryDayDates(): array
    {
        return [
            'monday' => $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            'tuesday' => $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            'wednesday' => $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            'thursday' => $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            'friday' => $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String(),
            'saturday' => $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String(),
            'sunday' => $this->currentDay->weekday(Carbon::SATURDAY)->addDay()->toIso8601String()
        ];
    }

    public function getDatesBySchedule(int $scheduleType): array
    {
        switch ($scheduleType) {
            case ScheduleEnum::MON_WED_FRI->value:
                return $this->getMonWenFriDates();
            case ScheduleEnum::TUE_THU_SAT->value:
                return $this->getTueThuSatDates();
            case ScheduleEnum::SAT_SUN->value:
                return $this->getSatSunDates();
            case ScheduleEnum::ALL_WEEKDAYS->value:
                return $this->getAllWeekDaysDates();
            case ScheduleEnum::EVERY_DAY->value:
                return $this->getEveryDayDates();
            default:
                return [];
        }
    }

    public function setNewCurrentDate(Carbon $date): void
    {
        $this->currentDay = $date;
    }
}
