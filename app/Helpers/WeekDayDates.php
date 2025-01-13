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
            'monday' => $this->currentDay->weekday(Carbon::MONDAY)->toDate(),
            'wednesday' => $this->currentDay->weekday(Carbon::WEDNESDAY)->toDate(),
            'friday' => $this->currentDay->weekday(Carbon::FRIDAY)->toDate()
        ];
    }

    public function getTueThuSatDates(): array
    {
        return [
            'tuesday' => $this->currentDay->weekday(Carbon::TUESDAY)->toDate(),
            'thursday' => $this->currentDay->weekday(Carbon::THURSDAY)->toDate(),
            'saturday' => $this->currentDay->weekday(Carbon::SATURDAY)->toDate()
        ];
    }

    public function getSatSunDates(): array
    {
        return [
            'saturday' => $this->currentDay->weekday(Carbon::SATURDAY)->toDate(),
            'sunday' => $this->currentDay->weekday(Carbon::SUNDAY)->toDate()
        ];
    }

    public function getAllWeekDaysDates(): array
    {
        return [
            'monday' => $this->currentDay->weekday(Carbon::MONDAY)->toDate(),
            'tuesday' => $this->currentDay->weekday(Carbon::TUESDAY)->toDate(),
            'wednesday' => $this->currentDay->weekday(Carbon::WEDNESDAY)->toDate(),
            'thursday' => $this->currentDay->weekday(Carbon::THURSDAY)->toDate(),
            'friday' => $this->currentDay->weekday(Carbon::FRIDAY)->toDate()
        ];
    }

    public function getEveryDayDates(): array
    {
        return [
            'monday' => $this->currentDay->weekday(Carbon::MONDAY)->toDate(),
            'tuesday' => $this->currentDay->weekday(Carbon::TUESDAY)->toDate(),
            'wednesday' => $this->currentDay->weekday(Carbon::WEDNESDAY)->toDate(),
            'thursday' => $this->currentDay->weekday(Carbon::THURSDAY)->toDate(),
            'friday' => $this->currentDay->weekday(Carbon::FRIDAY)->toDate(),
            'saturday' => $this->currentDay->weekday(Carbon::SATURDAY)->toDate(),
            'sunday' => $this->currentDay->weekday(Carbon::SUNDAY)->toDate()
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
