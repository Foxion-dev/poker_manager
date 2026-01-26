<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournaments', 'rake')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->decimal('rake', 5, 2)->default(30.00)->after('itm_percentage');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournaments', 'rake')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->dropColumn('rake');
			});
		}
	}
};
