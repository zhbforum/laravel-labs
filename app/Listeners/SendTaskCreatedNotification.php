<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Support\Facades\Log;

class SendTaskCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;

        $title = $task->title ?? "Без назви";

        $message =
            "Нова задача створена\n" .
            "ID: {$task->id}\n" .
            "Назва: {$title}\n" .
            "Проєкт ID: {$task->project_id}\n" .
            "Статус: {$task->status}\n" .
            "Створено: {$task->created_at}";

        Log::info("Task created notification queued", [
            "task_id" => $task->id,
            "title" => $title,
            "project_id" => $task->project_id ?? null,
            "status" => $task->status ?? null,
            "created_at" => now()->toDateTimeString(),
        ]);

        SendTelegramMessageJob::dispatch($message);
    }
}
