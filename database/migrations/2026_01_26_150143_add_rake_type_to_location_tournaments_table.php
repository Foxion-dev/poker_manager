<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournaments', 'rake_type')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->enum('rake_type', ['fixed', 'percentage'])->default('fixed')->after('rake');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournaments', 'rake_type')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->dropColumn('rake_type');
			});
		}
	}
};
