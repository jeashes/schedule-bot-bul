<?php

namespace App\Enums\Workspace;

enum ScheduleEnum: int
{
    case MON_WED_FRI = 1;
    case TUE_THU_SAT = 2;
    case SAT_SUN = 3;
    case ALL_WEEKDAYS = 4;
    case EVERY_DAY = 5;
}
