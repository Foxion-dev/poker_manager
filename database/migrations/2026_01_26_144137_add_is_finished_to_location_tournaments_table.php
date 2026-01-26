<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournaments', 'is_finished')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->boolean('is_finished')->default(false)->after('date');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournaments', 'is_finished')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->dropColumn('is_finished');
			});
		}
	}
};
