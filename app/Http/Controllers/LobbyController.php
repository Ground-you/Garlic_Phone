<?php
namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\ChatToggled;
use App\Events\PlayerJoined;
use App\Events\PlayerLeft;
use App\Events\PlayerReady;
use App\Events\LobbyDisbanded;
use App\Events\GameStarted;
use App\Models\Lobby;
use App\Models\LobbyPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LobbyController extends Controller
{
    // POST /lobby — 방 생성
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nickname'  => 'required|string|max:12',
            'mode'      => 'required|string',
            'players'   => 'required|integer|min:2|max:12',
            'timeLimit' => 'required|integer|min:10|max:120',
            'avatar'    => 'nullable|string',
            'statusMessage' => 'nullable|string|max:80',
        ]);

        do { $code = (string) rand(10000, 99999); }
        while (Lobby::where('code', $code)->exists());

        // Discord 아바타 우선, blob이면 기본 이미지
        $avatar = $request->user()?->avatar_url
            ?? (str_starts_with($validated['avatar'] ?? '', 'blob:')
                ? '/images/profile.png'
                : ($validated['avatar'] ?? '/images/profile.png'));

        Lobby::create([
            'code'          => $code,
            'host_nickname' => $validated['nickname'],
            'host_avatar'   => $avatar,
            'mode'          => $validated['mode'],
            'max_players'   => $validated['players'],
            'time_limit'    => $validated['timeLimit'],
            'chat_enabled'  => true,
        ]);

        LobbyPlayer::create([
            'lobby_code' => $code,
            'nickname'   => $validated['nickname'],
            'avatar'     => $avatar,
            'status_message' => $validated['statusMessage'] ?? null,
            'is_host'    => true,
            'is_ready'   => true,
            'session_id' => session()->getId(),
        ]);

        // nickname을 redirect에 포함
        return redirect()->route('lobby.show', [
            'id'       => $code,
            'isHost'   => 'true',
            'nickname' => $validated['nickname'],
        ]);
    }

    // GET /lobby/{id} — 대기방
    public function show(Request $request, $id)
    {
        $lobby = Lobby::where('code', $id)->firstOrFail();
        $isHost = $request->query('isHost', 'false') === 'true';
        $sessionId = session()->getId();

        // 1. 현재 세션의 플레이어 정보 먼저 조회
        $player = LobbyPlayer::where('lobby_code', $id)
            ->where('session_id', $sessionId)
            ->first();

        // 2. 아바타 기본값 정리
        $avatarParam = $request->query('avatar', '');
        $fallbackAvatar = (!empty($avatarParam) && !str_starts_with($avatarParam, 'blob:'))
            ? $avatarParam
            : '/images/profile.png';

        $nickname = $player?->nickname
            ?? $request->query('nickname')
            ?? $request->user()?->name
            ?? ($isHost ? '방장' : '멋진별명' . rand(100, 999));

        $avatar = $player?->avatar
            ?? $request->user()?->avatar_url
            ?? $fallbackAvatar;

        $statusMessage = $request->query('statusMessage', $player?->status_message ?? '');

        } else {
            $nickname = $request->query('nickname')
                ?? $request->user()?->name
                ?? '멋진별명' . rand(100, 999);

            $avatarParam = $request->query('avatar', '');
            $avatar = $request->user()?->avatar_url
                ?? ((!empty($avatarParam) && !str_starts_with($avatarParam, 'blob:'))
                    ? $avatarParam
                    : '/images/profile.png');

            $statusMessage = $request->query('statusMessage', ''); // ✅ 추가

            // 4. DB에 없으면 신규 생성, 있으면 최신 정보로 업데이트
            if (!$player) {
                $player = LobbyPlayer::create([
                    'lobby_code'     => $id,
                    'nickname'       => $nickname,
                    'avatar'         => $avatar,
                    'status_message' => $statusMessage,
                    'is_host'        => $isHost,
                    'is_ready'       => false,
                    'session_id'     => $sessionId,
                ]);

                if (!$isHost) {
                    broadcast(new PlayerJoined(
                        $id, $nickname, $avatar, $statusMessage, false, $sessionId
                    ));
                }
            } else {
                // 세션이 이미 존재하더라도 한마디/프로필 데이터 최신화
                $player->update([
                    'nickname'       => $nickname,
                    'avatar'         => $avatar,
                    'status_message' => $statusMessage,
                ]);
            }
        } 

        // 5. 로비 내 전체 참가자 목록 조회
        $initialPlayers = LobbyPlayer::where('lobby_code', $id)
            ->orderByDesc('is_host')
            ->orderBy('created_at')
            ->get(['nickname', 'avatar', 'status_message', 'is_host', 'is_ready', 'session_id'])
            ->toArray();

        return Inertia::render('Lobby', [
            'lobbyCode'      => $lobby->code,
            'lobbyId'        => $lobby->id,
            'mySessionId'    => $sessionId,
            'nickname'       => $nickname,
            'avatar'         => $avatar,
            'hostNickname'   => $lobby->host_nickname,
            'hostAvatar'     => $lobby->host_avatar ?? '/images/profile.png',
            'mode'           => $lobby->mode,
            'players'        => (int) $lobby->max_players,
            'timeLimit'      => (int) $lobby->time_limit,
            'isHost'         => $isHost,
            'chatEnabled'    => $lobby->chat_enabled,
            'initialPlayers' => $initialPlayers,
        ]);
    }

    // POST /upload-avatar — 아바타 파일 업로드
    public function uploadAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $path = $request->file('avatar')->store('avatars', 'public');
        return response()->json(['url' => Storage::url($path)]);
    }

    // POST /lobby/{code}/chat
    public function chat(Request $request, string $code)
    {
        $request->validate([
            'message'  => 'required|string|max:200',
            'nickname' => 'required|string',
            'avatar'   => 'nullable|string',
        ]);

        $lobby = Lobby::where('code', $code)->firstOrFail();

        if (!$lobby->chat_enabled) {
            return response()->json(['error' => '채팅이 비활성화되었습니다.'], 403);
        }

        broadcast(new ChatMessageSent(
            $code,
            $request->nickname,
            $request->avatar ?? '/images/profile.png',
            $request->message,
        ));

        return response()->json(['ok' => true]);
    }

    // PATCH /lobby/{code}/toggle-chat
    public function toggleChat(Request $request, string $code)
    {
        $lobby = Lobby::where('code', $code)->firstOrFail();
        $lobby->update(['chat_enabled' => $request->boolean('enabled')]);
        broadcast(new ChatToggled($code, (bool) $lobby->chat_enabled));
        return response()->json(['enabled' => $lobby->chat_enabled]);
    }

    // DELETE /lobby/{code}/leave
    public function leave(Request $request, string $code)
    {
        $sessionId = session()->getId();

        LobbyPlayer::where('lobby_code', $code)
            ->where('session_id', $sessionId)
            ->delete();

        if ($request->boolean('isHost')) {
            // 삭제 전에 broadcast (삭제 후엔 채널이 없어서 못 받음)
            broadcast(new LobbyDisbanded($code));
            Lobby::where('code', $code)->delete();
        } else {
            broadcast(new PlayerLeft($code, $sessionId))->toOthers();
        }

        return response()->json(['ok' => true]);
    }

    public function ready(Request $request, string $code)
    {
        $sessionId = session()->getId();
        $isReady   = $request->boolean('is_ready');

        LobbyPlayer::where('lobby_code', $code)
            ->where('session_id', $sessionId)
            ->update(['is_ready' => $isReady]);

        broadcast(new PlayerReady($code, $sessionId, $isReady));

        return response()->json(['ok' => true]);
    }

    public function start(Request $request, string $code)
    {
        $sessionId = session()->getId();

        // 방장 검증
        $isHost = LobbyPlayer::where('lobby_code', $code)
            ->where('session_id', $sessionId)
            ->where('is_host', true)
            ->exists();

        if (!$isHost) {
            return response()->json(['error' => '방장만 게임을 시작할 수 있습니다.'], 403);
        }

        // (선택) 전원 준비 확인
        $notReadyCount = LobbyPlayer::where('lobby_code', $code)
            ->where('is_host', false)
            ->where('is_ready', false)
            ->count();

        if ($notReadyCount > 0) {
            return response()->json(['error' => '아직 준비되지 않은 플레이어가 있습니다.'], 422);
        }

        broadcast(new GameStarted($code))->toOthers();

        return response()->json(['ok' => true]);
    }
}