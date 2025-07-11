<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Class TrelloBoard
 *
 * Represents a Trello board stored in a MongoDB collection.
 *
 * @property string $_id             The unique identifier for the board (MongoDB ObjectId as string).
 * @property string $trello_id       The Trello board ID.
 * @property string $user_id         The ID of the user who owns the board.
 * @property string $name            The name of the Trello board.
 * @property string $desc            The description of the Trello board.
 * @property bool   $closed          Indicates if the board is closed.
 * @property string $url             The URL of the Trello board.
 * @property string $permission_level The permission level of the board.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TrelloBoard query()
 */
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
            'user_id' => 'string',
            'closed' => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime'
        ];
    }
}
