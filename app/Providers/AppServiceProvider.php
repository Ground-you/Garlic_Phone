<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event; // 👈 상단에 필수 포함

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 💡 SocialiteWasCalled 이벤트를 감지하여 디스코드 드라이버를 강제로 확장 주입
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\Discord\DiscordExtendSocialite::class
        );
    }
}