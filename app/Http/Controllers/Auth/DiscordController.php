<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * 1. 사용자를 디스코드 인증 페이지로 리다이렉트
     */
    public function redirectToDiscord(): RedirectResponse
    {
        return Socialite::driver('discord')
            ->scopes(['identify', 'email'])
            ->redirect();
    }

    /**
     * 2. 디스코드 인증 완료 후 데이터 처리 및 세션 로그인
     */
    public function handleDiscordCallback(): RedirectResponse
    {
        try {
            // 디스코드로부터 유저 객체 수신
            $discordUser = Socialite::driver('discord')->user();

            // DB에 기존 디스코드 ID가 있는지 확인 후 업데이트 또는 새로 생성
            $user = User::updateOrCreate(
                ['discord_id' => $discordUser->getId()],
                [
                    'name' => $discordUser->getName(), // 디스코드 닉네임 반영
                    'avatar_url' => $discordUser->getAvatar(), // 디스코드 프로필 이미지 주소 반영
                    'session_id' => null, // 게스트 유저가 아니므로 null 처리
                ]
            );

            // 라라벨 Auth 시스템을 이용해 이 유저로 세션 로그인 처리
            Auth::login($user, true);

            // 로그인이 성공하면 메인 홈 화면으로 리다이렉트
            return redirect()->route('home');

        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', '디스코드 연동 중 오류가 발생했습니다.');
        }
    }
}