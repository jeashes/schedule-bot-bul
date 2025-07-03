<?php

namespace Tests\Feature\Telegram;

use Tests\TestCase;

class WebhookTest extends TestCase
{
    public function test_rejects_requests_with_an_invalid_telegram_id(): void
    {
        $secret = config('telegram.secret_token');

        $response = $this->postJson("/api/webhook/{$secret}", [
            'message' => [
                'from' => [
                    'id' => 999999999
                ],
            ]
        ]);

        $response->assertStatus(404);
    }

    public function test_rejects_requests_with_an_invalid_secret_token(): void
    {
        $secret = 'test secret';
        $response = $this->postJson("/api/webhook/{$secret}", [
            'message' => [
                'from' => [
                    'id' => 999999999
                ]
            ]
        ]);

        $response->assertStatus(403);
    }

    public function test_successfully_pass_when_telegram_id_path_is_empty(): void
    {
        $secret = config('telegram.secret_token');

        $response = $this->postJson("/api/webhook/{$secret}");

        $response->assertStatus(200);
    }
}
