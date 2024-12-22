<?php

namespace App\Dto\Telegram;

class PaceLevelStateDto
{
    public function __construct(
        public readonly string $currentAnswer,
        public readonly int $approved
    ) { }
}
