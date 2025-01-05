<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Client\Response;

interface OrganizationApiInterface
{
    public function create(string $displayName, ?string $description, ?string $name): Response;

    public function get(string $id): Response;

    public function update(string $id): Response;

    public function delete(string $id): Response;
}
