<?php

namespace App\Enums\Trello;

enum DefaultListNameEnum: string
{
    case TODO = 'To Do';
    case DOING = 'Doing';
    case DONE = 'Done';
}
