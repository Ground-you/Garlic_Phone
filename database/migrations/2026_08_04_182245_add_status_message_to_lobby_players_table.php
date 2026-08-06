<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('lobby_players', function (Blueprint $table) {
            $table->string('status_message', 80)->nullable()->after('avatar');
        });
    }
    public function down(): void {
        Schema::table('lobby_players', function (Blueprint $table) {
            $table->dropColumn('status_message');
        });
    }
};