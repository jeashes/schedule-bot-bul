<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class TrelloBoard extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trello_boards';

    protected $fillable = [
        'user_id',
        'trello_id',
        'workspace_id',
        'list_ids',
        'name',
        'desc',
    ];
}
