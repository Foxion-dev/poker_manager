<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournament_participants', 'rebuy')) {
			Schema::table('location_tournament_participants', function (Blueprint $table) {
				$table->integer('rebuy')->default(0)->after('place');
				$table->boolean('addon')->default(false)->after('rebuy');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournament_participants', 'rebuy')) {
			Schema::table('location_tournament_participants', function (Blueprint $table) {
				$table->dropColumn(['rebuy', 'addon']);
			});
		}
	}
};
