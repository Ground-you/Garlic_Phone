<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 유저(플레이어) 테이블 정의
        Schema::create('users', function (Blueprint $table) {
            $table->id(); 
            $table->string('discord_id')->nullable()->unique(); // 디스코드 로그인 유저용 (게스트는 null)
            $table->string('name'); // 디스코드 닉네임 또는 게스트가 직접 입력한 닉네임
            $table->string('avatar_url')->nullable(); // 디스코드 프로필 이미지 주소 (게스트는 기본 이미지)
            $table->string('status_message')->nullable(); // 명세서 상의 '나의 한마디'
            $table->string('session_id')->nullable()->index(); // 게스트 유저 구별 및 추적용 세션 ID
            $table->timestamps();
        });

        // 2. 로그인 세션 및 상태 관리를 위한 라라벨 기본 세션 테이블
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