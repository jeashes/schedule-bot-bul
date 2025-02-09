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
        $this->setCurrentDateByDefault();

        $week1 = [
            $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String()
        ];

        $this->currentDay->addWeek();

        $week2 = [
            $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String()
        ];

        return array_merge($week1, $week2);
    }

    public function getTueThuSatDates(): array
    {
        $this->setCurrentDateByDefault();

        $week1 = [
            $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String()
        ];

        $this->currentDay->addWeek();

        $week2 = [
            $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String()
        ];

        return array_merge($week1, $week2);
    }

    public function getSatSunDates(): array
    {
        $this->setCurrentDateByDefault();

        $week1 = [
            $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->addDay()->toIso8601String()
        ];

        $week2 = [
            $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->addDay()->toIso8601String()
        ];

        return array_merge($week1, $week2);
    }

    public function getAllWeekDaysDates(): array
    {
        $this->setCurrentDateByDefault();

        $week1 = [
            $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String()
        ];

        $this->currentDay->addWeek();

        $week2 = [
            $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String()
        ];

        return array_merge($week1, $week2);
    }

    public function getEveryDayDates(): array
    {
        $this->setCurrentDateByDefault();

        $week1 = [
            $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->addDay()->toIso8601String()
        ];

        $week2 = [
            $this->currentDay->weekday(Carbon::MONDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::TUESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::WEDNESDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::THURSDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::FRIDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->toIso8601String(),
            $this->currentDay->weekday(Carbon::SATURDAY)->addDay()->toIso8601String()
        ];

        return array_merge($week1, $week2);
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

    private function setCurrentDateByDefault(): void
    {
        $this->currentDay = Carbon::now()->addWeek()->startOfWeek(Carbon::MONDAY);
    }
}
