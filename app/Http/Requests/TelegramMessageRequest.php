<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'answer' => ['string', 'nullable'],
            'callbackData' => ['string', 'nullable'],
            'user' => ['array']
        ];
    }
}
