<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class TrelloList extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trello_cards';

    protected $fillable = [
        'trello_id',
        'user_id',
        'workspace_id',
        'board_id',
        'card_ids',
        'name',
    ];

}
