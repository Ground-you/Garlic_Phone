<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $lobbyCode,
        public string $sessionId,
        public int    $submittedCount,
        public int    $totalCount,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("game.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'topic.submitted'; }

    public function broadcastWith(): array
    {
        return [
            'session_id'      => $this->sessionId,
            'submitted_count' => $this->submittedCount,
            'total_count'     => $this->totalCount,
        ];
    }
}