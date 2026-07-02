<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); 
            $table->string('discord_id')->nullable()->unique(); // 디스코드 고유 ID
            $table->string('name'); // 디스코드 닉네임 또는 게스트 입력 닉네임
            $table->string('email')->nullable(); // 💡 명세서에 표시할 디스코드 이메일 주소 추가
            $table->string('avatar_url')->nullable(); // 디스코드 프로필 이미지 주소
            $table->string('status_message')->nullable(); // 나의 한마디
            $table->string('session_id')->nullable()->index(); // 게스트 구분용 세션 ID
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};