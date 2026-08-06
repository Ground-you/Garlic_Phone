<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_topics', function (Blueprint $table) {
            $table->id();
            $table->string('lobby_code');
            $table->string('session_id');
            $table->string('content');
            $table->timestamps();
            $table->unique(['lobby_code', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_topics');
    }
};