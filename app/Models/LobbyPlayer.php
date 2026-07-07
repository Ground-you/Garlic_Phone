<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LobbyPlayer extends Model
{
    protected $fillable = [
        'lobby_code', 'nickname', 'avatar',
        'is_host', 'is_ready', 'session_id',
    ];
}