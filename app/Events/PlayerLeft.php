<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeft implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $lobbyCode,
        public string $sessionId,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'player.left'; }

    public function broadcastWith(): array
    {
        return ['session_id' => $this->sessionId];
    }
}