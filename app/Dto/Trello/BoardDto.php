<?php

namespace App\Dto\Trello;

class BoardDto
{
    public readonly string $id;
    public readonly string $name;
    public readonly string $desc;
    public readonly bool $closed;
    public readonly string $idOrganization;
    public readonly string $url;
    public readonly string $permissionLevel;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->idOrganization = $data['idOrganization'];
        $this->name = $data['name'];
        $this->desc = $data['desc'];
        $this->closed = $data['closed'];
        $this->url = $data['url'];
        $this->permissionLevel = $data['prefs']['permissionLevel'];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'idOrganization' => $this->idOrganization,
            'name' => $this->name,
            'desc' => $this->desc,
            'closed' => $this->closed,
            'url' => $this->url,
            'permissionLevel' => $this->permissionLevel,
        ];
    }
}
