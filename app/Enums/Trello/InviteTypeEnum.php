<?php

namespace App\Enums\Trello;

enum InviteTypeEnum: string
{
    case ADMIN = 'admin';
    case NORMAL = 'normal';
    case OBSERVER = 'observer';
}
