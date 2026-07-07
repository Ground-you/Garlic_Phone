<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $lobbyCode,
        public string $nickname,
        public string $avatar,
        public string $message,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobbyCode}")];
    }

    public function broadcastAs(): string { return 'chat.message'; }

    public function broadcastWith(): array
    {
        return [
            'nickname' => $this->nickname,
            'avatar'   => $this->avatar,
            'message'  => $this->message,
            'time'     => now()->format('H:i'),
        ];
    }
}