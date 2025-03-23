<?php

namespace App\Interfaces\Telegram;

use App\Dto\TelegramMessageDto;

interface StateHandlerInterface
{
   public function handle(TelegramMessageDto $messageDto, int $chatState): void;
}