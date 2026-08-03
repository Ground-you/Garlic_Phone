<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerReady implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $lobbyCode,
        public string $sessionId,
        public bool   $isReady,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'player.ready'; }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'is_ready'   => $this->isReady,
        ];
    }
}