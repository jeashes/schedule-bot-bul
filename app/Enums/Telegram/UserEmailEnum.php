<?php

namespace App\Enums\Telegram;

enum UserEmailEnum: string
{
    case QUESTION = 'user_email_question';
    case KEY_NAME = 'user_email_studies';
    case NAME_DECLINE = 'user_email_name_no';
    case NAME_ACCEPT = 'user_email_name_yes';
}
