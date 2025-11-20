<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $text;
    public ?string $chatId;

    public function __construct(string $text, ?string $chatId = null)
    {
        $this->text   = $text;
        $this->chatId = $chatId;
    }

    public function handle(): void
    {
        Log::info('SendTelegramMessageJob started', [
            'text'   => $this->text,
            'chatId' => $this->chatId,
        ]);

        /** @var TelegramService $telegramService */
        $telegramService = app(TelegramService::class);

        $telegramService->sendMessage($this->text, $this->chatId);

        Log::info('SendTelegramMessageJob finished');
    }
}
