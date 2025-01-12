<?php

namespace App\Dto\Trello;

class ListDto
{
    public readonly string $id;
    public readonly string $idBoard;
    public readonly string $name;
    public readonly bool $closed;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->idBoard = $data['idBoard'];
        $this->name = $data['name'];
        $this->closed = $data['closed'];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'idBoard' => $this->idBoard,
            'name' => $this->name,
            'closed' => $this->closed,
        ];
    }
}
