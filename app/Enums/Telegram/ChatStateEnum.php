<?php

namespace App\Enums\Telegram;

enum ChatStateEnum: int
{
    case START = 0;
    case SUBJECT_STUDY = 1;
    case HOURS = 2;
    case SCHEDULE = 3;
    case EMAIL = 4;
    case FINISHED = 5;
    case USER_HAS_WORKSPACE = 6;
}
