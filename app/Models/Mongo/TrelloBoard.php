<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class TrelloBoard extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trello_boards';

    protected $fillable = [
        'trello_id',
        'user_id',
        'name',
        'desc',
        'closed',
        'url',
        'permission_level',
    ];

    protected function casts(): array
    {
        return [
            '_id' => 'string',
        ];
    }
}
