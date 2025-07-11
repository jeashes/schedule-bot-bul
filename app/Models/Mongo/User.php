<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Class User
 *
 * Represents a user document in the MongoDB 'users' collection.
 *
 * @property string $_id           The unique identifier for the user (MongoDB ObjectId).
 * @property int $telegram_id      The Telegram user ID.
 * @property string|null $trello_id The Trello user ID.
 * @property string|null $username The Telegram username.
 * @property string|null $workspace_id The associated workspace ID.
 * @property string|null $first_name The user's first name.
 * @property string|null $last_name  The user's last name.
 * @property int|null $chat_id     The Telegram chat ID.
 * @property string|null $language_code The user's language code.
 * @property string|null $email    The user's email address.
 * @property \Carbon\Carbon|null $created_at The creation timestamp.
 * @property \Carbon\Carbon|null $updated_at The update timestamp.
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * 
 */
class User extends Model
{
    public $timestamps = true;

    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'telegram_id',
        'trello_id',
        'username',
        'workspace_id',
        'first_name',
        'last_name',
        'chat_id',
        'language_code',
        'email',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        '_id' => 'string',
        'telegram_id' => 'integer',
        'chat_id' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime'
    ];
}
