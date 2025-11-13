<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTaskCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = "notifications";

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;

        Log::info("Task created notification sent", [
            "task_id" => $task->id,
            "title" => $task->title ?? null,
            "project_id" => $task->project_id ?? null,
            "status" => $task->status ?? null,
            "created_at" => now()->toDateTimeString(),
        ]);
    }
}
