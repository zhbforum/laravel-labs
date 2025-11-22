<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessageJob;
use App\Models\SchedulerLog;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CheckExpiredTasks extends Command
{
    protected $signature = 'app:check-expired-tasks';

    protected $description = 'Mark in_progress tasks older than 7 days (by due_date) as expired and send tg notifications';

    public function handle(): int
    {
        $log = SchedulerLog::start($this->signature, 'Checking overdue in_progress tasks by due_date');

        try {
            $threshold = Carbon::now()->subDays(7)->startOfDay();
            $count = 0;

            DB::transaction(function () use (&$count, $threshold) {
                Task::where('status', 'in_progress')
                    ->whereNotNull('due_date')
                    ->where('due_date', '<=', $threshold->toDateString())
                    ->orderBy('id')
                    ->chunkById(100, function ($tasks) use (&$count) {
                        foreach ($tasks as $task) {
                            $task->status = 'expired';
                            $task->save();

                            $count++;

                            $message = sprintf(
                                'Task #%d "%s" has been marked as expired (due_date more than 7 days ago).',
                                $task->id,
                                $task->title ?? 'No title'
                            );

                            SendTelegramMessageJob::dispatch($message);
                        }
                    });
            });

            $this->info("Expired {$count} tasks.");
            $log->markSuccess("Expired {$count} tasks.");

            return SymfonyCommand::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error while checking expired tasks: ' . $e->getMessage());
            $log->markFailed($e->getMessage());

            return SymfonyCommand::FAILURE;
        }
    }
}
