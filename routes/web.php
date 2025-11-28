<?php

use Illuminate\Support\Facades\Route;
use App\Services\TelegramService;
use App\Jobs\SendTelegramMessageJob;
use App\Events\TaskCreated;
use App\Models\Task;
use App\Events\TaskUpdated;
use App\Events\CommentCreated;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/telegram-test', function (TelegramService $telegramService) {
    $telegramService->sendMessage('Laravel TelegramService test: it works!');

    return 'Telegram test sent (check tg)';
});

Route::get('/telegram-job-test', function () {
    SendTelegramMessageJob::dispatch('Test of job: message from Laravel queue');

    return 'Telegram job dispatched (check tg + queue-worker)';
});

Route::get('/task-created-test', function () {
    $task = Task::query()->firstOrFail();

    event(new TaskCreated($task));

    return 'TaskCreated event dispatched (check Telegram)';
});

Route::get('/live', function () {
    return view('live');
})->name('live');

Route::get('/test/task', function () {
    broadcast(new TaskUpdated(
        projectId: 10,
        taskId: 101,
        title: 'Demo task from /test/task',
        status: 'in_progress',
    ));

    return 'TaskUpdated sent';
});

Route::get('/test/comment', function () {
    broadcast(new CommentCreated(
        projectId: 10,
        taskId: 101,
        body: 'Demo comment from /test/comment',
        author: 'Demo User',
    ));

    return 'CommentCreated sent';
});
