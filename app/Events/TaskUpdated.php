<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $projectId,
        public int $taskId,
        public string $title,
        public string $status,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("project.{$this->projectId}");
    }

    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'     => $this->taskId,
            'title'  => $this->title,
            'status' => $this->status,
        ];
    }
}
