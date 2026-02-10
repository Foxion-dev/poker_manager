<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_tournaments', function (Blueprint $table) {
            $table->decimal('bounty', 10, 2)->nullable()->after('buyin');
        });
    }

    public function down(): void
    {
        Schema::table('location_tournaments', function (Blueprint $table) {
            $table->dropColumn('bounty');
        });
    }
};

