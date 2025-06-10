<?php

namespace App\Service\Google;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CustomSearchClient extends BaseClient
{
    private const CUSTOM_SEARCH_URI = 'https://www.googleapis.com/customsearch/v1';

    public function __construct(GoogleConfig $config)
    {
        parent::__construct($config->apiKey, $config->cx);
    }

    public function search(string $title): Response
    {
        $params = $this->prepareApiTokenParams($title);

        return Http::withHeaders($this->prepareHeaders())
            ->get(self::CUSTOM_SEARCH_URI, $params);
    }

    private function prepareApiTokenParams(string $querySearch): array
    {
        return [
            'key' => $this->apiKey,
            'cx' => $this->cx,
            'start' => 1,
            'q' => $querySearch,
            'gl' => 'en',
            'hl' => 'en',
        ];
    }
}
