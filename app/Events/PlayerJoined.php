<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // ← 변경
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $lobbyCode,
        public string $nickname,
        public string $avatar,
        public bool   $isHost,
        public string $sessionId,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'player.joined'; }

    public function broadcastWith(): array
    {
        return [
            'nickname'   => $this->nickname,
            'avatar'     => $this->avatar,
            'is_host'    => $this->isHost,
            'session_id' => $this->sessionId,
        ];
    }
}