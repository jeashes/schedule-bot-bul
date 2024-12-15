<?php

namespace App\Enums\Telegram;

enum HoursOnStudyEnum: string
{
    case QUESTION = 'hours_on_study_question';
    case KEY_NAME = 'hours_on_study';
    case NAME_DECLINE = 'hours_on_study_count_no';
    case NAME_ACCEPT = 'hours_on_study_count_yes';
}
