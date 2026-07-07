<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lobby extends Model
{
    protected $fillable = [
        'code', 'host_nickname', 'host_avatar',
        'mode', 'max_players', 'time_limit', 'chat_enabled',
    ];

    public function players()
    {
        return $this->hasMany(LobbyPlayer::class, 'lobby_code', 'code');
    }
}