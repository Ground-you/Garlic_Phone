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
    
            $avatarHash = $discordUser->user['avatar'] ?? null;
            $userId = $discordUser->getId();
    
            $avatarUrl = $avatarHash
                ? "https://cdn.discordapp.com/avatars/{$userId}/{$avatarHash}.png?size=256"
                : "https://cdn.discordapp.com/embed/avatars/" . ($discordUser->user['discriminator'] % 5) . ".png";
    
            $user = User::updateOrCreate([
                'discord_id' => $discordUser->getId(),
            ], [
                'name'          => $discordUser->getName(),
                'email'         => $discordUser->getEmail(),
                'avatar_url'    => $avatarUrl, // ← 직접 조립한 URL
                'discriminator' => $discordUser->user['discriminator'] ?? '0',
            ]);
    
            Auth::login($user, true);
            request()->session()->regenerate();
    
            return redirect()->to('/');
    
        } catch (\Exception $e) {
            Log::error('디스코드 로그인 실패: ' . $e->getMessage());
            return redirect()->to('/')->with('error', '인증 실패');
        }
    }
}