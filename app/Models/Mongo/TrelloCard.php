<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class TrelloCard extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trello_cards';

    protected $fillable = [
        'trello_id',
        'user_id',
        'workspace_id',
        'board_id',
        'list_id',
        'closed',
        'description',
        'due',
        'dueComplete'
    ];

    public function getId(): string
    {
        return $this->getAttribute('_id');
    }
}
