<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSubmission extends Model
{
    protected $fillable = ['lobby_code', 'session_id', 'round', 'type', 'content'];
}