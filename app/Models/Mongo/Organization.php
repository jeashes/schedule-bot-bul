<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class Organization extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'organizations';

    protected $fillable = [
        'display_name',
        'name',
        'description',
        'trello_id',
    ];

    public function getId(): string
    {
        return $this->getAttribute('_id');
    }


    public function getdisplayName(): ?string
    {
        return $this->getAttribute('display_name');
    }

    public function setdisplayName(string $value): void
    {
        $this->setAttribute('display_name', $value);
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function setName(string $value): void
    {
        $this->setAttribute('name', $value);
    }

    public function getDescription(): ?string
    {
        return $this->getAttribute('description');
    }

    public function setDescription(string $value): void
    {
        $this->setAttribute('description', $value);
    }

    public function getTrelloId(): ?string
    {
        return $this->getAttribute('trello_id');
    }

    public function setTrelloId(string $value): void
    {
        $this->setAttribute('trello_id', $value);
    }
}
