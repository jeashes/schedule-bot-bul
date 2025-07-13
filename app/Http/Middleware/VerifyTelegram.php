<?php

namespace App\Http\Middleware;

use App\Models\Mongo\User as MongoUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifyTelegram
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeSecret = $request->route('tg_secret');

        abort_unless($routeSecret === config('telegram.secret_token'), 403, 'Invalid Telegram webhook secret');

        $telegramId = $request->input('message.from.id')
            ?? $request->input('callback_query.from.id')
            ?? $request->input('inline_query.from.id');

        if (! $telegramId) {
            Log::channel('telegram')->info('Request has not from.id', $request->toArray());

            return response('ignored', 200);
        }

        $user = MongoUser::query()
            ->where(['telegram_id' => $telegramId])
            ->orWhere(['chat_id' => $telegramId])
            ->firstOrFail();

        Auth::login($user);

        return next($request);
    }
}
