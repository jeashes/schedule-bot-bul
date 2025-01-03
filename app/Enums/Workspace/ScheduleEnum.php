<?php

namespace App\Enums\Workspace;

enum ScheduleEnum: int
{
    case MON_WED_FRI = 0;
    case TUE_THU_SAT = 1;
    case SAT_SUN = 2;
    case ALL_WEEKDAYS = 3;
    case EVERY_DAY = 4;
}
