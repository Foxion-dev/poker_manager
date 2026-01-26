<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournament_participants', 'name')) {
			Schema::table('location_tournament_participants', function (Blueprint $table) {
				$table->string('name')->nullable()->after('location_tournament_id');
			});
		}

		DB::statement('ALTER TABLE location_tournament_participants MODIFY user_id BIGINT UNSIGNED NULL');

		try {
			DB::statement('ALTER TABLE location_tournament_participants DROP INDEX loc_tourn_part_unique');
		} catch (\Exception $e) {
		}

		try {
			DB::statement('ALTER TABLE location_tournament_participants ADD UNIQUE KEY loc_tourn_part_unique (location_tournament_id, user_id)');
		} catch (\Exception $e) {
		}
	}

	public function down(): void
	{
		Schema::table('location_tournament_participants', function (Blueprint $table) {
			$table->dropColumn('name');
			$table->foreignId('user_id')->nullable(false)->change();
		});
	}
};
