<?php

return [
    App\Providers\AppServiceProvider::class,
    
    \SocialiteProviders\Manager\ServiceProvider::class, // 👈 이거 한 줄 추가!
];