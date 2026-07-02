<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class DiscordController extends Controller
{
    public function redirectToDiscord(): RedirectResponse
    {
        return Socialite::driver('discord')
            ->stateless()
            ->scopes(['identify', 'email'])
            ->redirect();
    }

    public function handleDiscordCallback(): RedirectResponse
    {
        try {
            $discordUser = Socialite::driver('discord')->stateless()->user();

            // 디스코드 ID를 기준으로 정보를 찾거나 생성/업데이트
            $user = User::updateOrCreate(
                ['discord_id' => $discordUser->getId()],
                [
                    'name' => $discordUser->getName(),
                    'email' => $discordUser->getEmail(),
                    'avatar_url' => $discordUser->getAvatar(),
                    'session_id' => null, 
                ]
            );

          
            Auth::login($user, true);
            request()->session()->regenerate();

            return redirect()->to('http://127.0.0.1:8000');

        } catch (\Exception $e) {
            Log::error('디스코드 로그인 실패: ' . $e->getMessage());
            return redirect()->to('http://127.0.0.1:8000')->with('error', '인증 실패');
        }
    }
}