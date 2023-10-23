<?php

namespace App\Logging\Telegram\Exceptions;

use Exception;
use Illuminate\Http\Request;

class TelegramBotApiException extends Exception
{

    // Отправить что либо
    public function report()
    {

    }

    // Отрендерить что либо на странице
    public function render(Request $request)
    {
        return response()->json([]);
    }
}
