<?php

use Illuminate\Support\Facades\Route;
use App\Services\TelegramService;
use App\Jobs\SendTelegramMessageJob;
use App\Events\TaskCreated;
use App\Models\Task;

Route::get("/", function () {
    return view("welcome");
});

Route::get("/telegram-test", function (TelegramService $telegramService) {
    $telegramService->sendMessage("Laravel TelegramService test: it works!");

    return "Telegram test sent (check tg)";
});

Route::get("/telegram-job-test", function () {
    SendTelegramMessageJob::dispatch("Test of job: message from Laravel queue");

    return "Telegram job dispatched (check tg + queue-worker)";
});

Route::get("/task-created-test", function () {
    $task = Task::query()->firstOrFail();

    event(new TaskCreated($task));

    return "TaskCreated event dispatched (check Telegram)";
});
