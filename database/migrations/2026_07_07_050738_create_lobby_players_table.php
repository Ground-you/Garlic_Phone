<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lobby_players', function (Blueprint $table) {
            $table->id();
            $table->string('lobby_code');
            $table->string('nickname');
            $table->string('avatar')->nullable();
            $table->boolean('is_host')->default(false);
            $table->boolean('is_ready')->default(false);
            $table->string('session_id');
            $table->timestamps();
            $table->foreign('lobby_code')
                  ->references('code')->on('lobbies')
                  ->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('lobby_players'); }
};