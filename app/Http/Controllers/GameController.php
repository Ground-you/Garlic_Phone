<?php
namespace App\Http\Controllers;

use App\Events\RoundAdvanced;
use App\Events\TopicSubmitted;
use App\Models\Lobby;
use App\Models\LobbyPlayer;
use App\Models\GameSubmission;
use App\Models\GameState;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    // GET /game/{code} — 인게임 페이지 최초 진입
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

        // 게임 상태 없으면 새로 생성 (첫 진입한 사람 기준)
        $state = GameState::firstOrCreate(
            ['lobby_code' => $code],
            ['current_round' => 0, 'total_rounds' => count($players)]
        );

        $roundData = $this->buildRoundData($code, $sessionId, $players, $state);

        return Inertia::render('Game', [
            'lobbyCode'   => $lobby->code,
            'mySessionId' => $sessionId,
            'nickname'    => $me->nickname ?? '플레이어',
            'avatar'      => $me->avatar ?? '/images/profile.png',
            'isHost'      => (bool) ($me->is_host ?? false),
            'mode'        => $lobby->mode,
            'timeLimit'   => (int) $lobby->time_limit,
            'players'     => $players,
            'round'       => $roundData,
        ]);
    }

    // GET /game/{code}/round — 현재 라운드 데이터 다시 조회 (라운드 넘어갈 때 프론트에서 호출)
    public function roundData(Request $request, string $code)
    {
        $sessionId = session()->getId();
        $players = LobbyPlayer::where('lobby_code', $code)
            ->orderByDesc('is_host')->orderBy('created_at')
            ->get(['nickname', 'avatar', 'is_host', 'session_id'])
            ->toArray();
        $state = GameState::where('lobby_code', $code)->firstOrFail();

        return response()->json(
            $this->buildRoundData($code, $sessionId, $players, $state)
        );
    }

    // POST /game/{code}/submit — 텍스트/그림 공통 제출
    public function submit(Request $request, string $code)
    {
        $request->validate([
            'round'   => 'required|integer',
            'type'    => 'required|in:text,drawing',
            'content' => 'nullable|string',
        ]);

        $sessionId = session()->getId();
        $state = GameState::where('lobby_code', $code)->firstOrFail();

        // 이미 지난 라운드에 대한 늦은 제출은 무시
        if ((int) $request->round !== $state->current_round) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $content = trim($request->content ?? '');
        if ($content === '') {
            $content = $request->type === 'text'
                ? '(제출 안 함)'
                : $content; // 빈 그림은 그대로 둠
        }

        GameSubmission::updateOrCreate(
            ['lobby_code' => $code, 'session_id' => $sessionId, 'round' => $state->current_round],
            ['type' => $request->type, 'content' => $content]
        );

        $totalPlayers = LobbyPlayer::where('lobby_code', $code)->count();
        $submittedCount = GameSubmission::where('lobby_code', $code)
            ->where('round', $state->current_round)->count();

        broadcast(new \App\Events\TopicSubmitted($code, $sessionId, $submittedCount, $totalPlayers));

        // 전원 제출 완료 → 다음 라운드로
        if ($submittedCount >= $totalPlayers) {
            $state->current_round += 1;
            $state->save();

            if ($state->current_round >= $state->total_rounds) {
                broadcast(new RoundAdvanced($code, $state->current_round, 'finished'));
            } else {
                $nextType = $state->current_round % 2 === 1 ? 'drawing' : 'text';
                broadcast(new RoundAdvanced($code, $state->current_round, $nextType));
            }
        }

        return response()->json(['ok' => true, 'submitted' => $submittedCount, 'total' => $totalPlayers, 'session_id' => $sessionId,]);
    }

    // 현재 라운드에 맞는 데이터(타입, 이전 제출물, 제출 현황) 계산
    private function buildRoundData(string $code, string $sessionId, array $players, GameState $state): array
    {
        $round = $state->current_round;
        $finished = $round >= $state->total_rounds;

        $type = $round === 0 ? 'text' : ($round % 2 === 1 ? 'drawing' : 'text');

        $previousContent = null;
        if ($round > 0) {
            $prevSubmission = $this->getPreviousSubmission($players, $code, $sessionId, $round);
            $previousContent = $prevSubmission?->content;
        }

        $mySubmission = GameSubmission::where('lobby_code', $code)
            ->where('session_id', $sessionId)
            ->where('round', $round)->first();

        $totalPlayers = count($players);
        $submittedCount = GameSubmission::where('lobby_code', $code)
            ->where('round', $round)->count();

        $submittedSessions = GameSubmission::where('lobby_code', $code)
            ->where('round', $round)->pluck('session_id');

        return [
            'round'             => $round,
            'totalRounds'       => $state->total_rounds,
            'type'              => $type,
            'finished'          => $finished,
            'previousContent'   => $previousContent,
            'hasSubmitted'      => (bool) $mySubmission,
            'submittedCount'    => $submittedCount,
            'totalPlayers'      => $totalPlayers,
            'submittedSessions' => $submittedSessions,
        ];
    }

    // 내가 이번 라운드에 받아야 할 이전 사람의 제출물 찾기
    private function getPreviousSubmission(array $players, string $lobbyCode, string $mySessionId, int $round)
    {
        $index = collect($players)->search(fn($p) => $p['session_id'] === $mySessionId);
        $totalPlayers = count($players);
        $prevIndex = ($index - $round + $totalPlayers) % $totalPlayers;
        $prevSessionId = $players[$prevIndex]['session_id'];

        return GameSubmission::where('lobby_code', $lobbyCode)
            ->where('session_id', $prevSessionId)
            ->where('round', $round - 1)
            ->first();
    }
}