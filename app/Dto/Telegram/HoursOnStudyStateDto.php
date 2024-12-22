<?php

namespace App\Dto\Telegram;

class HoursOnStudyStateDto
{
    public function __construct(
        public readonly string $currentAnswer,
        public readonly int $approved,
        public readonly int $edited
    ) { }
}
