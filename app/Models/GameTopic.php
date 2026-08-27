<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameTopic extends Model
{
    protected $fillable = ['lobby_code', 'session_id', 'content'];
}