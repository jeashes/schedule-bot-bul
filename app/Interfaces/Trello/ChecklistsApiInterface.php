<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Client\Response;

interface ChecklistsApiInterface
{
    public function createCheckItem(string $idCheckList, string $name): Response;

    public function createChecklist(
        string $idCard,
        ?string $name = null,
        ?string $pos = null,
        ?string $idChecklistSource = null
    ): Response;

    public function getChecklist(
        string $idChecklist,
        ?string $cards = null,
        ?string $checkItems = null,
        ?string $checkItemsFields = null,
        ?string $fields = null
    ): Response;

    public function deleteChecklist(string $idChecklist): Response;

    public function getCheckItem(string $idChecklist, string $idCheckItem, ?string $fields = null): Response;

    public function deteleteCheckItem(string $idChecklist, string $idCheckItem): Response;

    public function updateChecklist(string $idCheklist, ?string $name = null, ?string $pos = null): Response;
}
