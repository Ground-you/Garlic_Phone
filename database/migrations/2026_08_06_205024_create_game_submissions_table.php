<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('lobby_code');
            $table->string('session_id');   // 제출한 사람
            $table->integer('round');       // 몇 라운드인지 (0부터 시작)
            $table->string('type');         // 'text' or 'drawing'
            $table->longText('content');    // 텍스트 or base64 이미지
            $table->timestamps();
            $table->unique(['lobby_code', 'session_id', 'round']);
        });

        Schema::create('game_states', function (Blueprint $table) {
            $table->id();
            $table->string('lobby_code')->unique();
            $table->integer('current_round')->default(0);
            $table->integer('total_rounds');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_submissions');
        Schema::dropIfExists('game_states');
    }
};