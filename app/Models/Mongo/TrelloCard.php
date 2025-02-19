<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class TrelloCard extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trello_cards';

    protected $fillable = [
        'user_id',
        'trello_id',
        'board_id',
        'name',
        'desc',
        'url',
        'due',
        'dueComplete',
        'email',
    ];

    public function getId(): string
    {
        return $this->getAttribute('_id');
    }

    protected function casts(): array
    {
        return [
            '_id' => 'string',
        ];
    }
}
