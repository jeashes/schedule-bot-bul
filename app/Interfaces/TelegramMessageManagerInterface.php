<?php

namespace App\Interfaces;

use App\Dto\TelegramMessageDto;

interface TelegramMessageManagerInterface
{
    public function sendQuestion(TelegramMessageDto $messageDto): void;

    public function acceptAnswer(TelegramMessageDto $messageDto): void;
}
