<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class BotPhrases extends Model
{
    public const DEFAULT_LANGUAGE_CODE = 'en';

    protected $connection = 'mongodb';

    protected $collection = 'bot_phrases';

    protected $fillable = [
        'language_code',
        'phrase_key',
        'phrase_text',
    ];

    protected function casts(): array
    {
        return [
            '_id' => 'string',
        ];
    }
}
