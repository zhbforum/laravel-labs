<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Report;
use App\Models\SchedulerLog;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class GenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-report
                            {--store-file : Also save JSON report file to storage/app/reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate per-project task statistics (todo, in_progress, done, expired) and store them in the reports table (and optionally as a JSON file).";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Generating tasks report...");

        $log = SchedulerLog::start(
            $this->signature,
            "Generating per-project task statistics"
        );

        try {
            $now = Carbon::now();

            $statusTodo = "todo";
            $statusInProgress = "in_progress";
            $statusDone = "done";

            $deadlineColumn = "due_date";

            $rows = Task::query()
                ->select("project_id")
                ->selectRaw("count(*) as total")
                ->selectRaw(
                    "sum(case when status = ? then 1 else 0 end) as todo",
                    [$statusTodo]
                )
                ->selectRaw(
                    "sum(case when status = ? then 1 else 0 end) as in_progress",
                    [$statusInProgress]
                )
                ->selectRaw(
                    "sum(case when status = ? then 1 else 0 end) as done",
                    [$statusDone]
                )
                ->selectRaw(
                    "
                sum(
                    case
                        when {$deadlineColumn} is not null
                         and {$deadlineColumn} < ?
                         and status != ?
                        then 1 else 0
                    end
                ) as expired
            ",
                    [$now, $statusDone]
                )
                ->groupBy("project_id")
                ->get()
                ->keyBy("project_id");

            if ($rows->isEmpty()) {
                $msg = "No tasks found. Nothing to report.";
                $this->warn($msg);

                $log->markSuccess($msg);

                return SymfonyCommand::SUCCESS;
            }

            $projects = Project::query()
                ->whereIn("id", $rows->keys())
                ->get()
                ->keyBy("id");

            $items = $rows
                ->map(function ($row, int $projectId) use ($projects) {
                    $project = $projects->get($projectId);

                    return [
                        "project" => [
                            "id" => $projectId,
                            "name" => $project?->name,
                        ],
                        "tasks" => [
                            "total" => (int) $row->total,
                            "todo" => (int) $row->todo,
                            "in_progress" => (int) $row->in_progress,
                            "done" => (int) $row->done,
                            "expired" => (int) $row->expired,
                        ],
                    ];
                })
                ->values()
                ->all();

            $today = $now->toDateString();

            $report = Report::query()->create([
                "period_start" => $today,
                "period_end" => $today,
                "payload" => $items,
                "path" => "",
            ]);

            $this->info("Report #{$report->id} saved into `reports` table.");

            if ($this->option("store-file")) {
                $this->storeFile($report, $items, $now);
            }

            $finalMessage = sprintf(
                "Report #%d generated%s.",
                $report->id,
                $this->option("store-file") ? " and JSON file stored" : ""
            );

            $this->info("Report generation finished.");
            $log->markSuccess($finalMessage);

            return SymfonyCommand::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error while generating report: " . $e->getMessage());
            $log->markFailed($e->getMessage());

            return SymfonyCommand::FAILURE;
        }
    }

    protected function storeFile(
        Report $report,
        array $items,
        Carbon $now
    ): void {
        $filename = sprintf(
            "reports/tasks_report_%s_%s.json",
            $now->format("Ymd_His"),
            $report->id
        );

        $payload = [
            "report_id" => $report->id,
            "period_start" => $report->period_start,
            "period_end" => $report->period_end,
            "generated_at" => $now->toIso8601String(),
            "projects" => $items,
        ];

        Storage::disk("local")->put(
            $filename,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $report->update([
            "path" => $filename,
        ]);

        $this->info("JSON report saved to storage/app/{$filename}");
    }
}
