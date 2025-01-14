<?php

namespace App\Dto\Trello;

class CardDto
{
    public readonly string $id;
    public readonly string $idBoard;
    public readonly string $idList;
    public readonly array $idMembers;
    public readonly array $idLabels;
    public readonly array $attachments;
    public readonly string $name;
    public readonly int $pos;
    public readonly string $url;
    public readonly bool|string $desc;
    public readonly bool $closed;
    public readonly null|string $due;
    public readonly bool|string $dueComplete;
    public readonly null|string $email;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->idBoard = $data['idBoard'];
        $this->idList = $data['idList'];
        $this->idMembers = $data['idMembers'];
        $this->idLabels = $data['idLabels'];
        $this->attachments = $data['attachments'];
        $this->name = $data['name'];
        $this->pos = $data['pos'];
        $this->url = $data['url'];
        $this->desc = $data['desc'];
        $this->closed = $data['closed'];
        $this->due = $data['due'];
        $this->dueComplete = $data['dueComplete'];
        $this->email = $data['email'];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'idBoard' => $this->idBoard,
            'idList' => $this->idList,
            'idMembers' => $this->idMembers,
            'idLabels' => $this->idLabels,
            'attachments' => $this->attachments,
            'name' => $this->name,
            'pos' => $this->pos,
            'url' => $this->url,
            'desc' => $this->desc,
            'closed' => $this->closed,
            'due' => $this->due,
            'dueComplete' => $this->dueComplete,
            'email' => $this->email,
        ];
    }
}
