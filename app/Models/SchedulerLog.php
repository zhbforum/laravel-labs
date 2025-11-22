<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerLog extends Model
{
    protected $fillable = [
        "task_name",
        "status",
        "started_at",
        "finished_at",
        "message",
    ];

    protected $casts = [
        "started_at" => "datetime",
        "finished_at" => "datetime",
    ];

    public static function start(
        string $taskName,
        ?string $message = null
    ): self {
        return self::create([
            "task_name" => $taskName,
            "status" => "started",
            "started_at" => now(),
            "message" => $message,
        ]);
    }

    public function markSuccess(?string $message = null): void
    {
        $this->update([
            "status" => "success",
            "finished_at" => now(),
            "message" => $message ?? $this->message,
        ]);
    }

    public function markFailed(?string $message = null): void
    {
        $this->update([
            "status" => "failed",
            "finished_at" => now(),
            "message" => $message ?? $this->message,
        ]);
    }
}
