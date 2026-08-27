<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameState extends Model
{
    protected $fillable = ['lobby_code', 'current_round', 'total_rounds'];
}