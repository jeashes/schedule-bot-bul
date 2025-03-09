<?php

namespace App\Enums\Telegram;

enum ChatStateEnum: int
{
    case START = 0;
    case SUBJECT_STUDY = 1;
    case GOAL = 2;
    case KNOWLEDGE_LEVEL = 3;
    case COURSE_TYPE = 4;
    case TOOLS = 5;
    case HOURS = 6;
    case SCHEDULE = 7;
    case EMAIL = 8;
    case FINISHED = 9;
    case USER_HAS_WORKSPACE = 10;
}
