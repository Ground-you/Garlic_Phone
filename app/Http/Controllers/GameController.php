<?php
namespace App\Http\Controllers;

use App\Events\TopicSubmitted;
use App\Models\Lobby;
use App\Models\LobbyPlayer;
use App\Models\GameTopic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function show(Request $request, string $code)
    {
        $lobby = Lobby::where('code', $code)->firstOrFail();
        $sessionId = session()->getId();

        $me = LobbyPlayer::where('lobby_code', $code)
            ->where('session_id', $sessionId)->first();

        $players = LobbyPlayer::where('lobby_code', $code)
            ->orderByDesc('is_host')->orderBy('created_at')
            ->get(['nickname', 'avatar', 'is_host', 'session_id'])
            ->toArray();

        return Inertia::render('Game', [
            'lobbyCode'      => $lobby->code,
            'mySessionId'    => $sessionId,
            'nickname'       => $me->nickname ?? '플레이어',
            'avatar'         => $me->avatar ?? '/images/profile.png',
            'isHost'         => (bool) ($me->is_host ?? false),
            'mode'           => $lobby->mode,
            'maxPlayers'     => (int) $lobby->max_players,
            'timeLimit'      => (int) $lobby->time_limit,
            'players'        => $players,
            'submittedCount' => GameTopic::where('lobby_code', $code)->count(),
            'submittedSessions' => GameTopic::where('lobby_code', $code)->pluck('session_id'),
        ]);
    }

    public function submitTopic(Request $request, string $code)
    {
        $sessionId = session()->getId();
        $content = trim($request->input('content', ''));

        if ($content === '') {
            $defaults = ['산타를 타는 루돌프', '우주를 나는 고양이', '피자를 먹는 로봇', '춤추는 눈사람'];
            $content = $defaults[array_rand($defaults)];
        }

        GameTopic::updateOrCreate(
            ['lobby_code' => $code, 'session_id' => $sessionId],
            ['content' => $content]
        );

        $total     = LobbyPlayer::where('lobby_code', $code)->count();
        $submitted = GameTopic::where('lobby_code', $code)->count();

        broadcast(new TopicSubmitted($code, $sessionId, $submitted, $total));

        return response()->json(['ok' => true, 'submitted' => $submitted, 'total' => $total]);
    }
}