<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $lobbyCode) {}

    public function broadcastOn(): array
    {
        return [new Channel("game.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'game.ended'; }

    public function broadcastWith(): array
    {
        return ['lobby_code' => $this->lobbyCode];
    }
}