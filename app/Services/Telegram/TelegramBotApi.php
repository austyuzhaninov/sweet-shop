<?php

namespace App\Services\Telegram;

use App\Logging\Telegram\Exceptions\TelegramBotApiException;
use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramBotApi
{
    const HOST = 'https://api.telegram.org/bot';

    public static function sendMessage(string $token, int $chatId, string $text): bool
    {
        /**
         * HOMEWORK: обернул в try, с запроса получаю статус код, если успешный (200) отдаю true
         */
        try {
            $result = Http::get(
                self::HOST . $token . '/sendMessage',
                [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]
            );

            return $result->getStatusCode() == 200;

        } catch (Throwable $exception) {
            throw new TelegramBotApiException($exception);
        }
    }
}
