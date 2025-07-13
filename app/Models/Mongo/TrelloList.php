<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Represents a Trello list stored in the MongoDB 'trello_cards' collection.
 *
 * @property string $_id The unique identifier for the Trello list (cast to string).
 * @property string $trello_id The Trello list ID.
 * @property string $user_id The user ID associated with the list (cast to string).
 * @property string $board_id The Trello board ID the list belongs to (cast to string).
 * @property string $name The name of the Trello list.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TrelloList query()
 */
class TrelloList extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trello_cards';

    protected $fillable = [
        'trello_id',
        'user_id',
        'board_id',
        'name',
    ];

    protected $casts = [
        '_id' => 'string',
        'user_id' => 'string',
        'board_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
