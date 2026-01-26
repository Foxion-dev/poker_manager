<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournament_participants', 'is_paid')) {
			Schema::table('location_tournament_participants', function (Blueprint $table) {
				$table->boolean('is_paid')->default(false)->after('addon');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournament_participants', 'is_paid')) {
			Schema::table('location_tournament_participants', function (Blueprint $table) {
				$table->dropColumn('is_paid');
			});
		}
	}
};
