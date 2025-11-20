<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private ?string $chatId;
    private string $apiUrl = "https://api.telegram.org";

    public function __construct()
    {
        $this->botToken = (string) config("services.telegram.bot_token");
        $this->chatId = config("services.telegram.chat_id");
    }

    public function sendMessage(string $text, ?string $chatId = null): void
    {
        $chatId ??= $this->chatId;

        if (empty($this->botToken) || empty($chatId)) {
            Log::warning(
                "TelegramService: bot token or chat id is not configured."
            );
            return;
        }

        $url = "{$this->apiUrl}/bot{$this->botToken}/sendMessage";

        try {
            $response = Http::post($url, [
                "chat_id" => $chatId,
                "text" => $text,
                "parse_mode" => "HTML",
            ]);

            Log::info("Telegram message sent", [
                "chat_id" => $chatId,
                "text" => $text,
                "status" => $response->status(),
                "response" => $response->json(),
            ]);

            if ($response->failed()) {
                Log::error("Telegram API error", [
                    "chat_id" => $chatId,
                    "text" => $text,
                    "status" => $response->status(),
                    "response" => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("TelegramService exception", [
                "message" => $e->getMessage(),
            ]);
        }
    }
}
