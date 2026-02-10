<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_tournament_participants', function (Blueprint $table) {
            $table->decimal('bounty_stack', 10, 2)->nullable()->after('prize');
            $table->decimal('bounty_prize', 10, 2)->nullable()->after('bounty_stack');
        });
    }

    public function down(): void
    {
        Schema::table('location_tournament_participants', function (Blueprint $table) {
            $table->dropColumn(['bounty_stack', 'bounty_prize']);
        });
    }
};

