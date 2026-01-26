<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournaments', 'prize_distribution')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->json('prize_distribution')->nullable()->after('rake_type');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournaments', 'prize_distribution')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->dropColumn('prize_distribution');
			});
		}
	}
};
