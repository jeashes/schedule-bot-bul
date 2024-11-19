<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class User extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'chat_id',
        'language_code',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
}
