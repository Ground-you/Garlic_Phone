<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundAdvanced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $lobbyCode,
        public int    $round,
        public string $type,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("game.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'round.advanced'; }

    public function broadcastWith(): array
    {
        return ['round' => $this->round, 'type' => $this->type];
    }
}