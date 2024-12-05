<?php

namespace App\Enums\Telegram;

enum SubjectStudiesEnum: string
{
    case QUESTION = 'subject_of_studies_question';
    case KEY_NAME = 'subject_of_studies';
    case NAME_DECLINE = 'subject_of_studies_name_no';
    case NAME_ACCEPT = 'subject_of_studies_name_yes';
}
