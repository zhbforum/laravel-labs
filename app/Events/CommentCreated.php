<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $projectId,
        public int $taskId,
        public string $body,
        public string $author,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("project.{$this->projectId}");
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->taskId,
            'body'    => $this->body,
            'author'  => $this->author,
        ];
    }
}
