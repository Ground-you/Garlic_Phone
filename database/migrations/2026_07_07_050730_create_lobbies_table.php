<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('host_nickname');
            $table->string('host_avatar')->nullable();
            $table->string('mode')->default('normal');
            $table->integer('max_players')->default(8);
            $table->integer('time_limit')->default(40);
            $table->boolean('chat_enabled')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lobbies'); }
};