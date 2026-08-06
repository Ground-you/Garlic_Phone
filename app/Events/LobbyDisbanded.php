<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LobbyDisbanded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $lobbyCode) {}

    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'lobby.disbanded'; }

    public function broadcastWith(): array
    {
        return ['message' => '방장이 방을 해체했습니다.'];
    }
}