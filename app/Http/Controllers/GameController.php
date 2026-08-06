<?php
namespace App\Http\Controllers;

use App\Models\Lobby;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function show(Request $request, string $code)
    {
        $lobby = Lobby::where('code', $code)->firstOrFail();
        $sessionId = session()->getId();

        return Inertia::render('Game', [
            'lobbyCode'   => $lobby->code,
            'mySessionId' => $sessionId,
        ]);
    }
}