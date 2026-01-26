<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('location_tournaments', 'itm_percentage')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->decimal('itm_percentage', 5, 2)->default(100.00)->after('format');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('location_tournaments', 'itm_percentage')) {
			Schema::table('location_tournaments', function (Blueprint $table) {
				$table->dropColumn('itm_percentage');
			});
		}
	}
};
