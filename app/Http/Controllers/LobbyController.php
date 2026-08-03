<?php
namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\ChatToggled;
use App\Events\PlayerJoined;
use App\Events\PlayerLeft;
use App\Events\PlayerReady;
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
            'is_host'    => true,
            'is_ready'   => true,
            'session_id' => session()->getId(),
        ]);

        // ✅ nickname을 redirect에 포함
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

        if ($isHost) {
            // ✅ 방장: DB에서 닉네임/아바타 직접 읽기 (redirect 후에도 정확)
            $hostPlayer = LobbyPlayer::where('lobby_code', $id)
                ->where('session_id', $sessionId)
                ->where('is_host', true)
                ->first();

            $nickname = $hostPlayer?->nickname
                ?? $request->query('nickname')
                ?? $request->user()?->name
                ?? '방장';
            $avatar = $hostPlayer?->avatar ?? '/images/profile.png';

        } else {
            // 게스트: 쿼리 파라미터
            $nickname = $request->query('nickname')
                ?? $request->user()?->name
                ?? '게스트_' . rand(100, 999);

            $avatarParam = $request->query('avatar', '');
            $avatar = $request->user()?->avatar_url
                ?? ((!empty($avatarParam) && !str_starts_with($avatarParam, 'blob:'))
                    ? $avatarParam
                    : '/images/profile.png');

            // 게스트 DB 등록 + broadcast
            $existing = LobbyPlayer::where('lobby_code', $id)
                ->where('session_id', $sessionId)->first();

            if (!$existing) {
                LobbyPlayer::create([
                    'lobby_code' => $id,
                    'nickname'   => $nickname,
                    'avatar'     => $avatar,
                    'is_host'    => false,
                    'is_ready'   => false,
                    'session_id' => $sessionId,
                ]);

                broadcast(new PlayerJoined(
                    $id, $nickname, $avatar, false, $sessionId
                ))->toOthers();
            }
        }

        $initialPlayers = LobbyPlayer::where('lobby_code', $id)
            ->orderByDesc('is_host')
            ->orderBy('created_at')
            ->get(['nickname', 'avatar', 'is_host', 'is_ready', 'session_id'])
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

        broadcast(new PlayerLeft($code, $sessionId))->toOthers();

        if ($request->boolean('isHost')) {
            Lobby::where('code', $code)->delete();
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
}