<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LobbyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nickname'  => 'required|string|max:12',
            'mode'      => 'required|string',
            'players'   => 'required|integer',
            'timeLimit' => 'required|integer',
            'avatar'    => 'nullable|string', // ✅ avatar 추가
        ]);

        $lobbyId = rand(10000, 99990);

        // ✅ 세션에 방 정보 저장 (게스트가 입장할 때 방장 정보를 가져오기 위해)
        session(["lobby_{$lobbyId}" => [
            'hostNickname' => $validated['nickname'],
            'hostAvatar'   => $validated['avatar'] ?? '/images/profile.png',
            'mode'         => $validated['mode'],
            'players'      => $validated['players'],
            'timeLimit'    => $validated['timeLimit'],
        ]]);

        return redirect()->route('lobby.show', [
            'id'        => $lobbyId,
            'nickname'  => $validated['nickname'],
            'avatar'    => $validated['avatar'] ?? '',  // ✅ avatar 포함
            'mode'      => $validated['mode'],
            'players'   => $validated['players'],
            'timeLimit' => $validated['timeLimit'],
            'isHost'    => 'true',
        ]);
    }

    public function show(Request $request, $id)
    {
        // ✅ isHost 기본값 'false'로 변경 (store()에서 온 방장만 'true')
        $isHost = $request->query('isHost', 'false') === 'true';

        // 닉네임: 쿼리 파라미터 > Discord 로그인 유저명 > 게스트 랜덤
        $nickname = $request->query('nickname')
            ?? ($request->user()?->name)
            ?? ('게스트_' . rand(100, 999));

        // 아바타: 쿼리 파라미터 > Discord 아바타 URL > 기본 이미지
        $avatar = $request->query('avatar')
            ?? ($request->user()?->avatar_url)
            ?? '/images/profile.png';

        // ✅ 세션에서 방 정보 조회 (게스트용)
        $lobbyData = session("lobby_{$id}", []);

        return Inertia::render('Lobby', [
            'lobbyId'  => $id,
            'nickname' => $nickname,
            'avatar'   => $avatar ?: '/images/profile.png',

            // 방장 정보: 내가 방장이면 내 정보, 게스트면 세션의 방장 정보
            'hostNickname' => $isHost
                ? $nickname
                : ($lobbyData['hostNickname'] ?? '방장'),
            'hostAvatar' => $isHost
                ? ($avatar ?: '/images/profile.png')
                : ($lobbyData['hostAvatar'] ?? '/images/profile.png'),

            // 방 설정: 방장이면 쿼리 파라미터, 게스트면 세션
            'mode' => $isHost
                ? $request->query('mode', 'normal')
                : ($lobbyData['mode'] ?? 'normal'),
            'players' => (int)($isHost
                ? $request->query('players', 8)
                : ($lobbyData['players'] ?? 8)),
            'timeLimit' => (int)($isHost
                ? $request->query('timeLimit', 40)
                : ($lobbyData['timeLimit'] ?? 40)),

            'isHost' => $isHost,
        ]);
    }
}