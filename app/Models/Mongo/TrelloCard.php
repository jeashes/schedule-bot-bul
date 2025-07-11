<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Class TrelloCard
 *
 * Represents a Trello card stored in a MongoDB collection.
 *
 * @property string $_id         The unique identifier for the Trello card (MongoDB ObjectId as string).
 * @property string $user_id     The ID of the user associated with the card.
 * @property string $trello_id   The Trello card ID.
 * @property string $board_id    The Trello board ID.
 * @property string $name        The name/title of the Trello card.
 * @property string $desc        The description of the Trello card.
 * @property string $url         The URL to the Trello card.
 * @property \Illuminate\Support\Carbon|null $due         The due date of the card.
 * @property bool $dueComplete   Whether the card's due date is complete.
 * @property string|null $email  The email associated with the card.
 * @property \Illuminate\Support\Carbon|null $created_at  The creation timestamp.
 * @property \Illuminate\Support\Carbon|null $updated_at  The last update timestamp.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TrelloCard query()
 */
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

    protected function casts(): array
    {
        return [
            '_id' => 'string',
            'board_id' => 'string',
            'due' => 'datetime',
            'dueComplete' => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime'
        ];
    }
}
